<?php

namespace App\Modules\Documents\Jobs;

use App\Modules\Documents\Models\Document;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ScanDocumentWithClamAv implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(private readonly Document $document) {}

    public function handle(): void
    {
        $this->document->update(['status' => Document::STATUS_SCANNING]);

        if (! config('asd.clamav.enabled', true)) {
            // ClamAV disabled in dev — mark clean immediately
            $this->document->update([
                'status'           => Document::STATUS_CLEAN,
                'virus_scan_result'=> 'skipped (ClamAV disabled)',
                'virus_scanned_at' => now(),
            ]);

            return;
        }

        $tempPath = $this->downloadToTemp();

        try {
            $result = $this->scanWithClamAv($tempPath);
            $isClean = $result === 'OK';

            $this->document->update([
                'status'           => $isClean ? Document::STATUS_CLEAN : Document::STATUS_INFECTED,
                'virus_scan_result'=> $result,
                'virus_scanned_at' => now(),
            ]);

            if (! $isClean) {
                // Delete infected file from storage immediately
                Storage::disk($this->document->disk)->delete($this->document->path);
                activity()->performedOn($this->document)->log("Virus detected: {$result}");
            }
        } finally {
            @unlink($tempPath);
        }
    }

    private function downloadToTemp(): string
    {
        $content  = Storage::disk($this->document->disk)->get($this->document->path);
        $tempPath = sys_get_temp_dir() . '/' . uniqid('asd_scan_', true);
        file_put_contents($tempPath, $content);

        return $tempPath;
    }

    private function scanWithClamAv(string $filePath): string
    {
        $host = config('asd.clamav.host', 'clamav');
        $port = (int) config('asd.clamav.port', 3310);

        $socket = @fsockopen($host, $port, $errno, $errstr, 5);
        if ($socket === false) {
            throw new \RuntimeException("ClamAV unreachable: {$errstr} ({$errno})");
        }

        $fileSize = filesize($filePath);
        $handle   = fopen($filePath, 'rb');

        fwrite($socket, "zINSTREAM\0");
        fwrite($socket, pack('N', $fileSize));

        while (! feof($handle)) {
            fwrite($socket, fread($handle, 8192));
        }
        fclose($handle);

        fwrite($socket, pack('N', 0));
        $result = rtrim(fgets($socket), "\0\n");
        fclose($socket);

        // Result format: "stream: OK" or "stream: Win.Trojan.Example FOUND"
        return str_contains($result, ': OK') ? 'OK' : trim(str_replace(['stream: ', ' FOUND'], '', $result));
    }
}

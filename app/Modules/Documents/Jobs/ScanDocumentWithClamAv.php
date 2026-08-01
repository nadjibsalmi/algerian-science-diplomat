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

        $tempPath = null;

        try {
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
                    // Move infected file to a quarantine area instead of deleting immediately
                    $quarantinePath = 'quarantine/' . ltrim($this->document->path, '/');

                    try {
                        Storage::disk($this->document->disk)->move($this->document->path, $quarantinePath);
                        activity()->performedOn($this->document)->log("Virus detected and file moved to quarantine: {$result}");
                        $this->document->update(['path' => $quarantinePath]);
                    } catch (\Throwable $e) {
                        // If move fails, delete as fallback but log the failure
                        Storage::disk($this->document->disk)->delete($this->document->path);
                        activity()->performedOn($this->document)->log("Virus detected and file deleted (move failed): {$result}");
                    }
                }
            } catch (\RuntimeException $e) {
                // ClamAV unreachable or protocol error — mark as pending scan and record reason
                $this->document->update([
                    'status'            => Document::STATUS_PENDING,
                    'virus_scan_result' => 'clamav_unreachable: ' . $e->getMessage(),
                ]);
                activity()->performedOn($this->document)->log('ClamAV unreachable; scan deferred');
            }
        } finally {
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    private function downloadToTemp(): string
    {
        $disk = $this->document->disk;
        $stream = Storage::disk($disk)->readStream($this->document->path);
        if ($stream === false) {
            throw new \RuntimeException('Failed to open stream from storage');
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'asd_scan_');
        if ($tempPath === false) {
            throw new \RuntimeException('Failed to create temp file');
        }

        $out = fopen($tempPath, 'wb');
        if ($out === false) {
            fclose($stream);
            throw new \RuntimeException('Failed to open temp file for writing');
        }

        try {
            stream_copy_to_stream($stream, $out);
        } finally {
            fclose($stream);
            fclose($out);
        }

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

        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            fclose($socket);
            throw new \RuntimeException('Failed to open temp file for scanning');
        }

        // INSTREAM protocol: send zINSTREAM\0 then chunks prefixed by 4-byte length
        fwrite($socket, "zINSTREAM\0");

        try {
            while (! feof($handle)) {
                $chunk = fread($handle, 8192);
                if ($chunk === false) {
                    throw new \RuntimeException('Failed to read temp file during scan');
                }

                $len = strlen($chunk);
                if ($len > 0) {
                    fwrite($socket, pack('N', $len));
                    fwrite($socket, $chunk);
                }
            }

            // signal end
            fwrite($socket, pack('N', 0));

            $result = rtrim(fgets($socket), "\0\n");
        } finally {
            fclose($handle);
            fclose($socket);
        }

        // Result format: "stream: OK" or "stream: Win.Trojan.Example FOUND"
        return str_contains($result, ': OK') ? 'OK' : trim(str_replace(['stream: ', ' FOUND'], '', $result));
    }
}

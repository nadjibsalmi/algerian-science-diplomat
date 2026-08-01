<?php

namespace App\Modules\Notifications\Services;

use App\Models\User;
use App\Modules\Notifications\Models\NotificationDigest;
use App\Modules\Notifications\Models\NotificationPreference;
use App\Modules\Notifications\Models\SearchAlert;

class NotificationService
{
    public function preferences(User $user)
    {
        return NotificationPreference::where('user_id', $user->id)->orderBy('notification_type')->get();
    }

    public function upsertPreference(User $user, array $data): NotificationPreference
    {
        return NotificationPreference::updateOrCreate(
            ['user_id' => $user->id, 'notification_type' => $data['notification_type']],
            [
                'in_app' => $data['in_app'] ?? true,
                'email' => $data['email'] ?? true,
                'push' => $data['push'] ?? false,
            ],
        );
    }

    public function notifications(User $user)
    {
        return $user->notifications()->latest()->paginate(30);
    }

    public function markRead(User $user, ?string $notificationId = null): int
    {
        $query = $user->unreadNotifications();
        if ($notificationId !== null) {
            $query->whereKey($notificationId);
        }

        return $query->update(['read_at' => now()]);
    }

    public function delete(User $user, string $notificationId): void
    {
        $user->notifications()->whereKey($notificationId)->delete();
    }

    public function alerts(User $user)
    {
        return SearchAlert::where('user_id', $user->id)->latest()->paginate(20);
    }

    public function createAlert(User $user, array $data): SearchAlert
    {
        return SearchAlert::create([
            ...$data,
            'user_id' => $user->id,
            'active' => $data['active'] ?? true,
        ]);
    }

    public function updateAlert(User $user, SearchAlert $alert, array $data): SearchAlert
    {
        abort_unless($alert->user_id === $user->id, 403);
        $alert->update($data);

        return $alert->refresh();
    }

    public function deleteAlert(User $user, SearchAlert $alert): void
    {
        abort_unless($alert->user_id === $user->id, 403);
        $alert->delete();
    }

    public function digest(User $user): NotificationDigest
    {
        return NotificationDigest::firstOrCreate(['user_id' => $user->id]);
    }

    public function updateDigest(User $user, string $frequency): NotificationDigest
    {
        return NotificationDigest::updateOrCreate(
            ['user_id' => $user->id],
            ['frequency' => $frequency],
        );
    }

    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }
}
<?php

namespace App\Modules\SearchAlerts\Services;

use App\Models\User;
use App\Modules\SearchAlerts\Models\SearchAlert;

class SearchAlertService
{
    public function list(User $user)
    {
        return SearchAlert::where('user_id', $user->id)->latest()->paginate(20);
    }

    public function create(User $user, array $data): SearchAlert
    {
        return SearchAlert::create([...$data, 'user_id' => $user->id, 'active' => $data['active'] ?? true]);
    }

    public function update(User $user, SearchAlert $alert, array $data): SearchAlert
    {
        abort_unless($alert->user_id === $user->id, 403);
        $alert->update($data);
        return $alert->refresh();
    }

    public function delete(User $user, SearchAlert $alert): void
    {
        abort_unless($alert->user_id === $user->id, 403);
        $alert->delete();
    }
}
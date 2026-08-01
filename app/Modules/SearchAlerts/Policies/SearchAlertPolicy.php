<?php

namespace App\Modules\SearchAlerts\Policies;

use App\Models\User;
use App\Modules\SearchAlerts\Models\SearchAlert;

class SearchAlertPolicy
{
    public function view(User $user, SearchAlert $alert): bool { return $alert->user_id === $user->id; }
    public function update(User $user, SearchAlert $alert): bool { return $this->view($user, $alert); }
    public function delete(User $user, SearchAlert $alert): bool { return $this->view($user, $alert); }
}
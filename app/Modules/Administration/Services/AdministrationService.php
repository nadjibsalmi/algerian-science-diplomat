<?php

namespace App\Modules\Administration\Services;

use App\Models\User;
use App\Modules\Administration\Models\AdminAuditLog;
use App\Modules\Administration\Models\Setting;
use App\Modules\Administration\Models\UserSuspension;
use Illuminate\Support\Facades\DB;

class AdministrationService
{
    public function settings(?string $group = null)
    {
        return Setting::query()->when($group, fn ($q) => $q->where('group', $group))->orderBy('key')->get();
    }

    public function putSetting(string $key, array $value, string $group = 'general'): Setting
    {
        return Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }

    public function users(?string $status = null)
    {
        return User::query()->select(['id', 'firstname', 'lastname', 'email', 'status', 'last_login', 'created_at'])
            ->when($status, fn ($q) => $q->where('status', $status))->latest()->paginate(30);
    }

    public function suspend(User $actor, User $target, string $reason, ?string $until): UserSuspension
    {
        abort_if($actor->id === $target->id, 422, 'Un administrateur ne peut pas suspendre son propre compte.');
        return DB::transaction(function () use ($actor, $target, $reason, $until): UserSuspension {
            $suspension = UserSuspension::create([
                'user_id' => $target->id,
                'suspended_by' => $actor->id,
                'reason' => $reason,
                'suspended_until' => $until,
            ]);
            $target->update(['status' => 'suspended']);
            activity('administration')->performedOn($target)->causedBy($actor)
                ->withProperties(['reason' => $reason, 'suspended_until' => $until])
                ->log('user.suspended');
            return $suspension->refresh();
        });
    }

    public function liftSuspension(User $actor, User $target): void
    {
        $suspension = UserSuspension::where('user_id', $target->id)->whereNull('lifted_at')->latest()->firstOrFail();
        DB::transaction(function () use ($actor, $target, $suspension): void {
            $suspension->update(['lifted_at' => now(), 'lifted_by' => $actor->id]);
            $target->update(['status' => 'active']);
            activity('administration')->performedOn($target)->causedBy($actor)->log('user.suspension_lifted');
        });
    }

    public function auditLogs(?string $logName = null)
    {
        return AdminAuditLog::query()->when($logName, fn ($q) => $q->where('log_name', $logName))
            ->latest()->paginate(50);
    }

    public function overview(): array
    {
        return [
            'users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'settings' => Setting::count(),
            'audit_events' => AdminAuditLog::count(),
        ];
    }
}
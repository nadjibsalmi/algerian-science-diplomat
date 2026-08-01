<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Roles and permissions exactly as enumerated in the SRS. Every
 * permission is independent (Spatie's model), so a role is just a named
 * bundle of these - new roles can be composed without touching this list.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'create_offer', 'publish_offer', 'pause_offer', 'close_offer', 'edit_offer', 'delete_offer',
            'review_application', 'download_documents',
            'manage_users', 'manage_roles', 'view_logs', 'export_statistics',
            'manage_settings', 'send_private_message', 'view_reports', 'view_offers',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = [
            'Super Admin' => $permissions, // full access, all permissions
            'Platform Admin' => ['manage_users', 'manage_roles', 'view_logs', 'export_statistics', 'manage_settings', 'view_reports'],
            'Embassy Director' => ['create_offer', 'publish_offer', 'pause_offer', 'close_offer', 'edit_offer', 'delete_offer', 'review_application', 'download_documents', 'send_private_message', 'view_reports', 'view_offers'],
            'Embassy Recruiter' => ['create_offer', 'edit_offer', 'review_application', 'download_documents', 'send_private_message', 'view_offers'],
            'Embassy HR' => ['review_application', 'download_documents', 'send_private_message', 'view_offers'],
            'NGO Director' => ['create_offer', 'publish_offer', 'pause_offer', 'close_offer', 'edit_offer', 'delete_offer', 'review_application', 'view_offers'],
            'NGO Recruiter' => ['create_offer', 'edit_offer', 'review_application', 'view_offers'],
            'Candidate' => ['send_private_message'],
            'Support Agent' => ['send_private_message', 'view_logs'],
            'Auditor' => ['view_logs', 'view_reports'],
            'Read Only' => ['view_offers', 'view_reports'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $superAdmin = User::factory()->create([
            'firstname' => 'Super',
            'lastname' => 'Admin',
            'email' => 'admin@asd.dz',
        ]);
        $superAdmin->assignRole('Super Admin');
    }
}

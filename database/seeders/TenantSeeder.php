<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Buat role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $kasirRole = Role::firstOrCreate(['name' => 'kasir']);

        // Buat tenant demo
        $tenant = Tenant::firstOrCreate(
            ['subdomain' => 'demo'],
            [
                'name'      => 'Kafe Demo',
                'is_active' => true,
            ]
        );

        // Buat user admin untuk tenant demo
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Admin Demo',
                'password'  => Hash::make('password'),
            ]
        );
        $admin->assignRole($adminRole);

        // Buat user kasir untuk tenant demo
        $kasir = User::firstOrCreate(
            ['email' => 'kasir@demo.com'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Kasir Demo',
                'password'  => Hash::make('password'),
            ]
        );
        $kasir->assignRole($kasirRole);
    }
}

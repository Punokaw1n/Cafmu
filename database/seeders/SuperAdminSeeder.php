<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $existing = User::where('email', 'superadmin@cafmu.com')->first();

        if ($existing) {
            $this->command->info('Akun Super Admin sudah ada.');
            return;
        }

        User::create([
            'tenant_id' => null,
            'name'      => 'Super Admin',
            'email'     => 'superadmin@cafmu.com',
            'password'  => Hash::make('superadmin123'),
        ]);

        $this->command->info('✅ Akun Super Admin berhasil dibuat!');
        $this->command->info('   Email   : superadmin@cafmu.com');
        $this->command->info('   Password: superadmin123');
        $this->command->warn('   ⚠️  Segera ganti password setelah login pertama!');
    }
}

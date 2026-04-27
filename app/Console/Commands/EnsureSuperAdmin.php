<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class EnsureSuperAdmin extends Command
{
    protected $signature   = 'erp:ensure-super-admin';
    protected $description = 'Create the super admin account if it does not exist';

    public function handle(): int
    {
        $email    = env('SUPER_ADMIN_EMAIL', 'superadmin@africaerp.com');
        $password = env('SUPER_ADMIN_PASSWORD', 'superadmin123');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make($password),
                'role'      => 'super_admin',
                'is_active' => true,
            ]
        );

        $this->info($user->wasRecentlyCreated
            ? "Super admin created: {$email}"
            : "Super admin already exists: {$email}");

        return self::SUCCESS;
    }
}

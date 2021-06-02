<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $accountEmail = env('DEFAULT_ADMIN_EMAIL', 'admin@heidrun.app');
        $accountPassword = env('DEFAULT_ADMIN_PASSWORD', Str::random(32));

        User::factory()->create([
            'name' => 'Heidrun Admin',
            'email' => $accountEmail,
            'email_verified_at' => now(),
            'password' => Hash::make($accountPassword),
            'remember_token' => null,
        ]);

        Log::info(sprintf(
            "Heidrun admin account successfully created\n\n" .
            "Admin Account Email: %s\n" .
            "Admin Account Password: %s",
            $accountEmail,
            $accountPassword
        ));
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ClientTokenSeeder extends Seeder
{
    public function run(): void
    {
        // Cliente para password grant (Vue app)
        $clientId = '0197ce3d-db28-7138-a61b-177a734849fa';
        $plainSecret = 'C9zEkfPjdUrDcUuFDjxv7myST0N61O8sb0s7YOdM';

        DB::table('oauth_clients')->insert([
            'id' => $clientId,
            'name' => 'Vue App Password Client',
            'secret' => Hash::make($plainSecret), // bcrypt
            'provider' => 'users',
            'redirect' => 'http://localhost',
            'personal_access_client' => false,
            'password_client' => true,
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mostrar por consola el secret que debes usar en frontend
        $this->command->info('Client ID:     ' . $clientId);
        $this->command->info('Client Secret: ' . $plainSecret);
    }
}

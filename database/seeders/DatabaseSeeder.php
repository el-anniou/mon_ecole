<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Crée le compte administrateur unique de la V1.
     *
     * Les identifiants proviennent de l'environnement ; les valeurs par
     * défaut ci-dessous sont STRICTEMENT réservées au développement local
     * (voir README). En production, définir ADMIN_EMAIL et ADMIN_PASSWORD
     * avant le premier `php artisan db:seed`.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@exemple.test')],
            [
                'name' => env('ADMIN_NOM', 'Administratrice'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'changer-ce-mot-de-passe')),
            ],
        );
    }
}

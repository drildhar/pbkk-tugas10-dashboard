<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun demo beserta role-nya.
     *
     * @var list<array{name: string, email: string, role: string|null}>
     */
    public const ACCOUNTS = [
        [
            'name' => 'Adriel Mahira Dharma (Admin)',
            'email' => 'admin@its.ac.id',
            'role' => 'admin',
        ],
        [
            'name' => 'Staf Akademik FTEIC',
            'email' => 'staf@its.ac.id',
            'role' => 'staf',
        ],
        [
            'name' => 'Pengguna Umum',
            'email' => 'umum@its.ac.id',
            'role' => null,
        ],
    ];

    /**
     * Membuat akun pengguna uji coba dengan password "password".
     */
    public function run(): void
    {
        foreach (self::ACCOUNTS as $account) {
            /** @var User $user */
            $user = User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles($account['role'] !== null ? [$account['role']] : []);
        }
    }
}

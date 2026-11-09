<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Permission granular yang dipakai di seluruh aplikasi.
     * Key = nama permission Spatie, value = dokumentasi singkat.
     *
     * @var array<string, string>
     */
    public const PERMISSIONS = [
        'view-dashboard' => 'Melihat ringkasan statistik pada panel admin.',
        'kelola-mahasiswa' => 'Melihat, menambah, dan mengubah data mahasiswa.',
        'kelola-dosen' => 'Melihat, menambah, dan mengubah data dosen.',
        'kelola-tugas' => 'Melihat, menambah, dan mengubah data tugas.',
        'kelola-pengguna' => 'Mengelola pengguna dan menetapkan role (khusus admin).',
        'hapus-data' => 'Hak eksklusif admin untuk menghapus data.',
    ];

    /**
     * Matriks permission per role.
     *
     * @var array<string, list<string>>
     */
    public const ROLE_PERMISSIONS = [
        'admin' => [
            'view-dashboard',
            'kelola-mahasiswa',
            'kelola-dosen',
            'kelola-tugas',
            'kelola-pengguna',
            'hapus-data',
        ],
        'staf' => [
            'view-dashboard',
            'kelola-mahasiswa',
            'kelola-dosen',
            'kelola-tugas',
        ],
    ];

    /**
     * Menjalankan seeder role & permission (idempoten).
     */
    public function run(): void
    {
        // Bersihkan cache permission agar hasil seeding langsung dipakai.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(self::PERMISSIONS) as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * Atribut yang boleh diisi secara massal (anti mass-assignment vulnerability).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Atribut yang disembunyikan saat serialisasi.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Tugas yang dibuat/diampu oleh user staf (opsional, dipakai pada panel staf).
     */
    public function tugas(): HasMany
    {
        return $this->hasMany(Tugas::class, 'dosen_id');
    }

    /**
     * Menentukan nama rute beranda sesuai role pengguna.
     *
     * Admin  : /admin/dashboard
     * Staf   : /admin/tugas (panel kerja staf)
     * Lainnya: halaman umum (home)
     */
    public function homeRouteName(): string
    {
        if ($this->hasRole('admin')) {
            return 'admin.dashboard';
        }

        if ($this->hasRole('staf')) {
            return 'admin.tugas.index';
        }

        return 'home';
    }

    /**
     * Label role utama pengguna untuk kebutuhan tampilan.
     */
    public function primaryRoleLabel(): string
    {
        $role = $this->roles->first();

        return $role !== null ? ucfirst($role->name) : 'Pengguna Umum';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departemen extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang dipakai model.
     */
    protected $table = 'departemens';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode',
        'nama',
        'fakultas',
        'jenjang',
    ];

    /**
     * Daftar mahasiswa pada departemen ini.
     */
    public function mahasiswas(): HasMany
    {
        return $this->hasMany(Mahasiswa::class);
    }

    /**
     * Daftar dosen pada departemen ini.
     */
    public function dosens(): HasMany
    {
        return $this->hasMany(Dosen::class);
    }

    /**
     * Label lengkap: "Teknik Informatika (IF)".
     */
    public function getLabelAttribute(): string
    {
        return "{$this->nama} ({$this->kode})";
    }
}

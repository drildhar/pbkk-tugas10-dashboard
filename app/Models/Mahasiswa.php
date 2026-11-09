<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mahasiswa extends Model
{
    use HasFactory;

    /**
     * Prodi/kelas default untuk kebutuhan format NRP ITS.
     */
    public const JENIS_KELAMIN = [
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'departemen_id',
        'nrp',
        'nama',
        'email',
        'angkatan',
        'jenis_kelamin',
        'ipk',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'angkatan' => 'integer',
            'ipk' => 'float',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Departemen mahasiswa.
     */
    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class);
    }

    /**
     * Tugas milik mahasiswa.
     */
    public function tugas(): HasMany
    {
        return $this->hasMany(Tugas::class);
    }

    /**
     * Scope: hanya mahasiswa aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: pencarian multi-kolom (nama ATAU NRP ATAU email).
     *
     * Dipakai sebagai sub-query closure agar pencarian tidak "bentrok"
     * dengan filter lain yang memakai AND.
     */
    public function scopeCari(Builder $query, ?string $keyword): Builder
    {
        return $query->when($keyword, function (Builder $q, string $keyword): Builder {
            return $q->where(function (Builder $sub) use ($keyword): void {
                $sub->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('nrp', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        });
    }

    /**
     * Status keaktifan dalam bentuk label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Cuti/Non-Aktif';
    }
}

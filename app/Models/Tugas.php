<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Tugas extends Model
{
    use HasFactory;

    /**
     * Daftar status tugas yang valid (whitelist filter).
     */
    public const STATUSES = [
        'draft' => 'Draft',
        'aktif' => 'Aktif',
        'selesai' => 'Selesai',
        'terlambat' => 'Terlambat',
    ];

    /**
     * Daftar prioritas tugas (whitelist filter).
     */
    public const PRIORITAS = [
        'rendah' => 'Rendah',
        'sedang' => 'Sedang',
        'tinggi' => 'Tinggi',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'mahasiswa_id',
        'dosen_id',
        'mata_kuliah',
        'judul',
        'deskripsi',
        'status',
        'prioritas',
        'deadline',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
        ];
    }

    /**
     * Mahasiswa pemilik tugas.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    /**
     * Dosen pengampu tugas.
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    /**
     * Scope: tugas yang masih berjalan (status aktif & belum melewati tenggat).
     */
    public function scopeBerjalan(Builder $query): Builder
    {
        return $query->where('status', 'aktif')->where('deadline', '>=', Carbon::now());
    }

    /**
     * Scope: tugas yang sudah melewati tenggat namun belum selesai.
     */
    public function scopeLewatTenggat(Builder $query): Builder
    {
        return $query->whereIn('status', ['aktif', 'terlambat'])
            ->where('deadline', '<', Carbon::now());
    }

    /**
     * Scope: pencarian multi-kolom (judul ATAU mata kuliah ATAU nama mahasiswa).
     */
    public function scopeCari(Builder $query, ?string $keyword): Builder
    {
        return $query->when($keyword, function (Builder $q, string $keyword): Builder {
            return $q->where(function (Builder $sub) use ($keyword): void {
                $sub->where('judul', 'like', "%{$keyword}%")
                    ->orWhere('mata_kuliah', 'like', "%{$keyword}%")
                    ->orWhereHas('mahasiswa', function (Builder $mahasiswa) use ($keyword): void {
                        $mahasiswa->where('nama', 'like', "%{$keyword}%")
                            ->orWhere('nrp', 'like', "%{$keyword}%");
                    });
            });
        });
    }

    /**
     * Sisa hari menuju tenggat (negatif berarti sudah lewat).
     */
    public function getSisaHariAttribute(): int
    {
        return (int) Carbon::now()->startOfDay()->diffInDays($this->deadline->copy()->startOfDay(), false);
    }

    /**
     * Warna badge AdminLTE sesuai status.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'aktif' => 'primary',
            'selesai' => 'success',
            'terlambat' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Warna badge AdminLTE sesuai prioritas.
     */
    public function getPrioritasColorAttribute(): string
    {
        return match ($this->prioritas) {
            'tinggi' => 'danger',
            'sedang' => 'warning',
            default => 'info',
        };
    }
}

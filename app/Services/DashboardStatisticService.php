<?php

namespace App\Services;

use App\Models\Departemen;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Tugas;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardStatisticService
{
    /**
     * Angka utama untuk widget statistik (small box) dashboard admin.
     *
     * @return array<string, int|float>
     */
    public function widgets(): array
    {
        $totalMahasiswa = Mahasiswa::query()->count();
        $mahasiswaAktif = Mahasiswa::query()->where('is_active', true)->count();
        $totalDosen = Dosen::query()->count();
        $dosenAktif = Dosen::query()->where('is_active', true)->count();
        $totalTugas = Tugas::query()->count();
        $tugasBerjalan = Tugas::query()->berjalan()->count();
        $tugasLewatTenggat = Tugas::query()->lewatTenggat()->count();
        $tugasSelesai = Tugas::query()->where('status', 'selesai')->count();

        return [
            'totalMahasiswa' => $totalMahasiswa,
            'mahasiswaAktif' => $mahasiswaAktif,
            'totalDosen' => $totalDosen,
            'dosenAktif' => $dosenAktif,
            'totalTugas' => $totalTugas,
            'tugasBerjalan' => $tugasBerjalan,
            'tugasLewatTenggat' => $tugasLewatTenggat,
            'tugasSelesai' => $tugasSelesai,
            'persentaseSelesai' => $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100, 1) : 0.0,
        ];
    }

    /**
     * Distribusi tugas per status (untuk badge & grafik donat).
     *
     * @return array<string, int>
     */
    public function distribusiStatusTugas(): array
    {
        /** @var Collection<string, int> $raw */
        $raw = Tugas::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $result = [];

        foreach (array_keys(Tugas::STATUSES) as $status) {
            $result[$status] = (int) ($raw[$status] ?? 0);
        }

        return $result;
    }

    /**
     * Tren jumlah tugas yang dibuat selama N bulan terakhir.
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    public function trenTugas(int $bulan = 6): array
    {
        $labels = [];
        $data = [];

        foreach (range($bulan - 1, 0) as $mundur) {
            $periode = Carbon::now()->startOfMonth()->subMonths($mundur);

            $labels[] = $periode->translatedFormat('M Y');
            $data[] = Tugas::query()
                ->whereBetween('created_at', [$periode->copy()->startOfMonth(), $periode->copy()->endOfMonth()])
                ->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Rekap jumlah mahasiswa, dosen, dan tugas per departemen.
     *
     * @return Collection<int, Departemen>
     */
    public function rekapPerDepartemen(): Collection
    {
        $tugasPerDepartemen = DB::table('tugas')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'tugas.mahasiswa_id')
            ->selectRaw('mahasiswas.departemen_id as departemen_id, COUNT(*) as total')
            ->groupBy('mahasiswas.departemen_id')
            ->pluck('total', 'departemen_id');

        return Departemen::query()
            ->withCount([
                'mahasiswas',
                'mahasiswas as mahasiswas_aktif_count' => fn ($query) => $query->where('is_active', true),
                'dosens',
            ])
            ->orderBy('nama')
            ->get()
            ->each(function (Departemen $departemen) use ($tugasPerDepartemen): void {
                $departemen->setAttribute(
                    'tugas_count',
                    (int) ($tugasPerDepartemen[$departemen->getKey()] ?? 0)
                );
            });
    }

    /**
     * Daftar tugas terbaru untuk tabel ringkasan dashboard.
     *
     * @return Collection<int, Tugas>
     */
    public function tugasTerbaru(int $limit = 6): Collection
    {
        return Tugas::query()
            ->with(['mahasiswa.departemen', 'dosen'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Tugas yang mendekati tenggat (7 hari ke depan) dan belum selesai.
     *
     * @return Collection<int, Tugas>
     */
    public function tugasMendekatiTenggat(int $limit = 5): Collection
    {
        return Tugas::query()
            ->with(['mahasiswa', 'dosen'])
            ->whereIn('status', ['aktif', 'draft'])
            ->whereBetween('deadline', [Carbon::now(), Carbon::now()->addDays(7)])
            ->orderBy('deadline')
            ->limit($limit)
            ->get();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesTableQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TugasRequest;
use App\Models\Departemen;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Tugas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TugasController extends Controller implements HasMiddleware
{
    use ResolvesTableQuery;

    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:kelola-tugas'),
        ];
    }

    /**
     * Daftar tugas dengan 4 variabel filter + pencarian relasi.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $this->cleanSearch($request->input('search')),
            'status' => array_key_exists((string) $request->input('status'), Tugas::STATUSES)
                ? (string) $request->input('status')
                : null,
            'prioritas' => array_key_exists((string) $request->input('prioritas'), Tugas::PRIORITAS)
                ? (string) $request->input('prioritas')
                : null,
            'departemen_id' => $request->integer('departemen_id') ?: null,
            'tenggat' => in_array($request->input('tenggat'), ['terdekat', 'lewat'], true)
                ? (string) $request->input('tenggat')
                : null,
        ];

        [$sort, $direction] = $this->resolveSort(
            $request,
            ['judul', 'mata_kuliah', 'status', 'prioritas', 'deadline', 'created_at'],
            defaultColumn: 'deadline',
            defaultDirection: 'asc'
        );

        $tugas = Tugas::query()
            ->with(['mahasiswa.departemen', 'dosen'])
            ->cari($filters['search'])
            ->when($filters['status'], function (Builder $query, string $status): Builder {
                return $query->where('status', $status);
            })
            ->when($filters['prioritas'], function (Builder $query, string $prioritas): Builder {
                return $query->where('prioritas', $prioritas);
            })
            ->when($filters['departemen_id'], function (Builder $query, int $departemenId): Builder {
                return $query->whereHas('mahasiswa', function (Builder $mahasiswa) use ($departemenId): void {
                    $mahasiswa->where('departemen_id', $departemenId);
                });
            })
            ->when($filters['tenggat'] === 'terdekat', function (Builder $query): Builder {
                return $query->whereBetween('deadline', [Carbon::now(), Carbon::now()->addDays(7)]);
            })
            ->when($filters['tenggat'] === 'lewat', function (Builder $query): Builder {
                return $query->lewatTenggat();
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.tugas.index', [
            'tugas' => $tugas,
            'departemens' => Departemen::query()->orderBy('nama')->get(),
            'filters' => $filters,
            'sort' => $sort,
            'direction' => $direction,
            'ringkasanStatus' => Tugas::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.tugas.create', [
            'tugas' => new Tugas([
                'status' => 'aktif',
                'prioritas' => 'sedang',
                'deadline' => Carbon::now()->addWeek()->format('Y-m-d\TH:i'),
            ]),
            'mahasiswas' => $this->pilihanMahasiswa(),
            'dosens' => $this->pilihanDosen(),
        ]);
    }

    public function store(TugasRequest $request): RedirectResponse
    {
        $tugas = Tugas::query()->create($request->validated());

        return redirect()
            ->route('admin.tugas.index')
            ->with('success', "Tugas \"{$tugas->judul}\" berhasil ditambahkan.");
    }

    public function edit(Tugas $tugas): View
    {
        return view('admin.tugas.edit', [
            'tugas' => $tugas,
            'mahasiswas' => $this->pilihanMahasiswa(),
            'dosens' => $this->pilihanDosen(),
        ]);
    }

    public function update(TugasRequest $request, Tugas $tugas): RedirectResponse
    {
        $tugas->update($request->validated());

        return redirect()
            ->route('admin.tugas.index')
            ->with('success', "Tugas \"{$tugas->judul}\" berhasil diperbarui.");
    }

    /**
     * Hapus tugas — hanya admin (permission hapus-data).
     */
    public function destroy(Tugas $tugas): RedirectResponse
    {
        Gate::authorize('hapus-data');

        $judul = $tugas->judul;
        $tugas->delete();

        return redirect()
            ->route('admin.tugas.index')
            ->with('success', "Tugas \"{$judul}\" berhasil dihapus.");
    }

    /**
     * Opsi dropdown mahasiswa (dibatasi agar query tetap ringan).
     *
     * @return Collection<int, Mahasiswa>
     */
    private function pilihanMahasiswa()
    {
        return Mahasiswa::query()
            ->orderBy('nama')
            ->get(['id', 'nrp', 'nama']);
    }

    /**
     * Opsi dropdown dosen.
     *
     * @return Collection<int, Dosen>
     */
    private function pilihanDosen()
    {
        return Dosen::query()->orderBy('nama')->get(['id', 'nama']);
    }
}

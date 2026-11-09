<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesTableQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MahasiswaRequest;
use App\Models\Departemen;
use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MahasiswaController extends Controller implements HasMiddleware
{
    use ResolvesTableQuery;

    /**
     * Proteksi lapis server (Controller Middleware).
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:kelola-mahasiswa'),
        ];
    }

    /**
     * Daftar mahasiswa dengan pencarian multi-kolom, filter departemen/status,
     * sorting dinamis, dan pagination yang mempertahankan query string.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $this->cleanSearch($request->input('search')),
            'departemen_id' => $request->integer('departemen_id') ?: null,
            'status' => in_array($request->input('status'), ['aktif', 'non-aktif'], true)
                ? $request->input('status')
                : null,
            'angkatan' => $request->integer('angkatan') ?: null,
        ];

        [$sort, $direction] = $this->resolveSort(
            $request,
            ['nama', 'nrp', 'angkatan', 'ipk', 'created_at'],
            defaultColumn: 'nama',
            defaultDirection: 'asc'
        );

        $mahasiswas = Mahasiswa::query()
            ->with('departemen')
            ->cari($filters['search'])
            ->when($filters['departemen_id'], function (Builder $query, int $departemenId): Builder {
                return $query->where('departemen_id', $departemenId);
            })
            ->when($filters['status'], function (Builder $query, string $status): Builder {
                return $query->where('is_active', $status === 'aktif');
            })
            ->when($filters['angkatan'], function (Builder $query, int $angkatan): Builder {
                return $query->where('angkatan', $angkatan);
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.mahasiswa.index', [
            'mahasiswas' => $mahasiswas,
            'departemens' => Departemen::query()->orderBy('nama')->get(),
            'angkatans' => Mahasiswa::query()->distinct()->orderByDesc('angkatan')->pluck('angkatan'),
            'filters' => $filters,
            'sort' => $sort,
            'direction' => $direction,
            'totalAktif' => Mahasiswa::query()->where('is_active', true)->count(),
            'totalNonAktif' => Mahasiswa::query()->where('is_active', false)->count(),
        ]);
    }

    /**
     * Form tambah mahasiswa.
     */
    public function create(): View
    {
        return view('admin.mahasiswa.create', [
            'mahasiswa' => new Mahasiswa([
                'angkatan' => (int) date('Y'),
                'jenis_kelamin' => 'L',
                'ipk' => 3.00,
                'is_active' => true,
            ]),
            'departemens' => Departemen::query()->orderBy('nama')->get(),
        ]);
    }

    /**
     * Menyimpan mahasiswa baru (data sudah tervalidasi & dibatasi $fillable).
     */
    public function store(MahasiswaRequest $request): RedirectResponse
    {
        $mahasiswa = Mahasiswa::query()->create($request->validated());

        return redirect()
            ->route('admin.mahasiswa.show', $mahasiswa)
            ->with('success', "Data mahasiswa {$mahasiswa->nama} berhasil ditambahkan.");
    }

    /**
     * Detail mahasiswa beserta daftar tugasnya.
     */
    public function show(Mahasiswa $mahasiswa): View
    {
        $mahasiswa->load(['departemen', 'tugas' => fn ($query) => $query->with('dosen')->latest('deadline')]);

        return view('admin.mahasiswa.show', [
            'mahasiswa' => $mahasiswa,
        ]);
    }

    /**
     * Form edit mahasiswa.
     */
    public function edit(Mahasiswa $mahasiswa): View
    {
        return view('admin.mahasiswa.edit', [
            'mahasiswa' => $mahasiswa,
            'departemens' => Departemen::query()->orderBy('nama')->get(),
        ]);
    }

    /**
     * Memperbarui data mahasiswa.
     */
    public function update(MahasiswaRequest $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $mahasiswa->update($request->validated());

        return redirect()
            ->route('admin.mahasiswa.index')
            ->with('success', "Data mahasiswa {$mahasiswa->nama} berhasil diperbarui.");
    }

    /**
     * Menghapus mahasiswa.
     *
     * Dua lapis proteksi:
     * 1. Middleware rute "permission:hapus-data" (lihat routes/web.php).
     * 2. Otorisasi eksplisit Gate::authorize() di dalam method ini.
     */
    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        Gate::authorize('hapus-data');

        $nama = $mahasiswa->nama;
        $mahasiswa->delete();

        return redirect()
            ->route('admin.mahasiswa.index')
            ->with('success', "Data mahasiswa {$nama} berhasil dihapus.");
    }
}

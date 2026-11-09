<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesTableQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DosenRequest;
use App\Models\Departemen;
use App\Models\Dosen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DosenController extends Controller implements HasMiddleware
{
    use ResolvesTableQuery;

    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:kelola-dosen'),
        ];
    }

    /**
     * Daftar dosen dengan filter departemen, status, dan pencarian multi-kolom.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $this->cleanSearch($request->input('search')),
            'departemen_id' => $request->integer('departemen_id') ?: null,
            'status' => in_array($request->input('status'), ['aktif', 'non-aktif'], true)
                ? $request->input('status')
                : null,
        ];

        [$sort, $direction] = $this->resolveSort(
            $request,
            ['nama', 'nidn', 'jabatan', 'created_at'],
            defaultColumn: 'nama',
            defaultDirection: 'asc'
        );

        $dosens = Dosen::query()
            ->with('departemen')
            ->withCount('tugas')
            ->cari($filters['search'])
            ->when($filters['departemen_id'], function (Builder $query, int $departemenId): Builder {
                return $query->where('departemen_id', $departemenId);
            })
            ->when($filters['status'], function (Builder $query, string $status): Builder {
                return $query->where('is_active', $status === 'aktif');
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.dosen.index', [
            'dosens' => $dosens,
            'departemens' => Departemen::query()->orderBy('nama')->get(),
            'filters' => $filters,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.dosen.create', [
            'dosen' => new Dosen(['is_active' => true, 'jabatan' => 'Asisten Ahli']),
            'departemens' => Departemen::query()->orderBy('nama')->get(),
        ]);
    }

    public function store(DosenRequest $request): RedirectResponse
    {
        $dosen = Dosen::query()->create($request->validated());

        return redirect()
            ->route('admin.dosen.index')
            ->with('success', "Data dosen {$dosen->nama} berhasil ditambahkan.");
    }

    public function edit(Dosen $dosen): View
    {
        return view('admin.dosen.edit', [
            'dosen' => $dosen,
            'departemens' => Departemen::query()->orderBy('nama')->get(),
        ]);
    }

    public function update(DosenRequest $request, Dosen $dosen): RedirectResponse
    {
        $dosen->update($request->validated());

        return redirect()
            ->route('admin.dosen.index')
            ->with('success', "Data dosen {$dosen->nama} berhasil diperbarui.");
    }

    /**
     * Hapus dosen — hanya admin (permission hapus-data).
     */
    public function destroy(Dosen $dosen): RedirectResponse
    {
        Gate::authorize('hapus-data');

        $nama = $dosen->nama;
        $dosen->delete();

        return redirect()
            ->route('admin.dosen.index')
            ->with('success', "Data dosen {$nama} berhasil dihapus.");
    }
}

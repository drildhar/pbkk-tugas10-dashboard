@extends('adminlte::page')

@section('title', 'Data Tugas')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Data Tugas</h1>
            <small class="text-muted">
                Pencarian relasional (judul / mata kuliah / nama mahasiswa) + filter status, prioritas,
                departemen, dan tenggat.
            </small>
        </div>
        @can('kelola-tugas')
            <a href="{{ route('admin.tugas.create') }}" class="btn btn-warning btn-sm mt-2 mt-sm-0">
                <i class="fas fa-plus mr-1"></i> Tambah Tugas
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.partials.flash')

        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Pencarian &amp; Filter</h3>
                <div class="card-tools">
                    <span class="badge badge-warning">{{ $tugas->total() }} tugas</span>
                </div>
            </div>
            <div class="card-body pb-0">
                <form method="GET" action="{{ route('admin.tugas.index') }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">

                    <div class="form-row">
                        <div class="form-group col-lg-4">
                            <label for="search">Kata Kunci (Judul / Mata Kuliah / Mahasiswa)</label>
                            <input type="text" id="search" name="search" value="{{ $filters['search'] }}"
                                   class="form-control" maxlength="100" placeholder="Contoh: RBAC atau Adriel">
                        </div>

                        <div class="form-group col-lg-2 col-6">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">Semua</option>
                                @foreach (\App\Models\Tugas::STATUSES as $key => $label)
                                    <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-lg-2 col-6">
                            <label for="prioritas">Prioritas</label>
                            <select id="prioritas" name="prioritas" class="form-control">
                                <option value="">Semua</option>
                                @foreach (\App\Models\Tugas::PRIORITAS as $key => $label)
                                    <option value="{{ $key }}" @selected($filters['prioritas'] === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-lg-2 col-6">
                            <label for="departemen_id">Departemen</label>
                            <select id="departemen_id" name="departemen_id" class="form-control">
                                <option value="">Semua</option>
                                @foreach ($departemens as $departemen)
                                    <option value="{{ $departemen->id }}" @selected($filters['departemen_id'] === $departemen->id)>
                                        {{ $departemen->kode }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-lg-2 col-6">
                            <label for="tenggat">Tenggat</label>
                            <select id="tenggat" name="tenggat" class="form-control">
                                <option value="">Semua</option>
                                <option value="terdekat" @selected($filters['tenggat'] === 'terdekat')>≤ 7 hari</option>
                                <option value="lewat" @selected($filters['tenggat'] === 'lewat')>Lewat tenggat</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="fas fa-search mr-1"></i> Terapkan
                        </button>
                        <a href="{{ route('admin.tugas.index') }}" class="btn btn-sm btn-outline-secondary ml-1">
                            <i class="fas fa-undo mr-1"></i> Reset Filter
                        </a>

                        @foreach (\App\Models\Tugas::STATUSES as $key => $label)
                            <span class="badge badge-light border ml-1">
                                {{ $label }}: {{ $ringkasanStatus[$key] ?? 0 }}
                            </span>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tasks mr-1"></i> Daftar Tugas</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm table-striped table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>
                                <x-admin.sort-link column="judul" label="Judul / Mata Kuliah"
                                                   route="admin.tugas.index" :sort="$sort" :direction="$direction" />
                            </th>
                            <th>Mahasiswa</th>
                            <th>Dosen Pengampu</th>
                            <th>
                                <x-admin.sort-link column="deadline" label="Deadline"
                                                   route="admin.tugas.index" :sort="$sort" :direction="$direction" />
                            </th>
                            <th>Status</th>
                            <th>Prioritas</th>
                            <th class="text-right" style="width: 120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tugas as $index => $item)
                            <tr>
                                <td>{{ $tugas->firstItem() + $index }}</td>
                                <td>
                                    <span class="font-weight-bold">{{ $item->judul }}</span>
                                    <span class="d-block small text-muted">{{ $item->mata_kuliah }}</span>
                                </td>
                                <td class="small">
                                    {{ $item->mahasiswa?->nama ?? '—' }}
                                    <span class="d-block text-muted text-monospace">{{ $item->mahasiswa?->nrp }}</span>
                                </td>
                                <td class="small">{{ $item->dosen?->nama ?? '—' }}</td>
                                <td class="small">
                                    {{ $item->deadline->translatedFormat('d M Y H:i') }}
                                    <span class="d-block badge badge-{{ $item->sisa_hari < 0 ? 'danger' : ($item->sisa_hari <= 7 ? 'warning' : 'light') }}">
                                        {{ $item->sisa_hari < 0 ? 'lewat '.abs($item->sisa_hari).' hari' : $item->sisa_hari.' hari lagi' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $item->status_color }}">
                                        {{ \App\Models\Tugas::STATUSES[$item->status] ?? $item->status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $item->prioritas_color }}">
                                        {{ \App\Models\Tugas::PRIORITAS[$item->prioritas] ?? $item->prioritas }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    @can('kelola-tugas')
                                        <a href="{{ route('admin.tugas.edit', $item) }}" class="btn btn-xs btn-warning" title="Ubah">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    @endcan

                                    @can('hapus-data')
                                        <form method="POST" action="{{ route('admin.tugas.destroy', $item) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Hapus tugas {{ $item->judul }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-xs btn-outline-secondary" disabled
                                                title="Hanya admin (permission hapus-data)">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Tidak ada tugas yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="small text-muted">
                        Menampilkan {{ $tugas->firstItem() ?? 0 }}&ndash;{{ $tugas->lastItem() ?? 0 }}
                        dari {{ $tugas->total() }} data.
                        <span class="d-none d-lg-inline">
                            &middot; query string: <code>{{ http_build_query(request()->query()) ?: '—' }}</code>
                        </span>
                    </div>
                    <div class="mt-2 mt-md-0">{{ $tugas->links() }}</div>
                </div>
            </div>
        </div>
    </div>
@stop

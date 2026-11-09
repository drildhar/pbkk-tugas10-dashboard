@extends('adminlte::page')

@section('title', 'Data Dosen')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Data Dosen</h1>
            <small class="text-muted">Pencarian nama/NIDN/email, filter departemen dan status keaktifan.</small>
        </div>
        @can('kelola-dosen')
            <a href="{{ route('admin.dosen.create') }}" class="btn btn-success btn-sm mt-2 mt-sm-0">
                <i class="fas fa-plus mr-1"></i> Tambah Dosen
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.partials.flash')

        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Pencarian &amp; Filter</h3>
                <div class="card-tools">
                    <span class="badge badge-success">{{ $dosens->total() }} dosen</span>
                </div>
            </div>
            <div class="card-body pb-0">
                <form method="GET" action="{{ route('admin.dosen.index') }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">

                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label for="search">Kata Kunci (Nama / NIDN / Email)</label>
                            <input type="text" id="search" name="search" value="{{ $filters['search'] }}"
                                   class="form-control" maxlength="100" placeholder="Contoh: 0012345678">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="departemen_id">Departemen</label>
                            <select id="departemen_id" name="departemen_id" class="form-control">
                                <option value="">Semua Departemen</option>
                                @foreach ($departemens as $departemen)
                                    <option value="{{ $departemen->id }}" @selected($filters['departemen_id'] === $departemen->id)>
                                        {{ $departemen->label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">Semua</option>
                                <option value="aktif" @selected($filters['status'] === 'aktif')>Aktif</option>
                                <option value="non-aktif" @selected($filters['status'] === 'non-aktif')>Non-Aktif</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label class="d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-search mr-1"></i> Cari
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <a href="{{ route('admin.dosen.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Reset Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chalkboard-teacher mr-1"></i> Daftar Dosen</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm table-striped table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>
                                <x-admin.sort-link column="nidn" label="NIDN / Nama"
                                                   route="admin.dosen.index" :sort="$sort" :direction="$direction" />
                            </th>
                            <th>Departemen</th>
                            <th>
                                <x-admin.sort-link column="jabatan" label="Jabatan"
                                                   route="admin.dosen.index" :sort="$sort" :direction="$direction" />
                            </th>
                            <th class="text-center">Tugas</th>
                            <th>Status</th>
                            <th class="text-right" style="width: 120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dosens as $index => $dosen)
                            <tr>
                                <td>{{ $dosens->firstItem() + $index }}</td>
                                <td>
                                    <span class="text-monospace small text-muted d-block">{{ $dosen->nidn }}</span>
                                    <span class="font-weight-bold">{{ $dosen->nama }}</span>
                                    <span class="d-block small text-muted">{{ $dosen->email }}</span>
                                </td>
                                <td class="small">
                                    {{ $dosen->departemen?->nama ?? '—' }}
                                    <span class="text-muted">({{ $dosen->departemen?->kode }})</span>
                                </td>
                                <td class="small">{{ $dosen->jabatan }}</td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $dosen->tugas_count }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $dosen->is_active ? 'primary' : 'secondary' }}">
                                        {{ $dosen->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    @can('kelola-dosen')
                                        <a href="{{ route('admin.dosen.edit', $dosen) }}" class="btn btn-xs btn-warning" title="Ubah">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    @endcan

                                    @can('hapus-data')
                                        <form method="POST" action="{{ route('admin.dosen.destroy', $dosen) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Hapus data dosen {{ $dosen->nama }}?');">
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
                                <td colspan="7" class="text-center text-muted py-4">Tidak ada data dosen yang sesuai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="small text-muted">
                        Menampilkan {{ $dosens->firstItem() ?? 0 }}&ndash;{{ $dosens->lastItem() ?? 0 }}
                        dari {{ $dosens->total() }} data.
                    </div>
                    <div class="mt-2 mt-md-0">{{ $dosens->links() }}</div>
                </div>
            </div>
        </div>
    </div>
@stop

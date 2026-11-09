@extends('adminlte::page')

@section('title', 'Data Mahasiswa')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Data Mahasiswa</h1>
            <small class="text-muted">Pencarian multi-kolom, filter departemen, status, dan angkatan.</small>
        </div>
        @can('kelola-mahasiswa')
            <a href="{{ route('admin.mahasiswa.create') }}" class="btn btn-primary btn-sm mt-2 mt-sm-0">
                <i class="fas fa-plus mr-1"></i> Tambah Mahasiswa
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.partials.flash')

        {{-- ================= FORM PENCARIAN & FILTER ================= --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-1"></i> Pencarian &amp; Filter
                </h3>
                <div class="card-tools">
                    <span class="badge badge-info">
                        {{ number_format($mahasiswas->total(), 0, ',', '.') }} hasil ditemukan
                    </span>
                </div>
            </div>

            <div class="card-body pb-0">
                <form method="GET" action="{{ route('admin.mahasiswa.index') }}">
                    {{-- Sorting ikut terbawa saat filter dijalankan --}}
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="search">Kata Kunci (Nama / NRP / Email)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text"
                                       id="search"
                                       name="search"
                                       value="{{ $filters['search'] }}"
                                       class="form-control"
                                       placeholder="Contoh: Adriel atau 5025241097"
                                       maxlength="100">
                            </div>
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
                                <option value="">Semua Status</option>
                                <option value="aktif" @selected($filters['status'] === 'aktif')>Aktif</option>
                                <option value="non-aktif" @selected($filters['status'] === 'non-aktif')>Cuti / Non-Aktif</option>
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <label for="angkatan">Angkatan</label>
                            <select id="angkatan" name="angkatan" class="form-control">
                                <option value="">Semua</option>
                                @foreach ($angkatans as $angkatan)
                                    <option value="{{ $angkatan }}" @selected($filters['angkatan'] === $angkatan)>
                                        {{ $angkatan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-1">
                            <label class="d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block" title="Terapkan filter">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <div>
                            <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-undo mr-1"></i> Reset Filter
                            </a>

                            @if ($filters['search'] || $filters['departemen_id'] || $filters['status'] || $filters['angkatan'])
                                <span class="badge badge-light border ml-2">
                                    Filter aktif membawa {{ count(request()->query()) }} parameter URL
                                </span>
                            @endif
                        </div>
                        <div class="small text-muted mt-2 mt-md-0">
                            Mahasiswa aktif: <strong>{{ $totalAktif }}</strong> &middot;
                            non-aktif: <strong>{{ $totalNonAktif }}</strong>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================= TABEL DATA ================= --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i> Daftar Mahasiswa
                </h3>
                <div class="card-tools">
                    <span class="text-muted small">
                        Halaman {{ $mahasiswas->currentPage() }} dari {{ $mahasiswas->lastPage() }}
                    </span>
                </div>
            </div>

            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped text-nowrap table-sm">
                    <thead>
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>
                                <x-admin.sort-link column="nrp" label="NRP / Nama"
                                                   route="admin.mahasiswa.index" :sort="$sort" :direction="$direction" />
                            </th>
                            <th>Departemen</th>
                            <th>
                                <x-admin.sort-link column="angkatan" label="Angkatan"
                                                   route="admin.mahasiswa.index" :sort="$sort" :direction="$direction" />
                            </th>
                            <th>
                                <x-admin.sort-link column="ipk" label="IPK"
                                                   route="admin.mahasiswa.index" :sort="$sort" :direction="$direction" />
                            </th>
                            <th>Status</th>
                            <th class="text-right" style="width: 140px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mahasiswas as $index => $mahasiswa)
                            <tr>
                                <td>{{ $mahasiswas->firstItem() + $index }}</td>
                                <td>
                                    <span class="text-monospace small text-muted d-block">{{ $mahasiswa->nrp }}</span>
                                    <span class="font-weight-bold">{{ $mahasiswa->nama }}</span>
                                    <span class="d-block small text-muted">{{ $mahasiswa->email }}</span>
                                </td>
                                <td class="small">
                                    {{ $mahasiswa->departemen?->nama ?? '—' }}
                                    <span class="text-muted">({{ $mahasiswa->departemen?->kode }})</span>
                                </td>
                                <td class="small">{{ $mahasiswa->angkatan }}</td>
                                <td>
                                    <span class="badge badge-{{ $mahasiswa->ipk >= 3.5 ? 'success' : ($mahasiswa->ipk >= 3.0 ? 'info' : 'warning') }}">
                                        {{ number_format($mahasiswa->ipk, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $mahasiswa->is_active ? 'primary' : 'secondary' }}">
                                        {{ $mahasiswa->status_label }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.mahasiswa.show', $mahasiswa) }}"
                                       class="btn btn-xs btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @can('kelola-mahasiswa')
                                        <a href="{{ route('admin.mahasiswa.edit', $mahasiswa) }}"
                                           class="btn btn-xs btn-warning" title="Ubah">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    @endcan

                                    {{-- PROTEKSI UI: tombol hapus hanya dirender untuk admin --}}
                                    @can('hapus-data')
                                        <form method="POST"
                                              action="{{ route('admin.mahasiswa.destroy', $mahasiswa) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Hapus data mahasiswa {{ $mahasiswa->nama }}? Tugas terkait juga akan terhapus.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button"
                                                class="btn btn-xs btn-outline-secondary"
                                                disabled
                                                title="Hapus data hanya untuk admin (permission hapus-data)">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    Tidak ada data mahasiswa yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ================= PAGINATION (withQueryString) ================= --}}
            <div class="card-footer border-top">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="small text-muted">
                        Menampilkan
                        <strong>{{ $mahasiswas->firstItem() ?? 0 }}</strong>&ndash;<strong>{{ $mahasiswas->lastItem() ?? 0 }}</strong>
                        dari <strong>{{ $mahasiswas->total() }}</strong> data.
                        <span class="d-none d-lg-inline">
                            Query string terjaga: <code class="ml-1">{{ http_build_query(request()->query()) ?: '—' }}</code>
                        </span>
                    </div>
                    <div class="mt-2 mt-md-0">
                        {{ $mahasiswas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

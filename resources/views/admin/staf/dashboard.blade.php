@extends('adminlte::page')

@section('title', 'Panel Kerja Staf')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Panel Kerja Staf Akademik</h1>
            <small class="text-muted">
                Ruang kerja staf: pemantauan tenggat tugas dan distribusi data per departemen.
            </small>
        </div>
        <a href="{{ route('admin.tugas.index') }}" class="btn btn-primary btn-sm mt-2 mt-sm-0">
            <i class="fas fa-tasks mr-1"></i> Kelola Tugas
        </a>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.partials.flash')

        <div class="row">
            <div class="col-lg-7">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-hourglass-half mr-1"></i> Tugas Perlu Dipantau (7 Hari Ke Depan)
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th>Mahasiswa</th>
                                        <th>Deadline</th>
                                        <th>Sisa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tugasMendekatiTenggat as $item)
                                        <tr>
                                            <td class="small font-weight-bold">{{ $item->judul }}</td>
                                            <td class="small">{{ $item->mahasiswa?->nama ?? '—' }}</td>
                                            <td class="small">{{ $item->deadline->translatedFormat('d M Y H:i') }}</td>
                                            <td>
                                                <span class="badge badge-warning">{{ $item->sisa_hari }} hari</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                Tidak ada tugas yang mendekati tenggat.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-building mr-1"></i> Distribusi Data Akademik
                        </h3>
                    </div>
                    <div class="card-body">
                        @foreach ($rekapDepartemen as $departemen)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>{{ $departemen->nama }}</span>
                                    <span class="text-muted">
                                        {{ $departemen->mahasiswas_count }} mhs &middot;
                                        {{ $departemen->dosens_count }} dosen
                                    </span>
                                </div>
                                <div class="progress progress-xs">
                                    <div class="progress-bar bg-info"
                                         style="width: {{ min(100, $departemen->mahasiswas_count * 4) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="card-footer small text-muted">
                        Staf <strong>tidak</strong> memiliki permission <code>hapus-data</code> maupun
                        akses ke menu Pengguna &amp; Role.
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

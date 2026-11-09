@extends('adminlte::page')

@section('title', 'Dashboard Admin')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Dashboard Admin</h1>
            <small class="text-muted">
                Ringkasan agregat data akademik FTEIC &mdash; dihitung langsung dari database.
            </small>
        </div>
        <div class="mt-2 mt-sm-0">
            @role('admin')
                <span class="badge badge-danger px-3 py-2">
                    <i class="fas fa-user-shield mr-1"></i> Login sebagai Admin
                </span>
            @endrole
            @role('staf')
                <span class="badge badge-warning px-3 py-2">
                    <i class="fas fa-user-tie mr-1"></i> Login sebagai Staf
                </span>
            @endrole
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.partials.flash')

        {{-- ================= WIDGET 1-4: SMALL BOX STATISTIK ================= --}}
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($widgets['mahasiswaAktif'], 0, ',', '.') }}</h3>
                        <p>Mahasiswa Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <a href="{{ route('admin.mahasiswa.index', ['status' => 'aktif']) }}" class="small-box-footer">
                        Dari total {{ number_format($widgets['totalMahasiswa'], 0, ',', '.') }} mahasiswa
                        <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($widgets['totalDosen'], 0, ',', '.') }}</h3>
                        <p>Dosen Terdaftar</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <a href="{{ route('admin.dosen.index') }}" class="small-box-footer">
                        {{ number_format($widgets['dosenAktif'], 0, ',', '.') }} dosen berstatus aktif
                        <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($widgets['tugasBerjalan'], 0, ',', '.') }}</h3>
                        <p>Tugas Berjalan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <a href="{{ route('admin.tugas.index', ['status' => 'aktif']) }}" class="small-box-footer">
                        Dari total {{ number_format($widgets['totalTugas'], 0, ',', '.') }} tugas
                        <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($widgets['tugasLewatTenggat'], 0, ',', '.') }}</h3>
                        <p>Tugas Lewat Tenggat</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <a href="{{ route('admin.tugas.index', ['tenggat' => 'lewat']) }}" class="small-box-footer">
                        Perlu tindak lanjut segera
                        <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- ================= TREN TUGAS 6 BULAN ================= --}}
            <div class="col-lg-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-1"></i> Tren Tugas Dibuat (6 Bulan Terakhir)
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-light">
                                Tingkat penyelesaian: {{ $widgets['persentaseSelesai'] }}%
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $maksimalTren = max(1, max($trenTugas['data']));
                        @endphp

                        <div class="d-flex align-items-end justify-content-between" style="height: 190px;">
                            @foreach ($trenTugas['labels'] as $index => $label)
                                @php
                                    $nilai = $trenTugas['data'][$index];
                                    $tinggi = (int) round(($nilai / $maksimalTren) * 140);
                                @endphp
                                <div class="flex-fill text-center px-1">
                                    <div class="small font-weight-bold text-primary">{{ $nilai }}</div>
                                    <div class="bg-primary rounded-top mx-auto"
                                         style="height: {{ max(6, $tinggi) }}px; width: 60%;
                                                opacity: {{ $nilai > 0 ? 1 : 0.3 }};"></div>
                                    <div class="small text-muted mt-1">{{ $label }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= DISTRIBUSI STATUS TUGAS ================= --}}
            <div class="col-lg-4">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-1"></i> Distribusi Status Tugas
                        </h3>
                    </div>
                    <div class="card-body">
                        @php
                            $totalStatus = max(1, array_sum($distribusiStatus));
                        @endphp

                        @foreach ($distribusiStatus as $status => $jumlah)
                            @php
                                $warna = match ($status) {
                                    'aktif' => 'bg-primary',
                                    'selesai' => 'bg-success',
                                    'terlambat' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                                $persen = round(($jumlah / $totalStatus) * 100, 1);
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-capitalize">{{ $status }}</span>
                                    <span class="font-weight-bold">{{ $jumlah }} ({{ $persen }}%)</span>
                                </div>
                                <div class="progress progress-xs">
                                    <div class="progress-bar {{ $warna }}" style="width: {{ $persen }}%"></div>
                                </div>
                            </div>
                        @endforeach

                        <div class="text-muted small mt-3">
                            Total keseluruhan: <strong>{{ $widgets['totalTugas'] }}</strong> tugas
                            ({{ $widgets['tugasSelesai'] }} selesai).
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- ================= TUGAS TERBARU ================= --}}
            <div class="col-lg-7">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-1"></i> Tugas Terbaru
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.tugas.index') }}" class="btn btn-tool btn-sm">
                                Lihat semua <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Judul Tugas</th>
                                        <th>Mahasiswa</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tugasTerbaru as $item)
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold">{{ $item->judul }}</span>
                                                <div class="small text-muted">{{ $item->mata_kuliah }}</div>
                                            </td>
                                            <td>
                                                {{ $item->mahasiswa?->nama ?? '—' }}
                                                <div class="small text-muted">
                                                    {{ $item->mahasiswa?->nrp }}
                                                </div>
                                            </td>
                                            <td class="small">
                                                {{ $item->deadline->translatedFormat('d M Y H:i') }}
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $item->status_color }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                Belum ada data tugas.
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
                {{-- ================= TUGAS MENDEKATI TENGGAT ================= --}}
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-hourglass-half mr-1"></i> Mendekati Tenggat (7 Hari)
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse ($tugasMendekatiTenggat as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="mr-2">
                                        <div class="font-weight-bold small">{{ $item->judul }}</div>
                                        <div class="text-muted small">
                                            {{ $item->mahasiswa?->nama ?? '—' }}
                                            &middot; {{ $item->deadline->translatedFormat('d M Y') }}
                                        </div>
                                    </div>
                                    <span class="badge badge-warning">
                                        {{ $item->sisa_hari }} hari
                                    </span>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted py-4">
                                    Tidak ada tugas yang mendekati tenggat.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- ================= REKAP PER DEPARTEMEN ================= --}}
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-building mr-1"></i> Rekap Per Departemen
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Departemen</th>
                                        <th class="text-center">Mhs</th>
                                        <th class="text-center">Dosen</th>
                                        <th class="text-center">Tugas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rekapDepartemen as $departemen)
                                        <tr>
                                            <td class="small">
                                                {{ $departemen->nama }}
                                                <span class="text-muted">({{ $departemen->kode }})</span>
                                            </td>
                                            <td class="text-center small">{{ $departemen->mahasiswas_count }}</td>
                                            <td class="text-center small">{{ $departemen->dosens_count }}</td>
                                            <td class="text-center small">{{ $departemen->tugas_count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

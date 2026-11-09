@extends('adminlte::page')

@section('title', 'Detail Mahasiswa')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h1 class="m-0">{{ $mahasiswa->nama }}</h1>
            <small class="text-muted text-monospace">{{ $mahasiswa->nrp }}</small>
        </div>
        <div class="mt-2 mt-sm-0">
            <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            @can('kelola-mahasiswa')
                <a href="{{ route('admin.mahasiswa.edit', $mahasiswa) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-pen mr-1"></i> Ubah
                </a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.partials.flash')

        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center mb-3">
                            <span class="fa-stack fa-3x text-primary">
                                <i class="fas fa-circle fa-stack-2x text-light"></i>
                                <i class="fas fa-user-graduate fa-stack-1x"></i>
                            </span>
                        </div>
                        <h3 class="profile-username text-center">{{ $mahasiswa->nama }}</h3>
                        <p class="text-muted text-center">{{ $mahasiswa->departemen?->nama ?? '—' }}</p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>NRP</b> <span class="float-right text-monospace">{{ $mahasiswa->nrp }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Email</b> <span class="float-right small">{{ $mahasiswa->email }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Angkatan</b> <span class="float-right">{{ $mahasiswa->angkatan }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Jenis Kelamin</b>
                                <span class="float-right">{{ \App\Models\Mahasiswa::JENIS_KELAMIN[$mahasiswa->jenis_kelamin] ?? '—' }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>IPK</b> <span class="float-right">{{ number_format($mahasiswa->ipk, 2) }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Status</b>
                                <span class="float-right badge badge-{{ $mahasiswa->is_active ? 'primary' : 'secondary' }}">
                                    {{ $mahasiswa->status_label }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tasks mr-1"></i> Tugas Mahasiswa ({{ $mahasiswa->tugas->count() }})
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Mata Kuliah</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                    <th>Prioritas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mahasiswa->tugas as $tugas)
                                    <tr>
                                        <td class="small font-weight-bold">{{ $tugas->judul }}</td>
                                        <td class="small">{{ $tugas->mata_kuliah }}</td>
                                        <td class="small">{{ $tugas->deadline->translatedFormat('d M Y H:i') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $tugas->status_color }}">
                                                {{ ucfirst($tugas->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $tugas->prioritas_color }}">
                                                {{ ucfirst($tugas->prioritas) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Belum ada tugas untuk mahasiswa ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

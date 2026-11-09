<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — Admin Dashboard & RBAC</title>
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand-md navbar-dark navbar-primary">
        <div class="container">
            <a href="{{ route('home') }}" class="navbar-brand">
                <i class="fas fa-university mr-2"></i>
                <b>PBKK</b> Dashboard
            </a>
            <div class="ml-auto">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-gauge-high mr-1"></i> Buka Panel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="content-wrapper">
        <div class="content py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1 class="font-weight-bold mb-3">Admin Dashboard &amp; Advanced RBAC</h1>
                        <p class="lead text-muted">
                            Tugas PBKK Pertemuan 10 &mdash; Laravel + Breeze + Spatie Permission + AdminLTE.
                            Otorisasi berlapis (middleware, permission, Gate, dan Blade directive)
                            dengan pencarian multi-kolom serta pagination yang mempertahankan filter.
                        </p>

                        <div class="row mt-4">
                            <div class="col-sm-6 mb-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Mahasiswa</span>
                                        <span class="info-box-number">{{ number_format($statistik['mahasiswa'], 0, ',', '.') }} data</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-chalkboard-teacher"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Dosen</span>
                                        <span class="info-box-number">{{ number_format($statistik['dosen'], 0, ',', '.') }} data</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-tasks"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Tugas Berjalan</span>
                                        <span class="info-box-number">{{ number_format($statistik['tugasBerjalan'], 0, ',', '.') }} tugas</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-secondary"><i class="fas fa-building"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Departemen</span>
                                        <span class="info-box-number">{{ number_format($statistik['departemen'], 0, ',', '.') }} unit</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-bolt mr-1"></i> Quick Login Demo
                                </h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">
                                    Pintasan untuk dosen/asisten penguji. Klik tombol di bawah untuk login
                                    instan dengan akun demo yang sudah di-seed.
                                </p>

                                @if (config('demo.enabled'))
                                    <div class="row">
                                        <div class="col-6">
                                            <form method="POST" action="{{ route('demo.login', 'admin') }}">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-block">
                                                    <i class="fas fa-user-shield mr-1"></i> Admin
                                                </button>
                                            </form>
                                        </div>
                                        <div class="col-6">
                                            <form method="POST" action="{{ route('demo.login', 'staf') }}">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-block">
                                                    <i class="fas fa-user-tie mr-1"></i> Staf
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        Quick login dinonaktifkan (DEMO_LOGIN_ENABLED=false).
                                    </div>
                                @endif

                                <hr>
                                <table class="table table-sm table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <td><span class="badge badge-danger">admin</span></td>
                                            <td class="text-monospace small">admin@its.ac.id / password</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge badge-warning">staf</span></td>
                                            <td class="text-monospace small">staf@its.ac.id / password</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge badge-secondary">umum</span></td>
                                            <td class="text-monospace small">umum@its.ac.id / password</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer text-muted small">
                                Dikembangkan oleh <strong>Adriel Mahira Dharma</strong> &mdash; NRP 5025241097
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="main-footer text-center text-sm">
        <strong>PBKK Kelas B</strong> &middot; Departemen Teknik Informatika, FTEIC ITS
    </footer>
</div>

<script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
</body>
</html>

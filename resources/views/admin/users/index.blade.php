@extends('adminlte::page')

@section('title', 'Manajemen Pengguna & Role')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Manajemen Pengguna &amp; Role</h1>
            <small class="text-muted">Halaman eksklusif admin (middleware <code>role:admin</code>).</small>
        </div>
        <span class="badge badge-danger px-3 py-2 mt-2 mt-sm-0">
            <i class="fas fa-user-shield mr-1"></i> Admin Only
        </span>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.partials.flash')

        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Pengguna</h3>
                <div class="card-tools">
                    <span class="badge badge-danger">{{ $users->total() }} pengguna</span>
                </div>
            </div>
            <div class="card-body pb-0">
                <form method="GET" action="{{ route('admin.users.index') }}" class="form-row">
                    <div class="form-group col-md-6">
                        <label for="search">Cari Nama / Email</label>
                        <input type="text" id="search" name="search" value="{{ $filters['search'] }}"
                               class="form-control" maxlength="100">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="role">Role</label>
                        <select id="role" name="role" class="form-control">
                            <option value="">Semua Role</option>
                            <option value="admin" @selected($filters['role'] === 'admin')>Admin</option>
                            <option value="staf" @selected($filters['role'] === 'staf')>Staf</option>
                            <option value="umum" @selected($filters['role'] === 'umum')>Tanpa Role (umum)</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label class="d-none d-md-block">&nbsp;</label>
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-search mr-1"></i> Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users mr-1"></i> Daftar Pengguna</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama / Email</th>
                            <th>Role Saat Ini</th>
                            <th>Permission Efektif</th>
                            <th style="width: 260px">Ubah Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $index => $user)
                            <tr>
                                <td>{{ $users->firstItem() + $index }}</td>
                                <td>
                                    <span class="font-weight-bold">{{ $user->name }}</span>
                                    <span class="d-block small text-muted">{{ $user->email }}</span>
                                </td>
                                <td>
                                    @forelse ($user->roles as $role)
                                        <span class="badge badge-{{ $role->name === 'admin' ? 'danger' : 'warning' }}">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="badge badge-secondary">tanpa role</span>
                                    @endforelse
                                </td>
                                <td class="small text-muted">
                                    {{ $user->getAllPermissions()->pluck('name')->implode(', ') ?: '—' }}
                                </td>
                                <td>
                                    @if ($user->is(auth()->user()))
                                        <span class="text-muted small">
                                            <i class="fas fa-lock mr-1"></i> Akun Anda (tidak dapat diubah)
                                        </span>
                                    @else
                                        <form method="POST"
                                              action="{{ route('admin.users.update-role', $user) }}"
                                              class="form-inline">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" class="form-control form-control-sm mr-1">
                                                <option value="admin" @selected($user->hasRole('admin'))>admin</option>
                                                <option value="staf" @selected($user->hasRole('staf'))>staf</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Ubah role {{ $user->email }}?');">
                                                Terapkan
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Tidak ada pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted">
                        Menampilkan {{ $users->firstItem() ?? 0 }}&ndash;{{ $users->lastItem() ?? 0 }}
                        dari {{ $users->total() }} pengguna.
                    </span>
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

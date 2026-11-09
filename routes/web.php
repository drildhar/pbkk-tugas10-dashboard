<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DosenController;
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\TugasController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Publik
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Rute Netral Pasca-Login
|--------------------------------------------------------------------------
|
| /dashboard tidak lagi menampilkan view statis, melainkan mendelegasikan
| pengguna ke panel yang sesuai dengan role-nya (admin / staf / umum).
|
*/

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Rute Panel Admin (Backoffice) — Defense in Depth
|--------------------------------------------------------------------------
|
| Lapis 1 : middleware auth + role:admin|staf (hanya user login & staff).
| Lapis 2 : middleware permission:* per modul di dalam controller.
| Lapis 3 : Gate::authorize('hapus-data') pada aksi destruktif.
| Lapis 4 : Blade @can / @role untuk menyembunyikan elemen UI.
|
*/

Route::middleware(['auth', 'role:admin|staf'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'redirect'])->name('redirect');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/panel-staf', [DashboardController::class, 'staf'])->name('staf.dashboard');

        /*
         * Modul Mahasiswa
         * DELETE dipisah agar diproteksi permission eksklusif hapus-data.
         */
        Route::resource('mahasiswa', MahasiswaController::class)->except(['destroy']);
        Route::delete('mahasiswa/{mahasiswa}', [MahasiswaController::class, 'destroy'])
            ->middleware('permission:hapus-data')
            ->name('mahasiswa.destroy');

        /*
         * Modul Dosen
         */
        Route::resource('dosen', DosenController::class)->except(['destroy', 'show']);
        Route::delete('dosen/{dosen}', [DosenController::class, 'destroy'])
            ->middleware('permission:hapus-data')
            ->name('dosen.destroy');

        /*
         * Modul Tugas
         */
        Route::resource('tugas', TugasController::class)
            ->parameters(['tugas' => 'tugas'])
            ->except(['destroy', 'show']);
        Route::delete('tugas/{tugas}', [TugasController::class, 'destroy'])
            ->middleware('permission:hapus-data')
            ->name('tugas.destroy');

        /*
         * Manajemen pengguna & role (eksklusif admin).
         */
        Route::get('pengguna', [UserController::class, 'index'])
            ->middleware('role:admin')
            ->name('users.index');
        Route::patch('pengguna/{user}/role', [UserController::class, 'updateRole'])
            ->middleware('role:admin')
            ->name('users.update-role');
    });

require __DIR__.'/auth.php';

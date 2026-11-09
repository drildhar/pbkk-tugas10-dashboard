<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Tugas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Halaman publik (landing page) dengan ringkasan data & pintasan akun demo.
     */
    public function index(): View
    {
        return view('home', [
            'statistik' => [
                'mahasiswa' => Mahasiswa::query()->count(),
                'dosen' => Dosen::query()->count(),
                'tugasBerjalan' => Tugas::query()->berjalan()->count(),
                'departemen' => Departemen::query()->count(),
            ],
        ]);
    }

    /**
     * Pengalihan pasca-login multi-role.
     *
     * Rute /dashboard tetap dipertahankan sebagai "pintu" netral: pengguna
     * diarahkan ke panel yang sesuai dengan role-nya masing-masing.
     */
    public function dashboard(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->homeRouteName());
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardStatisticService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class DashboardController extends Controller implements HasMiddleware
{
    public function __construct(private readonly DashboardStatisticService $statistics) {}

    /**
     * Proteksi server-side: hanya pemilik permission view-dashboard.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view-dashboard'),
        ];
    }

    /**
     * Beranda panel admin dengan widget statistik agregat real-time.
     */
    public function index(): View
    {
        return view('admin.dashboard', [
            'widgets' => $this->statistics->widgets(),
            'distribusiStatus' => $this->statistics->distribusiStatusTugas(),
            'trenTugas' => $this->statistics->trenTugas(),
            'rekapDepartemen' => $this->statistics->rekapPerDepartemen(),
            'tugasTerbaru' => $this->statistics->tugasTerbaru(),
            'tugasMendekatiTenggat' => $this->statistics->tugasMendekatiTenggat(),
        ]);
    }

    /**
     * Alias lama /admin/dashboard agar tetap kompatibel dengan tautan lama.
     */
    public function redirect(): RedirectResponse
    {
        return redirect()->route('admin.dashboard');
    }

    /**
     * Halaman ringkasan khusus staf (panel kerja staf).
     */
    public function staf(): View
    {
        return view('admin.staf.dashboard', [
            'rekapDepartemen' => $this->statistics->rekapPerDepartemen(),
            'tugasMendekatiTenggat' => $this->statistics->tugasMendekatiTenggat(10),
        ]);
    }
}

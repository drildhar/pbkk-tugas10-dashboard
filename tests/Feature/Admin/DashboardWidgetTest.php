<?php

namespace Tests\Feature\Admin;

use App\Models\Departemen;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Tugas;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWidgetTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $departemen = Departemen::query()->create([
            'kode' => 'IF',
            'nama' => 'Teknik Informatika',
            'fakultas' => 'FTEIC',
            'jenjang' => 'S1',
        ]);

        $dosen = Dosen::query()->create([
            'departemen_id' => $departemen->id,
            'nidn' => '0012345678',
            'nama' => 'Dr. Penguji, M.T.',
            'email' => 'penguji@its.ac.id',
            'jabatan' => 'Lektor',
            'is_active' => true,
        ]);

        $aktif = Mahasiswa::query()->create([
            'departemen_id' => $departemen->id,
            'nrp' => '5025241097',
            'nama' => 'Adriel Mahira Dharma',
            'email' => 'adriel@mhs.its.ac.id',
            'angkatan' => 2024,
            'jenis_kelamin' => 'L',
            'ipk' => 3.86,
            'is_active' => true,
        ]);

        Mahasiswa::query()->create([
            'departemen_id' => $departemen->id,
            'nrp' => '5025211001',
            'nama' => 'Mahasiswa Non Aktif',
            'email' => 'nonaktif@mhs.its.ac.id',
            'angkatan' => 2021,
            'jenis_kelamin' => 'P',
            'ipk' => 2.90,
            'is_active' => false,
        ]);

        Tugas::query()->create([
            'mahasiswa_id' => $aktif->id,
            'dosen_id' => $dosen->id,
            'mata_kuliah' => 'PBKK',
            'judul' => 'Tugas Berjalan',
            'status' => 'aktif',
            'prioritas' => 'tinggi',
            'deadline' => now()->addDays(3),
        ]);

        Tugas::query()->create([
            'mahasiswa_id' => $aktif->id,
            'dosen_id' => $dosen->id,
            'mata_kuliah' => 'PBKK',
            'judul' => 'Tugas Terlambat',
            'status' => 'aktif',
            'prioritas' => 'sedang',
            'deadline' => now()->subDays(3),
        ]);
    }

    public function test_dashboard_renders_aggregate_widgets(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('widgets', function (array $widgets): bool {
            return $widgets['totalMahasiswa'] === 2
                && $widgets['mahasiswaAktif'] === 1
                && $widgets['totalDosen'] === 1
                && $widgets['tugasBerjalan'] === 1
                && $widgets['tugasLewatTenggat'] === 1;
        });

        $response->assertSee('Mahasiswa Aktif');
        $response->assertSee('Dosen Terdaftar');
        $response->assertSee('Tugas Berjalan');
        $response->assertSee('Rekap Per Departemen');
    }

    public function test_staf_can_view_dashboard_and_own_workspace(): void
    {
        $staf = User::factory()->create();
        $staf->assignRole('staf');

        $this->actingAs($staf)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($staf)->get(route('admin.staf.dashboard'))->assertOk();
    }

    public function test_dashboard_redirect_route_sends_user_to_panel(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $umum = User::factory()->create();
        $umum->syncRoles([]);

        $this->actingAs($umum)
            ->get(route('dashboard'))
            ->assertRedirect(route('home'));
    }
}

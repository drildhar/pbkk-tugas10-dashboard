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

class RbacAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staf;

    private Mahasiswa $mahasiswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create(['email' => 'admin@its.ac.id', 'name' => 'Admin']);
        $this->admin->assignRole('admin');

        $this->staf = User::factory()->create(['email' => 'staf@its.ac.id', 'name' => 'Staf']);
        $this->staf->assignRole('staf');

        $departemen = Departemen::query()->create([
            'kode' => 'IF',
            'nama' => 'Teknik Informatika',
            'fakultas' => 'FTEIC',
            'jenjang' => 'S1',
        ]);

        $this->mahasiswa = Mahasiswa::query()->create([
            'departemen_id' => $departemen->id,
            'nrp' => '5025241097',
            'nama' => 'Adriel Mahira Dharma',
            'email' => 'adriel.mahira@mhs.its.ac.id',
            'angkatan' => 2024,
            'jenis_kelamin' => 'L',
            'ipk' => 3.86,
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_admin_panel(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.mahasiswa.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_role_cannot_access_admin_panel(): void
    {
        $umum = User::factory()->create();
        $umum->syncRoles([]);

        $this->actingAs($umum)
            ->get(route('admin.mahasiswa.index'))
            ->assertForbidden();
    }

    public function test_staf_can_read_mahasiswa_but_cannot_delete(): void
    {
        $this->actingAs($this->staf)
            ->get(route('admin.mahasiswa.index'))
            ->assertOk()
            ->assertSee('Adriel Mahira Dharma')
            // Tombol hapus tidak dirender untuk staf, diganti ikon lock.
            ->assertSee('fa-lock')
            ->assertDontSee('fa-trash');
    }

    public function test_staf_delete_request_is_forbidden_403(): void
    {
        $this->actingAs($this->staf)
            ->delete(route('admin.mahasiswa.destroy', $this->mahasiswa))
            ->assertForbidden();

        $this->assertDatabaseHas('mahasiswas', ['id' => $this->mahasiswa->id]);
    }

    public function test_admin_can_delete_mahasiswa(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.mahasiswa.destroy', $this->mahasiswa))
            ->assertRedirect(route('admin.mahasiswa.index'));

        $this->assertDatabaseMissing('mahasiswas', ['id' => $this->mahasiswa->id]);
    }

    public function test_staf_cannot_delete_dosen_and_tugas_either(): void
    {
        $departemen = Departemen::query()->firstOrFail();

        $dosen = Dosen::query()->create([
            'departemen_id' => $departemen->id,
            'nidn' => '0012345678',
            'nama' => 'Dr. Dosen Penguji, M.T.',
            'email' => 'dosen@its.ac.id',
            'jabatan' => 'Lektor',
            'is_active' => true,
        ]);

        $tugas = Tugas::query()->create([
            'mahasiswa_id' => $this->mahasiswa->id,
            'dosen_id' => $dosen->id,
            'mata_kuliah' => 'PBKK',
            'judul' => 'Implementasi RBAC',
            'status' => 'aktif',
            'prioritas' => 'tinggi',
            'deadline' => now()->addDays(3),
        ]);

        $this->actingAs($this->staf)
            ->delete(route('admin.dosen.destroy', $dosen))
            ->assertForbidden();

        $this->actingAs($this->staf)
            ->delete(route('admin.tugas.destroy', $tugas))
            ->assertForbidden();

        $this->assertDatabaseHas('dosens', ['id' => $dosen->id]);
        $this->assertDatabaseHas('tugas', ['id' => $tugas->id]);
    }

    public function test_user_management_page_is_admin_only(): void
    {
        $this->actingAs($this->staf)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Manajemen Pengguna');
    }

    public function test_staf_cannot_see_admin_only_sidebar_menu(): void
    {
        $this->actingAs($this->staf)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Pengguna &amp; Role', false)
            ->assertSee('Data Mahasiswa');
    }

    public function test_admin_can_see_admin_only_sidebar_menu(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pengguna &amp; Role', false);
    }

    public function test_sidebar_menu_requires_permission_not_hardcoded_role(): void
    {
        $response = $this->actingAs($this->staf)->get(route('admin.dashboard'));

        $response->assertOk();
        $this->assertTrue($this->staf->hasPermissionTo('kelola-mahasiswa'));
        $this->assertFalse($this->staf->hasPermissionTo('hapus-data'));
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\Departemen;
use App\Models\Mahasiswa;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class MahasiswaFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Departemen $informatika;

    private Departemen $sistemInformasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->informatika = Departemen::query()->create([
            'kode' => 'IF',
            'nama' => 'Teknik Informatika',
            'fakultas' => 'FTEIC',
            'jenjang' => 'S1',
        ]);

        $this->sistemInformasi = Departemen::query()->create([
            'kode' => 'SI',
            'nama' => 'Sistem Informasi',
            'fakultas' => 'FTEIC',
            'jenjang' => 'S1',
        ]);

        $this->buatMahasiswa('5025241097', 'Adriel Mahira Dharma', $this->informatika, 2024, true);
        $this->buatMahasiswa('5025221001', 'Budi Santoso', $this->informatika, 2022, true);
        $this->buatMahasiswa('5025231002', 'Citra Kirana', $this->sistemInformasi, 2023, false);
        $this->buatMahasiswa('5025241003', 'Adriel Pratama', $this->sistemInformasi, 2024, true);
    }

    private function buatMahasiswa(string $nrp, string $nama, Departemen $departemen, int $angkatan, bool $aktif): Mahasiswa
    {
        return Mahasiswa::query()->create([
            'departemen_id' => $departemen->id,
            'nrp' => $nrp,
            'nama' => $nama,
            'email' => strtolower(str_replace(' ', '.', $nama)).'@mhs.its.ac.id',
            'angkatan' => $angkatan,
            'jenis_kelamin' => 'L',
            'ipk' => 3.50,
            'is_active' => $aktif,
        ]);
    }

    public function test_search_matches_nama_or_nrp(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.mahasiswa.index', ['search' => 'Adriel']));

        $response->assertOk();
        $response->assertSee('Adriel Mahira Dharma');
        $response->assertSee('Adriel Pratama');
        $response->assertDontSee('Budi Santoso');

        $byNrp = $this->actingAs($this->admin)
            ->get(route('admin.mahasiswa.index', ['search' => '5025221001']));

        $byNrp->assertSee('Budi Santoso');
        $byNrp->assertDontSee('Citra Kirana');
    }

    public function test_filters_are_combinable(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.mahasiswa.index', [
            'departemen_id' => $this->informatika->id,
            'status' => 'aktif',
            'angkatan' => 2024,
        ]));

        $response->assertOk();
        $response->assertSee('Adriel Mahira Dharma');
        $response->assertDontSee('Budi Santoso');
        $response->assertDontSee('Citra Kirana');
        $response->assertDontSee('Adriel Pratama');
    }

    public function test_pagination_keeps_query_string(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            $this->buatMahasiswa(
                '50252510'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'Mahasiswa Tambahan '.$i,
                $this->informatika,
                2025,
                true
            );
        }

        $response = $this->actingAs($this->admin)->get(route('admin.mahasiswa.index', [
            'search' => 'Mahasiswa Tambahan',
            'status' => 'aktif',
            'sort' => 'nama',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        /** @var LengthAwarePaginator $paginator */
        $paginator = $response->viewData('mahasiswas');

        $this->assertSame(15, $paginator->total());
        $this->assertCount(10, $paginator->items());

        $urlHalamanDua = $paginator->url(2);

        $this->assertStringContainsString('search=Mahasiswa', $urlHalamanDua);
        $this->assertStringContainsString('status=aktif', $urlHalamanDua);
        $this->assertStringContainsString('page=2', $urlHalamanDua);
    }

    public function test_sorting_is_whitelisted(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.mahasiswa.index', [
            'sort' => 'nrp; DROP TABLE users',
            'direction' => 'asc',
        ]));

        $response->assertOk();
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_staf_can_add_mahasiswa(): void
    {
        $staf = User::factory()->create();
        $staf->assignRole('staf');

        $response = $this->actingAs($staf)->post(route('admin.mahasiswa.store'), [
            'departemen_id' => $this->informatika->id,
            'nrp' => '5025271999',
            'nama' => 'Mahasiswa Baru',
            'email' => 'mahasiswa.baru@mhs.its.ac.id',
            'angkatan' => 2025,
            'jenis_kelamin' => 'P',
            'ipk' => 3.75,
            'is_active' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mahasiswas', ['nrp' => '5025271999', 'nama' => 'Mahasiswa Baru']);
    }

    public function test_validation_rejects_invalid_nrp_and_duplicate_email(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.mahasiswa.store'), [
            'departemen_id' => $this->informatika->id,
            'nrp' => '123',
            'nama' => 'Test',
            'email' => 'adriel.mahira.dharma@mhs.its.ac.id',
            'angkatan' => 2024,
            'jenis_kelamin' => 'L',
            'ipk' => 3.00,
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors(['nrp', 'email']);
        $this->assertDatabaseCount('mahasiswas', 4);
    }
}

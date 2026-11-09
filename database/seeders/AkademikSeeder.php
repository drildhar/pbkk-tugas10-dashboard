<?php

namespace Database\Seeders;

use App\Models\Departemen;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Tugas;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AkademikSeeder extends Seeder
{
    /**
     * Departemen di lingkungan FTEIC ITS.
     *
     * @var list<array{kode: string, nama: string}>
     */
    private const DEPARTEMEN = [
        ['kode' => 'IF', 'nama' => 'Teknik Informatika'],
        ['kode' => 'SI', 'nama' => 'Sistem Informasi'],
        ['kode' => 'TK', 'nama' => 'Teknik Komputer'],
        ['kode' => 'TE', 'nama' => 'Teknik Elektro'],
        ['kode' => 'TB', 'nama' => 'Teknik Biomedik'],
        ['kode' => 'TI', 'nama' => 'Teknologi Informasi'],
    ];

    /**
     * Mata kuliah khas departemen informatika ITS.
     *
     * @var list<string>
     */
    private const MATA_KULIAH = [
        'Pemrograman Berbasis Kerangka Kerja',
        'Pemrograman Web',
        'Basis Data',
        'Struktur Data',
        'Algoritma dan Pemrograman',
        'Rekayasa Perangkat Lunak',
        'Kecerdasan Artifisial',
        'Jaringan Komputer',
        'Sistem Operasi',
        'Keamanan Informasi',
        'Analisis dan Perancangan Sistem',
        'Pemrograman Mobile',
    ];

    /**
     * Judul tugas realistis.
     *
     * @var list<string>
     */
    private const JUDUL_TUGAS = [
        'Implementasi Autentikasi Multi-Role dengan Laravel Breeze',
        'Perancangan ERD Sistem Informasi Akademik',
        'Membuat REST API Sederhana dengan Sanctum',
        'Optimasi Query Eloquent untuk Laporan Bulanan',
        'Unit Testing Modul Otorisasi Menggunakan PHPUnit',
        'Dashboard Analitik dengan Chart.js dan AdminLTE',
        'Normalisasi Database Hingga Bentuk 3NF',
        'Implementasi Queue untuk Pengiriman Notifikasi',
        'Refactoring Controller dengan Form Request Validation',
        'Deployment Aplikasi Laravel di VPS Ubuntu',
        'Analisis Kompleksitas Algoritma Pencarian',
        'Studi Kasus Keamanan Aplikasi Web (OWASP Top 10)',
        'Pengembangan Fitur Export PDF Laporan Tugas',
        'Implementasi Role-Based Access Control pada Modul Akademik',
        'Perancangan UI/UX Halaman Manajemen Data',
    ];

    /**
     * Gelar akademik dosen ITS yang realistis.
     *
     * @var list<string>
     */
    private const GELAR = ['S.T., M.T.', 'S.Kom., M.Kom.', 'S.T., M.Sc.', 'S.T., M.T., Ph.D.', 'S.Kom., M.T.'];

    /**
     * Jabatan fungsional dosen.
     *
     * @var list<string>
     */
    private const JABATAN = ['Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar'];

    private Generator $faker;

    /**
     * Membuat data akademik dummy yang realistis.
     */
    public function run(): void
    {
        $this->faker = FakerFactory::create('id_ID');
        $this->faker->seed(1097);

        $departemens = $this->seedDepartemen();
        $dosens = $this->seedDosen($departemens);
        $mahasiswas = $this->seedMahasiswa($departemens);

        $this->seedTugas($mahasiswas, $dosens);
    }

    /**
     * @return Collection<string, Departemen>
     */
    private function seedDepartemen()
    {
        $result = collect();

        foreach (self::DEPARTEMEN as $item) {
            $result->put($item['kode'], Departemen::query()->updateOrCreate(
                ['kode' => $item['kode']],
                [
                    'nama' => $item['nama'],
                    'fakultas' => 'Fakultas Teknologi Elektro dan Informatika Cerdas',
                    'jenjang' => 'S1',
                ]
            ));
        }

        return $result;
    }

    /**
     * Membuat data 18 dosen (tersebar pada 6 departemen).
     *
     * @param  Collection<string, Departemen>  $departemens
     * @return Collection<int, Dosen>
     */
    private function seedDosen($departemens)
    {
        $result = collect();

        for ($i = 1; $i <= 18; $i++) {
            $nama = $this->namaDosen();
            $nidn = str_pad((string) $this->faker->unique()->numberBetween(1, 9999999999), 10, '0', STR_PAD_LEFT);
            $email = $this->emailUnik($nama, 'its.ac.id', $i);

            $result->push(Dosen::query()->updateOrCreate(
                ['nidn' => $nidn],
                [
                    'departemen_id' => $departemens->random()->id,
                    'nama' => $nama,
                    'email' => $email,
                    'jabatan' => $this->faker->randomElement(self::JABATAN),
                    'is_active' => $this->faker->boolean(92),
                ]
            ));
        }

        return $result;
    }

    /**
     * Membuat data 42 mahasiswa dengan NRP format ITS (5025 + angkatan + urutan).
     *
     * @param  Collection<string, Departemen>  $departemens
     * @return Collection<int, Mahasiswa>
     */
    private function seedMahasiswa($departemens)
    {
        $result = collect();

        // Mahasiswa pertama: identitas pengembang (dipakai sebagai data contoh).
        $result->push(Mahasiswa::query()->updateOrCreate(
            ['nrp' => '5025241097'],
            [
                'departemen_id' => $departemens->get('IF')->id,
                'nama' => 'Adriel Mahira Dharma',
                'email' => 'adriel.mahira@mhs.its.ac.id',
                'angkatan' => 2024,
                'jenis_kelamin' => 'L',
                'ipk' => 3.86,
                'is_active' => true,
            ]
        ));

        for ($i = 1; $i <= 41; $i++) {
            $angkatan = $this->faker->numberBetween(2021, 2025);
            $urut = 1000 + $i;
            $nrp = '5025'.substr((string) $angkatan, 2, 2).str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
            $nama = $this->faker->name();

            $result->push(Mahasiswa::query()->updateOrCreate(
                ['nrp' => $nrp],
                [
                    'departemen_id' => $departemens->random()->id,
                    'nama' => $nama,
                    'email' => $this->emailUnik($nama, 'mhs.its.ac.id', $i),
                    'angkatan' => $angkatan,
                    'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
                    'ipk' => round($this->faker->randomFloat(2, 2.51, 3.99), 2),
                    'is_active' => $this->faker->boolean(88),
                ]
            ));
        }

        return $result;
    }

    /**
     * Membuat data 75 tugas dengan status yang konsisten terhadap tenggat waktu.
     *
     * @param  Collection<int, Mahasiswa>  $mahasiswas
     * @param  Collection<int, Dosen>  $dosens
     */
    private function seedTugas($mahasiswas, $dosens): void
    {
        if (Tugas::query()->exists()) {
            return;
        }

        for ($i = 1; $i <= 75; $i++) {
            $deadline = Carbon::now()->addDays($this->faker->numberBetween(-45, 30));

            if ($deadline->isPast()) {
                $status = $this->faker->randomElement(['selesai', 'selesai', 'terlambat']);
            } else {
                $status = $this->faker->randomElement(['aktif', 'aktif', 'draft']);
            }

            Tugas::query()->create([
                'mahasiswa_id' => $mahasiswas->random()->id,
                'dosen_id' => $this->faker->boolean(85) ? $dosens->random()->id : null,
                'mata_kuliah' => $this->faker->randomElement(self::MATA_KULIAH),
                'judul' => $this->faker->randomElement(self::JUDUL_TUGAS),
                'deskripsi' => $this->faker->paragraph(2),
                'status' => $status,
                'prioritas' => $this->faker->randomElement(array_keys(Tugas::PRIORITAS)),
                'deadline' => $deadline,
            ]);
        }
    }

    /**
     * Nama dosen lengkap dengan gelar depan & belakang.
     */
    private function namaDosen(): string
    {
        $pria = $this->faker->boolean(65);
        $nama = $this->faker->firstName($pria ? 'male' : 'female').' '.$this->faker->lastName();
        $gelarDepan = $this->faker->randomElement(['Dr.', 'Dr. Eng.', 'Prof. Dr.', 'Ir.', '']);
        $gelarBelakang = $this->faker->randomElement(self::GELAR);

        return trim($gelarDepan.' '.$nama).', '.$gelarBelakang;
    }

    /**
     * Membuat alamat email unik dari nama (tanpa gelar).
     */
    private function emailUnik(string $nama, string $domain, int $sequence): string
    {
        $bersih = Str::lower(Str::ascii($nama));
        $bersih = preg_replace('/[^a-z ]/', '', $bersih) ?? 'pengguna';
        $bagian = preg_split('/\s+/', trim($bersih)) ?: ['pengguna'];
        $slug = implode('.', array_slice($bagian, 0, 2));

        return sprintf('%s.%d@%s', $slug !== '' ? $slug : 'pengguna', $sequence, $domain);
    }
}

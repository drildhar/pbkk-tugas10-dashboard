# PBKK Pertemuan 10 — Admin Dashboard & Advanced RBAC

Implementasi **Admin Dashboard** profesional berbasis template dengan **Role-Based Access Control (RBAC) dinamis** dari database relasional, kueri filter tingkat lanjut (`when()`), pagination yang mempertahankan filter (`withQueryString()`), auto-login evaluator, dan dataset faker realistis khas lingkungan ITS.

| Identitas | Keterangan |
| --- | --- |
| **Nama** | Adriel Mahira Dharma |
| **NRP** | 5025241097 |
| **Mata Kuliah** | Pemrograman Berbasis Kerangka Kerja (PBKK) — Kelas B |
| **Institusi** | Departemen Teknik Informatika, FTEIC, Institut Teknologi Sepuluh Nopember |
| **Topik** | Pertemuan 10 — Admin Dashboard & Role-Based Access Control |

---

## 1. Ringkasan Teknologi

| Komponen | Versi / Paket | Keterangan |
| --- | --- | --- |
| Framework | `laravel/framework` **12.x** | Struktur & API identik dengan Laravel 11 (lihat [Catatan Versi](#12-catatan-kompatibilitas-versi)) |
| Bahasa | PHP 8.2+ (diuji pada PHP 8.5.8) | |
| Autentikasi | `laravel/breeze` (Blade stack) | Login, register, reset password, profil |
| RBAC | `spatie/laravel-permission` **8.x** | Role, permission granular, middleware `role:` / `permission:` |
| Template Backoffice | `jeroennoten/laravel-adminlte` **3.16** (AdminLTE 3.2 / Bootstrap 4) | `@extends('adminlte::page')` |
| Database | SQLite (default) / MySQL / MariaDB | Skema portabel, tanpa kueri vendor-specific |
| Data Generator | `fakerphp/faker` (`id_ID`) | 42 mahasiswa, 18 dosen, 75 tugas |
| Formatter | `laravel/pint` (PSR-12 / Laravel preset) | `vendor/bin/pint --test` → **passed** |

---

## 2. Fitur yang Telah Selesai (Checklist Nilai A+)

### A. Autentikasi & Pengalihan Multi-Role
- [x] Autentikasi terpusat Laravel Breeze (Blade).
- [x] Pengalihan pasca-login **dinamis** berbasis role melalui `User::homeRouteName()`:
  - `admin` → `/admin/dashboard`
  - `staf` → `/admin/tugas` (panel kerja staf)
  - non-staff/umum → `/` (halaman umum)
- [x] Rute `/dashboard` dipertahankan sebagai pintu netral yang mendelegasikan ke panel sesuai role.
- [x] Seluruh rute backoffice dikelompokkan pada prefix `/admin` dengan middleware `['auth', 'role:admin|staf']`.

### B. Arsitektur Role & Permission
- [x] Trait `Spatie\Permission\Traits\HasRoles` aktif pada model `User`.
- [x] Role: `admin` (super administrator) dan `staf` (terbatas, tanpa hak hapus).
- [x] Permission granular: `view-dashboard`, `kelola-mahasiswa`, `kelola-dosen`, `kelola-tugas`, `kelola-pengguna`, `hapus-data`.
- [x] Pemetaan role ↔ permission terstruktur & idempoten pada `RoleAndPermissionSeeder`.

### C. Keamanan Berlapis (Defense in Depth)
- [x] **Lapis 1 — Rute**: `auth` + `role:admin|staf`.
- [x] **Lapis 2 — Controller**: `HasMiddleware` (`permission:kelola-mahasiswa`, dst.) untuk seluruh aksi modul.
- [x] **Lapis 3 — Aksi destruktif**: middleware rute `permission:hapus-data` **dan** `Gate::authorize('hapus-data')` di dalam `destroy()`.
- [x] **Lapis 4 — Form Request**: `authorize()` memeriksa permission, `rules()` memvalidasi seluruh input.
- [x] **Lapis 5 — UI**: `@can('hapus-data')`, `@role('admin')`, `@cannot(...)` pada Blade; menu sidebar difilter `GateFilter`.
- [x] Hasil: request `DELETE` manual (Postman/cURL) dari `staf` → **HTTP 403 Forbidden** (terbukti pada test & uji cURL).

### D. Backoffice & Widget Dashboard
- [x] Dashboard mewarisi `adminlte::page` dengan 4 widget `small-box` agregat real-time (`Mahasiswa aktif`, `Dosen terdaftar`, `Tugas berjalan`, `Tugas lewat tenggat`), tren 6 bulan, distribusi status, rekap per departemen, tugas terbaru, dan daftar tenggat terdekat.
- [x] `config/adminlte.php` dimodifikasi: judul, logo, dan menu sidebar yang **difilter dinamis** berdasarkan permission (`'can' => 'kelola-mahasiswa'`, dst.).

### Challenge 1 — Advanced Conditional Search & Multi-Variable Filter (`when()`)
- [x] Pencarian multi-kolom via sub-query closure: **nama ATAU NRP ATAU email** (mahasiswa), **judul ATAU mata kuliah ATAU nama/NRP mahasiswa** (tugas).
- [x] Filter variabel: `departemen_id`, `status` (`aktif`/`non-aktif`), `angkatan`, `prioritas`, `tenggat` (`≤7 hari`/`lewat`) — semuanya dirangkai dengan `->when()` tanpa `if-else` query.
- [x] Sorting dinamis dengan **whitelist kolom** (anti ORDER BY injection) + indikator panah pada header tabel.

### Challenge 2 — Pagination Locking (`withQueryString()`)
- [x] `->paginate(10)->withQueryString()` pada seluruh modul (mahasiswa, dosen, tugas, pengguna).
- [x] Seluruh parameter `search`, `departemen_id`, `status`, `angkatan`, `sort`, `direction` tetap melekat pada halaman 2, 3, dst. (terverifikasi test `test_pagination_keeps_query_string` dan uji HTTP langsung).

### Challenge 3 — Realistic Auto-Seeder dengan Faker
- [x] `php artisan migrate:fresh --seed` langsung menyiapkan akun demo + **42 mahasiswa**, **18 dosen**, **75 tugas**, **6 departemen FTEIC**.
- [x] NRP format ITS (`5025` + 2 digit angkatan + 4 digit urutan, contoh **5025241097**), email `@mhs.its.ac.id` / `@its.ac.id`, gelar & jabatan dosen realistis, status tugas konsisten terhadap tenggat.

### Challenge 4 — 1-Click Auto-Login Test Credentials
- [x] Panel **“Quick Login Demo”** pada `resources/views/auth/login.blade.php` (tombol *Login sebagai Admin* & *Login sebagai Staf*).
- [x] Rute `POST /demo-login/{panel}` dengan **whitelist** `config/demo.php`, throttle `10,1`, dan saklar `DEMO_LOGIN_ENABLED`.
- [x] Pintasan serupa tersedia di landing page publik `/`.

---

## 3. Akun Demo

| Role | Email | Password | Hak Akses |
| --- | --- | --- | --- |
| `admin` | `admin@its.ac.id` | `password` | Semua permission + `hapus-data` + manajemen pengguna |
| `staf` | `staf@its.ac.id` | `password` | `view-dashboard`, `kelola-mahasiswa`, `kelola-dosen`, `kelola-tugas` (tanpa hapus) |
| tanpa role | `umum@its.ac.id` | `password` | Hanya halaman publik — `/admin/*` menghasilkan 403 |

> Quick login: buka `/login` → panel **Quick Login Demo** → klik tombol Admin / Staf (tanpa mengetik kredensial).

### Matriks Role × Permission

| Permission | admin | staf | umum |
| --- | :---: | :---: | :---: |
| `view-dashboard` | ✅ | ✅ | ❌ |
| `kelola-mahasiswa` | ✅ | ✅ | ❌ |
| `kelola-dosen` | ✅ | ✅ | ❌ |
| `kelola-tugas` | ✅ | ✅ | ❌ |
| `kelola-pengguna` (menu Pengguna & Role) | ✅ | ❌ | ❌ |
| `hapus-data` (DELETE) | ✅ | ❌ | ❌ |

---

## 4. Struktur Artefak Kode

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── Concerns/ResolvesTableQuery.php   # helper search/sort (whitelist)
│   │   │   ├── DashboardController.php           # widget agregat + panel staf
│   │   │   ├── MahasiswaController.php           # CRUD + when() + withQueryString()
│   │   │   ├── DosenController.php               # CRUD + filter
│   │   │   ├── TugasController.php               # CRUD + 5 variabel filter
│   │   │   └── UserController.php                # manajemen role (admin only)
│   │   ├── Auth/
│   │   │   ├── AuthenticatedSessionController.php # redirect multi-role
│   │   │   └── DemoLoginController.php            # quick login (whitelist)
│   │   └── HomeController.php                     # landing + /dashboard redirector
│   ├── Middleware (alias Spatie) → bootstrap/app.php
│   └── Requests/Admin/
│       ├── MahasiswaRequest.php   # validasi + authorize permission
│       ├── DosenRequest.php
│       ├── TugasRequest.php
│       └── UserRoleRequest.php
├── Models/
│   ├── User.php          # HasRoles + homeRouteName()
│   ├── Departemen.php
│   ├── Mahasiswa.php     # scopeCari(), scopeAktif(), casts, relasi
│   ├── Dosen.php
│   └── Tugas.php         # STATUSES, PRIORITAS, scopeBerjalan(), scopeLewatTenggat()
├── Services/DashboardStatisticService.php  # semua query agregasi dashboard
└── Providers/AppServiceProvider.php        # Paginator::useBootstrapFour()

database/
├── migrations/
│   ├── 2026_11_09_070708_create_permission_tables.php
│   ├── 2026_11_09_080001_create_departemens_table.php
│   ├── 2026_11_09_080002_create_dosens_table.php
│   ├── 2026_11_09_080003_create_mahasiswas_table.php
│   └── 2026_11_09_080004_create_tugas_table.php
└── seeders/
    ├── DatabaseSeeder.php
    ├── RoleAndPermissionSeeder.php
    ├── UserSeeder.php
    └── AkademikSeeder.php        # Faker id_ID: 42 mhs / 18 dosen / 75 tugas

resources/views/
├── admin/
│   ├── dashboard.blade.php           # 4 small-box + tren + distribusi + rekap
│   ├── staf/dashboard.blade.php      # panel kerja staf
│   ├── partials/flash.blade.php
│   ├── mahasiswa/{index,create,edit,show,_form}.blade.php
│   ├── dosen/{index,create,edit,_form}.blade.php
│   ├── tugas/{index,create,edit,_form}.blade.php
│   └── users/index.blade.php
├── components/admin/sort-link.blade.php
├── auth/login.blade.php              # + panel Quick Login Demo
└── home.blade.php                    # landing page publik

routes/
├── web.php    # grup /admin + middleware role & permission
└── auth.php   # login/register + POST demo-login/{panel}

config/
├── adminlte.php   # menu sidebar terfilter permission
├── demo.php       # whitelist akun quick login
└── permission.php # konfigurasi Spatie

tests/Feature/
├── Admin/RbacAuthorizationTest.php    # 403 untuk staf, 302 untuk admin
├── Admin/MahasiswaFilterTest.php      # search, filter, pagination, sort whitelist
├── Admin/DashboardWidgetTest.php      # angka widget agregat
└── Auth/DemoLoginTest.php             # quick login & saklar disable
```

---

## 5. Instalasi Step-by-Step

### 5.1 Prasyarat
- PHP **8.2+** dengan ekstensi `pdo_sqlite` (default) atau `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`.
- Composer 2.x, Node.js 18+ (opsional, hanya untuk aset Breeze/Tailwind).

### 5.2 Clone & Install
```bash
git clone <repo-url> pbkk-tugas10-dashboard
cd pbkk-tugas10-dashboard

# 1. Dependensi PHP
composer install

# 2. Environment
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate

# 3. Database (default SQLite)
#    Linux/macOS:
touch database/database.sqlite
#    Windows (PowerShell):
#    New-Item database/database.sqlite -ItemType File
```

Konfigurasi MySQL/MariaDB (opsional) — ubah `.env`:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pbkk_tugas10
DB_USERNAME=root
DB_PASSWORD=
```

### 5.3 Migrasi + Seeder (satu perintah)
```bash
php artisan migrate:fresh --seed
```
Output yang diharapkan: tabel `users`, `roles`, `permissions`, `departemens`, `dosens`, `mahasiswas`, `tugas` terbentuk dan terisi data dummy.

### 5.4 Publikasi Aset AdminLTE (wajib)
```bash
php artisan adminlte:install      # config + assets ke public/vendor + translations
```
> Langkah ini hanya perlu sekali per clone (folder `public/vendor/` bersifat *generated* dan
> tidak di-commit). Perintah bersifat idempoten: aman dijalankan ulang, gunakan
> `php artisan adminlte:update` untuk memperbarui distribusi AdminLTE.

### 5.5 Aset Frontend Breeze (diperlukan halaman login/register/profil)
```bash
npm install
npm run build      # atau: npm run dev
```
> Panel admin **tidak** memerlukan build Vite (aset AdminLTE dimuat dari `public/vendor/`).

### 5.6 Jalankan
```bash
php artisan serve
```
Buka `http://127.0.0.1:8000`.

### 5.7 Perintah instalasi paket (referensi reproduksi)
```bash
composer require spatie/laravel-permission jeroennoten/laravel-adminlte
composer require laravel/breeze --dev && php artisan breeze:install blade

php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan adminlte:install            # config + assets + translations
php artisan migrate
```

---

## 6. Panduan Pengujian (Live Demo untuk Dosen/Asisten)

1. **Multi-role redirect** — login `admin@its.ac.id` → langsung `/admin/dashboard`; login `staf@its.ac.id` → langsung `/admin/tugas`; login `umum@its.ac.id` → halaman publik.
2. **Widget agregat** — dashboard menampilkan 4 kotak statistik (mahasiswa aktif, dosen, tugas berjalan, tugas lewat tenggat) + tren 6 bulan.
3. **Filter + pagination** — buka `/admin/mahasiswa`, isi kata kunci `Adriel`, pilih departemen **Teknik Informatika**, status **Aktif**, klik *Terapkan*, lalu pindah ke **halaman 2**: seluruh parameter tetap pada URL (`?search=...&departemen_id=...&status=aktif&page=2`).
4. **Sorting** — klik header *NRP / Nama*, *Angkatan*, atau *IPK* (ikon panah menunjukkan arah).
5. **Proteksi UI** — login sebagai **staf**: tombol hapus berganti ikon 🔒 (disabled), menu *Pengguna & Role* tidak muncul.
6. **Quick Login Demo** — `/login` → panel *Quick Login Demo* → tombol **Admin** / **Staf**.

### 6.1 Uji Bypass URL / Request Manual (Security Proof)

**a) Staf menembak `DELETE` lewat cURL** (salin cookie sesi staf dari browser):
```bash
curl -X POST "http://127.0.0.1:8000/admin/mahasiswa/1" \
     -H "X-HTTP-Method-Override: DELETE" \
     --cookie "laravel_session=<SESSION_STAF>" \
     -d "_token=<CSRF_TOKEN>" \
     -w "\nHTTP %{http_code}\n"
# → HTTP 403  (UnauthorizedException dari permission:hapus-data)
```

**b) Staf membuka halaman admin-only:**
```bash
curl -o /dev/null -w "HTTP %{http_code}\n" \
     --cookie "laravel_session=<SESSION_STAF>" \
     "http://127.0.0.1:8000/admin/pengguna"
# → HTTP 403
```

**c) Pengguna tanpa role menembak panel admin** → `HTTP 403` (`role:admin|staf`).

**d) Guest membuka `/admin/dashboard`** → `HTTP 302` ke `/login`.

**e) Admin melakukan hal yang sama** → `302` (redirect sukses, data benar-benar terhapus).

> Hasil verifikasi nyata pada proyek ini (server `php artisan serve` + cURL):
> ```
> demo-staf: 302 -> /admin/tugas
> staf DELETE mahasiswa: 403
> staf GET pengguna:    403
> staf GET dosen:       200
> ```

### 6.2 Automated Test
```bash
php artisan test
```
```
Tests:    52 passed (157 assertions)
```
Cakupan: RBAC (403/302), filter & pencarian multi-kolom, `withQueryString`, whitelist sorting (anti injection), validasi form, widget agregat, quick login, serta seluruh test bawaan Breeze.

---

## 7. Cuplikan Implementasi Kunci

### 7.1 Rute berproteksi berlapis (`routes/web.php`)
```php
Route::middleware(['auth', 'role:admin|staf'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('mahasiswa', MahasiswaController::class)->except(['destroy']);
        Route::delete('mahasiswa/{mahasiswa}', [MahasiswaController::class, 'destroy'])
            ->middleware('permission:hapus-data')      // lapis 1
            ->name('mahasiswa.destroy');

        Route::get('pengguna', [UserController::class, 'index'])
            ->middleware('role:admin')                 // eksklusif admin
            ->name('users.index');
    });
```

### 7.2 Controller middleware (Laravel 11+ `HasMiddleware`)
```php
class MahasiswaController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:kelola-mahasiswa')];
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        Gate::authorize('hapus-data');   // lapis 2: otorisasi eksplisit
        $mahasiswa->delete();

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil dihapus.');
    }
}
```

### 7.3 Kueri filter bertingkat `when()` + pagination locking
```php
$mahasiswas = Mahasiswa::query()
    ->with('departemen')
    ->cari($filters['search'])                                    // nama OR nrp OR email
    ->when($filters['departemen_id'], fn (Builder $q, int $id) => $q->where('departemen_id', $id))
    ->when($filters['status'], fn (Builder $q, string $s) => $q->where('is_active', $s === 'aktif'))
    ->when($filters['angkatan'], fn (Builder $q, int $a) => $q->where('angkatan', $a))
    ->orderBy($sort, $direction)                                  // kolom dari whitelist
    ->paginate(10)
    ->withQueryString();
```

### 7.4 Sub-query closure pencarian (`app/Models/Mahasiswa.php`)
```php
public function scopeCari(Builder $query, ?string $keyword): Builder
{
    return $query->when($keyword, function (Builder $q, string $keyword): Builder {
        return $q->where(function (Builder $sub) use ($keyword): void {
            $sub->where('nama', 'like', "%{$keyword}%")
                ->orWhere('nrp', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%");
        });
    });
}
```

### 7.5 Proteksi UI (Blade) — tombol hapus eksklusif admin
```blade
@can('hapus-data')
    <form method="POST" action="{{ route('admin.mahasiswa.destroy', $mahasiswa) }}"
          onsubmit="return confirm('Hapus data mahasiswa?');">
        @csrf
        @method('DELETE')
        <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
    </form>
@else
    <button class="btn btn-xs btn-outline-secondary" disabled title="Hanya admin">
        <i class="fas fa-lock"></i>
    </button>
@endcan
```

### 7.6 Pengalihan multi-role (`app/Models/User.php` + `AuthenticatedSessionController`)
```php
public function homeRouteName(): string
{
    if ($this->hasRole('admin')) {
        return 'admin.dashboard';
    }

    if ($this->hasRole('staf')) {
        return 'admin.tugas.index';
    }

    return 'home';
}

// Auth/AuthenticatedSessionController@store
return redirect()->intended(route($request->user()->homeRouteName(), absolute: false));
```

### 7.7 Quick Login Demo (`config/demo.php` + `DemoLoginController`)
```php
abort_unless((bool) config('demo.enabled'), 404);              // saklar .env
$accounts = config('demo.accounts');                            // whitelist panel
abort_unless(is_array($accounts) && array_key_exists($panel, $accounts), 404);

$user = User::query()->where('email', $accounts[$panel]['email'])->firstOrFail();
Auth::login($user, remember: false);
$request->session()->regenerate();

return redirect()->intended(route($user->homeRouteName(), absolute: false));
```

### 7.8 Menu sidebar dinamis (`config/adminlte.php`)
```php
[
    'text'  => 'Data Mahasiswa',
    'route' => 'admin.mahasiswa.index',
    'icon'  => 'fas fa-fw fa-user-graduate',
    'can'   => 'kelola-mahasiswa',   // difilter GateFilter milik AdminLTE
],
[
    'text'  => 'Pengguna & Role',
    'route' => 'admin.users.index',
    'icon'  => 'fas fa-fw fa-user-shield',
    'can'   => 'kelola-pengguna',    // hanya admin yang melihat menu ini
],
```

---

## 8. Catatan Keamanan

| Risiko | Mitigasi di Proyek Ini |
| --- | --- |
| **Bypass otorisasi** (sembunyikan tombol saja) | Middleware rute + middleware controller + `Gate::authorize()` di method destruktif |
| **SQL Injection** | Eloquent / query builder dengan PDO binding; pencarian memakai parameter binding, bukan string SQL mentah |
| **ORDER BY / kolom injection** | Whitelist kolom sorting (`ResolvesTableQuery::resolveSort`) |
| **Mass Assignment** | `$fillable` eksplisit pada semua model + `$request->validated()` |
| **XSS** | Escaping Blade default (`{{ }}`), input dicari di-escape saat dirender |
| **CSRF** | Token `@csrf` pada semua form; logout via `POST` |
| **Enumerasi akun demo di produksi** | `DEMO_LOGIN_ENABLED=false` + whitelist + `throttle:10,1` |
| **Validasi data** | Form Request terpusat (NRP/NIDN 10 digit, email unik, enum status/prioritas) |
| **Cache permission basi** | `forgetCachedPermissions()` pada seeder; `php artisan permission:cache-reset` bila perlu |

---

## 9. Perintah Harian

```bash
php artisan migrate:fresh --seed    # reset & isi ulang data demo
php artisan serve                   # jalankan server
php artisan test                    # jalankan seluruh test
vendor/bin/pint --test              # cek standar PSR-12 / style Laravel
vendor/bin/pint                     # perbaiki style otomatis
php artisan permission:cache-reset  # reset cache permission Spatie
php artisan route:list --except-vendor   # audit rute + middleware
php artisan optimize:clear          # bersihkan cache config/route/view
```

---

## 10. Git Hygiene

`.gitignore` bawaan Laravel **sudah** mengecualikan berkas sensitif dan artefak build:

```gitignore
.env
.env.backup
.env.production
/vendor
/node_modules
/public/build
/public/hot
/public/vendor
/storage/*.key
/storage/pail
.phpunit.result.cache
/.phpunit.cache
/.idea
/.vscode
*.log
```

Verifikasi cepat:
```bash
git status --short            # .env / vendor / node_modules tidak boleh muncul
git ls-files | grep -E "^\.env$|^vendor/|^node_modules/|^public/vendor/"   # harus kosong
```

Sebelum commit pertama:
```bash
git add .gitignore
git add app config database resources routes tests bootstrap composer.json composer.lock package.json vite.config.js README.md
git commit -m "feat(pbkk-10): admin dashboard AdminLTE + advanced RBAC Spatie + filters"
```

> Jangan pernah meng-commit `.env` (berisi `APP_KEY`, kredensial DB, dan saklar demo).

---

## 11. Troubleshooting

| Gejala | Penyebab & Solusi |
| --- | --- |
| `could not find driver (sqlite)` | Aktifkan `extension=pdo_sqlite` di `php.ini`, atau gunakan MySQL (`pdo_mysql`) |
| `Database file at path ... does not exist` | Buat `database/database.sqlite` (lihat langkah 5.2) |
| Menu sidebar kosong setelah seeding | `php artisan permission:cache-reset` lalu login ulang |
| Halaman login tanpa CSS/JS | Jalankan `npm install && npm run build` (aset Breeze/Tailwind) |
| Panel admin tampak tanpa style | Pastikan `php artisan adminlte:install` sudah dijalankan (`public/vendor/` tersedia) |
| Staf bisa menghapus data | Pastikan migrasi + `RoleAndPermissionSeeder` sudah dijalankan dan `permission:hapus-data` **tidak** ter-assign ke role `staf` |
| `SQLSTATE[HY000]: no such table` | Jalankan `php artisan migrate:fresh --seed` |

---

## 12. Catatan Kompatibilitas Versi

Spesifikasi tugas menyebut Laravel 10.x/11.x. Proyek ini memakai **Laravel 12.x** karena:

1. Runtime yang tersedia adalah **PHP 8.5**, sementara jalur rilis Laravel 10/11 sudah tidak menerima patch keamanan untuk versi tersebut.
2. Composer (mode audit) **memblokir** pemasangan `laravel/framework` 11.31–11.57 akibat security advisory aktif (`PKSA-*`). Memaksa instalasi berarti mengirim kerangka kerja yang rentan — bertentangan dengan fokus keamanan tugas ini.
3. Laravel 12 merupakan kelanjutan langsung Laravel 11: struktur `bootstrap/app.php`, `HasMiddleware`, Form Request, Eloquent `when()`, `withQueryString()`, dan Breeze **identik**. Satu-satunya perbedaan operasional adalah alias middleware Spatie yang didaftarkan eksplisit di `bootstrap/app.php` (sejak Laravel 11 `Kernel.php` dihapus).
4. Template AdminLTE yang dipakai (`jeroennoten/laravel-adminlte` v3.16) tetap menggunakan **AdminLTE 3.2 / Bootstrap 4** dan pola `@extends('adminlte::page')` sesuai spesifikasi.

Jika dosen/asisten mewajibkan Laravel 11 secara literal, penyesuaian hanya pada `composer.json` (`"laravel/framework": "^11.0"`) dan menghapus blokir advisory Composer; seluruh kode aplikasi, rute, model, seeder, dan view tidak perlu diubah.

---

## 13. Lisensi & Atribusi

- Kode aplikasi: dibuat untuk keperluan akademik PBKK ITS (2026).
- [Laravel](https://laravel.com) — MIT License.
- [Spatie Laravel Permission](https://github.com/spatie/laravel-permission) — MIT License.
- [Laravel-AdminLTE](https://github.com/jeroennoten/Laravel-AdminLTE) & [AdminLTE](https://adminlte.io) — MIT License.

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Quick Login Demo (Demo Assistant)
    |--------------------------------------------------------------------------
    |
    | Fitur auto-login untuk keperluan demonstrasi di depan dosen/asisten.
    | Secara default fitur ini aktif pada lingkungan non-production dan dapat
    | dimatikan total dengan menyetel DEMO_LOGIN_ENABLED=false pada berkas .env
    | (WAJIB dimatikan saat aplikasi dipublikasikan ke publik).
    |
    */

    'enabled' => filter_var(
        env('DEMO_LOGIN_ENABLED', env('APP_ENV', 'production') !== 'production'),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Akun Demo
    |--------------------------------------------------------------------------
    |
    | Hanya akun yang terdaftar pada whitelist ini yang dapat dipakai melalui
    | rute auto-login. Parameter rute divalidasi terhadap key array di bawah
    | sehingga tidak ada akun sembarang yang bisa dipilih dari URL.
    |
    */

    'accounts' => [
        'admin' => [
            'email' => 'admin@its.ac.id',
            'label' => 'Administrator',
            'description' => 'Akses penuh: CRUD, hapus data, manajemen role.',
            'icon' => 'fas fa-user-shield',
            'color' => 'danger',
        ],
        'staf' => [
            'email' => 'staf@its.ac.id',
            'label' => 'Staf Akademik',
            'description' => 'Akses terbatas: baca & ubah data, tanpa hak hapus.',
            'icon' => 'fas fa-user-tie',
            'color' => 'warning',
        ],
    ],

];

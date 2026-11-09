<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel data mahasiswa (NRP ITS 10 digit, contoh 5025241097).
     */
    public function up(): void
    {
        Schema::create('mahasiswas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('departemen_id')
                ->constrained('departemens')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->char('nrp', 10)->unique();
            $table->string('nama', 120);
            $table->string('email', 150)->unique();
            $table->unsignedSmallInteger('angkatan');
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->decimal('ipk', 3, 2)->default(3.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('nama');
            $table->index(['is_active', 'angkatan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswas');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel tugas akademik mahasiswa.
     */
    public function up(): void
    {
        Schema::create('tugas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mahasiswa_id')
                ->constrained('mahasiswas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('dosen_id')
                ->nullable()
                ->constrained('dosens')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('mata_kuliah', 120);
            $table->string('judul', 180);
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['draft', 'aktif', 'selesai', 'terlambat'])->default('aktif');
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi'])->default('sedang');
            $table->dateTime('deadline');
            $table->timestamps();

            $table->index(['status', 'deadline']);
            $table->index('mata_kuliah');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};

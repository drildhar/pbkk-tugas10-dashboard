<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel data dosen beserta relasi ke departemen.
     */
    public function up(): void
    {
        Schema::create('dosens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('departemen_id')
                ->constrained('departemens')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('nidn', 10)->unique();
            $table->string('nama', 120);
            $table->string('email', 150)->unique();
            $table->string('jabatan', 60)->default('Asisten Ahli');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'departemen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosens');
    }
};

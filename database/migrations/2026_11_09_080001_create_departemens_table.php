<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel master departemen (unit akademik FTEIC ITS).
     */
    public function up(): void
    {
        Schema::create('departemens', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 12)->unique();
            $table->string('nama', 120);
            $table->string('fakultas', 150)->default('FTEIC');
            $table->string('jenjang', 10)->default('S1');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departemens');
    }
};

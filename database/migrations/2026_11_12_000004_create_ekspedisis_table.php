<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekspedisis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kurir', 100);
            $table->string('kode', 20)->unique();
            $table->enum('jenis_layanan', ['Reguler', 'Express']);
            $table->string('zona', 100);
            $table->unsignedInteger('tarif')->default(0);
            $table->string('estimasi', 50)->nullable();
            $table->string('status', 20)->default('Aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekspedisis');
    }
};

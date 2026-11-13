<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produk', 100);
            $table->string('kategori', 50)->nullable();
            $table->string('merek', 50)->nullable();
            $table->unsignedInteger('harga');
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('berat')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('status', 20)->default('Aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};

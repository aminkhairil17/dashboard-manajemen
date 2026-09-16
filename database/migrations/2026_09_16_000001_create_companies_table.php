<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('bed_capacity')->default(120);

            // Kredensial koneksi SIMRS GOS milik entitas ini (tiap RS biasanya
            // punya instalasi sendiri). Diisi begitu akses dari tim IT RS terkait
            // tersedia — dipakai oleh SimrsGosRepository di masa depan untuk
            // switch koneksi database `simrs` secara dinamis per company.
            $table->string('simrs_db_host')->nullable();
            $table->unsignedInteger('simrs_db_port')->nullable();
            $table->string('simrs_db_database')->nullable();
            $table->string('simrs_db_username')->nullable();
            $table->text('simrs_db_password')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

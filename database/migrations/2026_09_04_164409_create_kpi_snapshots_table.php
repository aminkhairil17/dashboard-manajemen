<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('category');
            $table->string('kpi_key');
            $table->decimal('value', 14, 4);
            $table->string('unit')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['kpi_key', 'date']);
            $table->index(['category', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_snapshots');
    }
};

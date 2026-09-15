<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_targets', function (Blueprint $table) {
            $table->id();
            $table->string('kpi_key')->unique();
            $table->string('label');
            $table->decimal('min_value', 14, 4)->nullable();
            $table->decimal('max_value', 14, 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_targets');
    }
};

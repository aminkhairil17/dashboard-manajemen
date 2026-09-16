<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_snapshots', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropUnique(['kpi_key', 'date']);
            $table->unique(['company_id', 'kpi_key', 'date']);
        });

        Schema::table('kpi_targets', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropUnique(['kpi_key']);
            $table->unique(['company_id', 'kpi_key']);
        });
    }

    public function down(): void
    {
        Schema::table('kpi_snapshots', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'kpi_key', 'date']);
            $table->dropConstrainedForeignId('company_id');
            $table->unique(['kpi_key', 'date']);
        });

        Schema::table('kpi_targets', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'kpi_key']);
            $table->dropConstrainedForeignId('company_id');
            $table->unique(['kpi_key']);
        });
    }
};

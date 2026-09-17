<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lecturas', function (Blueprint $table) {
            if (! Schema::hasColumn('lecturas', 'outlier_iqr')) {
                $table->boolean('outlier_iqr')->default(false)->after('temperatura_suelo');
            }
            if (! Schema::hasColumn('lecturas', 'outlier_zscore')) {
                $table->boolean('outlier_zscore')->default(false);
            }
            if (! Schema::hasColumn('lecturas', 'outlier_isolation_forest')) {
                $table->boolean('outlier_isolation_forest')->default(false);
            }
            if (! Schema::hasColumn('lecturas', 'cluster_dbscan')) {
                $table->integer('cluster_dbscan')->nullable();
            }
            if (! Schema::hasColumn('lecturas', 'outlier_consenso')) {
                $table->boolean('outlier_consenso')->default(false);
            }
        });

        Schema::table('lecturas', function (Blueprint $table) {
            $indexes = Schema::getIndexes('lecturas');
            $names = array_column($indexes, 'name');
            if (! in_array('lecturas_estacion_fecha_unique', $names, true)) {
                $table->unique(['estacion_id', 'fecha_lectura'], 'lecturas_estacion_fecha_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lecturas', function (Blueprint $table) {
            $indexes = Schema::getIndexes('lecturas');
            $names = array_column($indexes, 'name');
            if (in_array('lecturas_estacion_fecha_unique', $names, true)) {
                $table->dropUnique('lecturas_estacion_fecha_unique');
            }

            $drop = [];
            foreach (['outlier_iqr', 'outlier_zscore', 'outlier_isolation_forest', 'cluster_dbscan', 'outlier_consenso'] as $col) {
                if (Schema::hasColumn('lecturas', $col)) {
                    $drop[] = $col;
                }
            }
            if ($drop) {
                $table->dropColumn($drop);
            }
        });
    }
};

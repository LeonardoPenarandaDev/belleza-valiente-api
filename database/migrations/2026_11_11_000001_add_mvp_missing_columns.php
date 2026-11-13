<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'eliminada_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('eliminada_at')->nullable()->after('terminos_aceptados_at');
            });
        }

        if (Schema::hasTable('profesionales') && !Schema::hasColumn('profesionales', 'activa')) {
            Schema::table('profesionales', function (Blueprint $table) {
                $table->boolean('activa')->default(true)->after('verificada');
            });
        }

        if (Schema::hasTable('reservas') && !Schema::hasColumn('reservas', 'cancelada_por')) {
            Schema::table('reservas', function (Blueprint $table) {
                $table->string('cancelada_por', 20)->nullable()->after('motivo_rechazo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'eliminada_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('eliminada_at');
            });
        }

        if (Schema::hasTable('profesionales') && Schema::hasColumn('profesionales', 'activa')) {
            Schema::table('profesionales', function (Blueprint $table) {
                $table->dropColumn('activa');
            });
        }

        if (Schema::hasTable('reservas') && Schema::hasColumn('reservas', 'cancelada_por')) {
            Schema::table('reservas', function (Blueprint $table) {
                $table->dropColumn('cancelada_por');
            });
        }
    }
};

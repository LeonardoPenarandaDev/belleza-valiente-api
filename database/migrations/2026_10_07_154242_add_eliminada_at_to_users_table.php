<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fecha en que la usuaria eliminó su cuenta. La fila se conserva anonimizada
     * porque las reservas la referencian (Ley 1581 y requisito de Google Play).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('eliminada_at')->nullable()->after('terminos_aceptados_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('eliminada_at');
        });
    }
};

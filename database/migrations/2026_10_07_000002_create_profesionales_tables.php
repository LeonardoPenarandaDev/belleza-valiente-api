<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perfil profesional, zonas que atiende, servicios que ofrece y portafolio.
     */
    public function up(): void
    {
        Schema::create('profesionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->unsignedTinyInteger('anios_experiencia')->nullable();
            // La fundación verifica en persona; solo las verificadas aparecen a las clientas.
            $table->boolean('verificada')->default(false)->index();
            // Se recalculan cada vez que se registra una calificación.
            $table->decimal('calificacion_promedio', 3, 2)->default(0);
            $table->unsignedInteger('total_calificaciones')->default(0);
            $table->timestamps();
        });

        Schema::create('profesional_zona', function (Blueprint $table) {
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->foreignId('zona_id')->constrained('zonas')->cascadeOnDelete();

            $table->primary(['profesional_id', 'zona_id']);
        });

        Schema::create('servicio_profesional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->restrictOnDelete();
            $table->unsignedInteger('precio');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['profesional_id', 'servicio_id']);
        });

        Schema::create('fotos_portafolio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->string('ruta');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fotos_portafolio');
        Schema::dropIfExists('servicio_profesional');
        Schema::dropIfExists('profesional_zona');
        Schema::dropIfExists('profesionales');
    }
};

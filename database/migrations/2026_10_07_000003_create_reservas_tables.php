<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Citas y calificaciones.
     */
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            // restrictOnDelete: el historial de citas no se pierde al borrar usuarios.
            $table->foreignId('cliente_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('profesional_id')->constrained('profesionales')->restrictOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->restrictOnDelete();
            $table->foreignId('zona_id')->constrained('zonas')->restrictOnDelete();

            $table->dateTime('fecha_hora');
            // Copiados al crear la cita para que no cambien si la profesional edita sus precios.
            $table->unsignedSmallInteger('duracion_minutos');
            $table->unsignedInteger('precio');

            $table->string('direccion');
            $table->text('notas')->nullable();

            // Ver App\Enums\EstadoReserva.
            $table->string('estado', 20)->default('solicitada');
            $table->string('motivo_rechazo')->nullable();

            // Se llenan al completar (ver App\Enums\MetodoPago).
            $table->string('metodo_pago', 20)->nullable();
            $table->unsignedInteger('valor_pagado')->nullable();
            $table->unsignedInteger('comision')->nullable();

            $table->timestamp('aceptada_at')->nullable();
            $table->timestamp('completada_at')->nullable();
            $table->timestamp('cancelada_at')->nullable();
            $table->timestamps();

            $table->index(['profesional_id', 'estado', 'fecha_hora']);
            $table->index(['cliente_id', 'fecha_hora']);
        });

        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->unique()->constrained('reservas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->unsignedTinyInteger('estrellas');
            $table->text('comentario')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
        Schema::dropIfExists('reservas');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Zonas de cobertura, categorías y catálogo base de servicios.
     */
    public function up(): void
    {
        Schema::create('zonas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('icono')->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->unsignedSmallInteger('duracion_minutos')->default(60);
            // Pesos colombianos, sin decimales.
            $table->unsignedInteger('precio_referencia')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['categoria_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('zonas');
    }
};

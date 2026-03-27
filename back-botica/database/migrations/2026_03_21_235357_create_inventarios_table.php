<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('codigo')->nullable();
            $table->integer('cantidad')->nullable();
            $table->boolean('flag_blister')->nullable()->default(false);
            $table->string('precio_blister')->nullable();
            $table->integer('cantidad_blister')->nullable();
            $table->boolean('flag_unidad')->nullable()->default(false);
            $table->string('precio_unidad')->nullable();
            $table->string('stock_minimo')->nullable();
            $table->string('precio_oficial')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('registro_sanitario')->nullable();
            $table->string('lote')->nullable();
            $table->string('ubicacion')->nullable();
            $table->decimal('precio', 8, 2)->nullable();
            $table->boolean('flag_disponible')->nullable()->default(true);
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedors')->onDelete('set null');
            $table->foreignId('presentacion_id')->nullable()->constrained('presentacions')->onDelete('set null');
            $table->foreignId('laboratorio_id')->nullable()->constrained('laboratorios')->onDelete('set null');
            $table->foreignId('area_id')->nullable()->constrained('areas')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};

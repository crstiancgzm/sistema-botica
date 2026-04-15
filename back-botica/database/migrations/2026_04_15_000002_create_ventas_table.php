<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas')->onDelete('restrict');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->string('cliente_nombre')->default('Consumidor Final');
            $table->string('cliente_dni', 8)->nullable();
            $table->enum('tipo_comprobante', ['boleta', 'factura', 'sin_comprobante'])->default('boleta');
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia'])->default('efectivo');
            $table->decimal('total', 10, 2);
            $table->decimal('monto_recibido', 10, 2)->nullable();
            $table->decimal('vuelto', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};

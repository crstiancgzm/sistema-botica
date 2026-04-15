<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->enum('turno', ['mañana', 'tarde']);
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->decimal('monto_inicial', 10, 2)->default(0);
            $table->decimal('monto_final', 10, 2)->nullable();
            $table->timestamp('hora_apertura')->useCurrent();
            $table->timestamp('hora_cierre')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_apertura_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('usuario_cierre_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Máximo 2 cajas por día: una por turno
            $table->unique(['fecha', 'turno']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};

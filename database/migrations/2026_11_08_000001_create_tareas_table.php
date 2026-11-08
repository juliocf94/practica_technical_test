<?php

use App\Enums\EstadoTarea;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->enum('estado', EstadoTarea::values())->default(EstadoTarea::Pendiente->value);
            $table->date('fecha_limite');
            $table->timestamps();

            $table->index(['user_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};

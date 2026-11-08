<?php

namespace App\Models;

use App\Enums\EstadoTarea;
use Database\Factories\TareaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarea extends Model
{
    /** @use HasFactory<TareaFactory> */
    use HasFactory;

    protected $table = 'tareas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'titulo',
        'descripcion',
        'estado',
        'fecha_limite',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'estado' => 'pendiente',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoTarea::class,
            'fecha_limite' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Filtra por estado cuando se indica uno: Tarea::estado('pendiente').
     */
    public function scopeEstado(Builder $query, ?string $estado): Builder
    {
        return $query->when($estado, fn (Builder $q) => $q->where('estado', $estado));
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\EstadoTarea;

/**
 * Mensajes para las validaciones.
 */
final class TareaValidationMessages
{
    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => 'El campo :attribute no puede superar los :max caracteres.',
            'estado.Illuminate\Validation\Rules\Enum' => 'El estado debe ser uno de: '.implode(', ', EstadoTarea::values()).'.',
            'date_format' => 'El campo :attribute debe tener el formato AAAA-MM-DD.',
            'after_or_equal' => 'La :attribute no puede ser anterior a hoy.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributes(): array
    {
        return [
            'titulo' => 'título',
            'descripcion' => 'descripción',
            'estado' => 'estado',
            'fecha_limite' => 'fecha límite',
        ];
    }
}

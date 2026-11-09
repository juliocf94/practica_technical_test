<?php

namespace App\Http\Requests;

use App\Enums\EstadoTarea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Permite actualizaciones parciales: solo se validan los campos enviados,
 * pero si un campo obligatorio llega, no puede venir vacío.
 */
class UpdateTareaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La propiedad de la tarea se comprueba con TareaPolicy en el controlador
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['sometimes', 'required', 'string', 'max:255'],
            'descripcion' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'estado' => ['sometimes', 'required', Rule::enum(EstadoTarea::class)],
            'fecha_limite' => ['sometimes', 'required', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return TareaValidationMessages::messages();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return TareaValidationMessages::attributes();
    }
}

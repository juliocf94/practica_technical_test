<?php

namespace App\Http\Requests;

use App\Enums\EstadoTarea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTareaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La ruta ya exige autenticación (auth:sanctum)
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'estado' => ['required', Rule::enum(EstadoTarea::class)],
            'fecha_limite' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
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

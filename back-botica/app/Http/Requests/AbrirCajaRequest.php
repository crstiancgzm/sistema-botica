<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AbrirCajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'turno'         => 'required|in:mañana,tarde',
            'monto_inicial' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'turno.required' => 'El turno es obligatorio.',
            'turno.in'       => 'El turno debe ser "mañana" o "tarde".',
            'monto_inicial.required' => 'El monto inicial es obligatorio.',
            'monto_inicial.numeric'  => 'El monto inicial debe ser un número.',
            'monto_inicial.min'      => 'El monto inicial no puede ser negativo.',
        ];
    }
}

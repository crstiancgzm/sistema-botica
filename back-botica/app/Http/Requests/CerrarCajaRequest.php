<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CerrarCajaRequest extends FormRequest
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
            'monto_final'   => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'monto_final.required' => 'El monto final es obligatorio.',
            'monto_final.numeric'  => 'El monto final debe ser un número.',
            'monto_final.min'      => 'El monto final no puede ser negativo.',
        ];
    }
}

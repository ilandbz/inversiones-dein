<?php

namespace App\Http\Requests\EvaluacionPrestamo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEvaluacionPrestamoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id'          => 'required|integer|exists:evaluacion_prestamos,id',
            'credito_id'  => 'required|integer|exists:creditos,id',
            'user_id'     => 'required|integer|exists:users,id',
            'cargo'       => 'required|string|max:100',
            'estado'      => 'required|string|max:60',
            'observacion' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'required'  => '* Dato Obligatorio',
            'integer'   => 'Ingrese un valor numérico entero',
            'exists'    => 'El registro seleccionado no existe',
            'max'       => 'Ingrese Máximo :max caracteres',
            'string'    => 'Ingrese caracteres alfanuméricos',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Credito;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'asesor_id' => 'required|exists:asesors,id',
            'aval_id' => 'nullable|exists:personas,id',
            'tipo' => 'required',
            'monto' => 'required|numeric|min:0',
            'origen_financiamiento_id' => 'required|exists:origen_financiamientos,id',
            'frecuencia' => 'required|in:DIARIA,SEMANAL,QUINCENAL,MENSUAL,DIARIO',
            'plazo' => 'required|integer|min:1',
            'tasainteres' => 'required|numeric|min:0',
            'costomora' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'interes' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'exists' => 'El :attribute seleccionado no es válido.',
            'numeric' => 'El campo :attribute debe ser un número.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'min' => 'El campo :attribute debe ser al menos :min.',
            'in' => 'La frecuencia seleccionada no es válida.',
        ];
    }
}

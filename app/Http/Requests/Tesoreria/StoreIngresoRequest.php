<?php

namespace App\Http\Requests\Tesoreria;

use Illuminate\Foundation\Http\FormRequest;

class StoreIngresoRequest extends FormRequest
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
            'monto'           => 'required|numeric|min:0',
            'descripcion'     => 'required|string|max:65535',
        ];
    }

    public function messages()
    {
        return [
            'monto.required'           => 'El monto es obligatorio.',
            'monto.numeric'            => 'El monto debe ser un número.',
            'monto.min'                => 'El monto no puede ser negativo.',
            'descripcion.required'     => 'La descripción es obligatoria.',
            'descripcion.string'       => 'La descripción debe ser un texto.',
            'descripcion.max'          => 'La descripción no puede superar los 65535 caracteres.',
        ];
    }

}
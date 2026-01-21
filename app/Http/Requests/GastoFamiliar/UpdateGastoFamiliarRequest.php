<?php

namespace App\Http\Requests\GastoFamiliar;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGastoFamiliarRequest extends FormRequest
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
            'credito_id'         => 'required|integer|exists:creditos,id',
            'alimentacion'       => 'required|numeric|min:0',
            'alquileres'         => 'required|numeric|min:0',
            'educacion'          => 'required|numeric|min:0',
            'servicios'          => 'required|numeric|min:0',
            'transporte'         => 'required|numeric|min:0',
            'salud'              => 'required|numeric|min:0',
            'otros'              => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'required' => '* Dato Obligatorio',
            'max' => 'Ingrese Máximo :max caracteres',
            'string' => 'Ingrese caracteres alfanuméricos',
            'number' => 'Ingrese solo numeros',
            'unique' => 'El valor ya existe'
        ];
    }

}
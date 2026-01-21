<?php

namespace App\Http\Requests\GastoNegocio;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGastoNegocioRequest extends FormRequest
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
            'credito_id'    => 'required|integer|exists:creditos,id',
            'alquiler'           => 'required|numeric|min:0',
            'servicios'          => 'required|numeric|min:0',
            'personal'           => 'required|numeric|min:0',
            'sunat'              => 'required|numeric|min:0',
            'transporte'         => 'required|numeric|min:0',
            'gastosfinancieros'  => 'required|numeric|min:0',
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
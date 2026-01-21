<?php

namespace App\Http\Requests\PropuestaCredito;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropuestaCreditoRequest extends FormRequest
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
            'credito_id' => 'required|integer|exists:creditos,id',
            'unidad_familiar' => 'required',
            'experiencia_cred' => 'required',
            'destino_prest' => 'required',
            'referencias' => 'required',
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
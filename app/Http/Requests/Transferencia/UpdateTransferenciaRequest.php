<?php

namespace App\Http\Requests\Transferencia;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransferenciaRequest extends FormRequest
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
            'de_entidad_id' => 'required|exists:tipo_entidads,id',
            'de_agencia_id' => 'required|exists:agencias,id',
            'a_entidad_id'  => 'required|exists:tipo_entidads,id',
            'a_agencia_id'  => 'required|exists:agencias,id',
            'monto'         => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'required' => '* Dato Obligatorio',
            'exists'   => 'El valor seleccionado no es válido',
            'numeric'  => 'Ingrese solo números',
            'min'      => 'El valor debe ser mayor o igual a :min',
            'date'     => 'Ingrese una fecha válida',
            'date_format' => 'Ingrese una hora válida con el formato HH:MM',
        ];
    }

}
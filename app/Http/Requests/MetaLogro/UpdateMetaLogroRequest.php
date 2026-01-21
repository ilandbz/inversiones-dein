<?php

namespace App\Http\Requests\MetaLogro;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMetaLogroRequest extends FormRequest
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
            'tipo'            => 'required|in:META,LOGRO',
            'nivel_meta'      => 'required|in:ASESOR,GERENCIA',
            'agencia_id'      => 'nullable|exists:agencias,id',
            'asesor'          => 'nullable|exists:users,name',
            'creditos_mora'   => 'required',
            'cartera'         => 'required|numeric|min:0',
            'clientes'        => 'required|integer|min:0',
            'desembolsados'   => 'required|numeric|min:0',
            'saldomora'       => 'required|numeric|min:0',
            'clientesnuevos'  => 'required|integer|min:0',
            'fecharegistro'   => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'required' => '* Dato Obligatorio',
            'in'       => 'Seleccione una opción válida',
            'exists'   => 'El valor seleccionado no existe',
            'date'     => 'Ingrese una fecha válida',
            'numeric'  => 'Ingrese un número válido',
            'integer'  => 'Ingrese un número entero',
            'min'      => 'El valor mínimo permitido es :min',
        ];
    }

}
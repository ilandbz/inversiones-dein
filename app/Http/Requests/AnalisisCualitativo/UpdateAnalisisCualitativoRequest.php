<?php

namespace App\Http\Requests\AnalisisCualitativo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnalisisCualitativoRequest extends FormRequest
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
            'tipogarantia' => 'required|numeric|in:2,4',
            'cargafamiliar' => 'required|numeric|in:0,1,2,4',
            'riesgoedadmax' => 'required|numeric|in:1,3',
            'antecedentescred' => 'required|numeric|in:1,3,4,5',
            'recorpagoult' => 'required|numeric|in:0,2,5,7',
            'niveldesarr' => 'required|numeric|in:1,2,3,4',
            'tiempo_neg' => 'required|numeric|in:1,2,3',
            'control_ingegre' => 'required|numeric|in:1,2,3',
            'vent_totdec' => 'required|numeric|in:0,2',
            'compsubsector' => 'required|numeric|in:0,2,4',
            'totunidfamiliar' => 'required|numeric|min:0',
            'totunidempresa' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
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
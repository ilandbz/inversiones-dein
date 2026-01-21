<?php

namespace App\Http\Requests\HistoriaPersonal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHistoriaPersonalRequest extends FormRequest
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
            'dni'          => 'required|size:8|alpha_num',
            'mes'          => 'required|integer|between:1,12',
            'nro'          => 'required|integer|min:1',
            'cantidad'     => 'required|numeric|min:0',
            'tipo'         => 'nullable|string|max:20',
            'saldo'        => 'required|numeric|min:0',
            'fecharreg'    => 'required|date',
            'descripcion'  => 'required|string|max:60',
            'codusuario'   => 'required|size:9|alpha_num',
            'tiporegistro' => 'required|string|max:40',
            'anho'         => 'required|integer|min:1900|max:'.date('Y'),
        ];
    }

    public function messages()
    {
        return [
            'required'    => '* Dato Obligatorio',
            'max'         => 'Ingrese Máximo :max caracteres',
            'size'        => 'El campo debe tener exactamente :size caracteres',
            'alpha_num'   => 'Ingrese caracteres alfanuméricos',
            'string'      => 'Ingrese texto válido',
            'integer'     => 'Ingrese un número entero',
            'numeric'     => 'Ingrese un valor numérico',
            'min'         => 'Valor mínimo permitido: :min',
            'between'     => 'Debe estar entre :min y :max',
            'date'        => 'Ingrese una fecha válida',
            'unique'      => 'Ya existe un registro con estos datos',
        ];
    }

}
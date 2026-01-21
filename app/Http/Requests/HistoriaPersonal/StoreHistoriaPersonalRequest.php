<?php

namespace App\Http\Requests\HistoriaPersonal;

use Illuminate\Foundation\Http\FormRequest;

class StoreHistoriaPersonalRequest extends FormRequest
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
            'dni'          => 'required|size:8|exists:personas,dni',
            'mes'          => 'required',
            'monto'     => 'required|numeric|min:0',
            'tipo'         => 'nullable|string|max:20',
            'descripcion'  => 'required|string|max:250',
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
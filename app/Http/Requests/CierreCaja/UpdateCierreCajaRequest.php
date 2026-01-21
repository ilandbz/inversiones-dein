<?php

namespace App\Http\Requests\CierreCaja;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCierreCajaRequest extends FormRequest
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
            'agencia_id'    => 'required|exists:agencias,id',
            'fecha'         => 'required|date',
            'hora'          => 'required|date_format:H:i',
            'billetaje_id'  => 'required|exists:billetajes,id',
            'user_id'       => 'required|exists:users,id',
            'total'         => 'required|numeric|min:0',
            'observacion'   => 'nullable|string|max:255',
            'f2000'         => 'required|integer|min:0',
            'f1000'         => 'required|integer|min:0',
            'b200'          => 'required|integer|min:0',
            'b100'          => 'required|integer|min:0',
            'b50'           => 'required|integer|min:0',
            'b20'           => 'required|integer|min:0',
            'b10'           => 'required|integer|min:0',
            'm5'            => 'required|integer|min:0',
            'm2'            => 'required|integer|min:0',
            'm1'            => 'required|integer|min:0',
            'm50'           => 'required|integer|min:0',
            'm20'           => 'required|integer|min:0',
            'm10'           => 'required|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'required'       => '* Dato Obligatorio',
            'exists'         => 'El valor seleccionado no es válido',
            'integer'        => 'Ingrese solo números enteros',
            'date'           => 'Ingrese una fecha válida',
            'date_format'    => 'El formato de hora debe ser HH:MM',
            'numeric'        => 'Ingrese solo números',
            'min'            => 'El valor debe ser mayor o igual a :min',
            'string'         => 'Ingrese caracteres alfanuméricos',
            'max'            => 'Ingrese Máximo :max caracteres',
        ];
    }

}
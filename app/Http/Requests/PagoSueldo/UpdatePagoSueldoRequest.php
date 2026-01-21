<?php

namespace App\Http\Requests\PagoSueldo;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePagoSueldoRequest extends FormRequest
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
            'personal_id'  => 'required|integer|exists:personals,id',
            'mes'          => 'required|integer|between:1,12',
            'fechareg'     => 'required|date',
            'afp'          => 'nullable|numeric|min:0',
            'adelantos'    => 'nullable|numeric|min:0',
            'dsctos'       => 'nullable|numeric|min:0',
            'movilidad'    => 'nullable|numeric|min:0',
            'alimentacion' => 'nullable|numeric|min:0',
            'bonificacion' => 'nullable|numeric|min:0',
            'asignacion'   => 'nullable|numeric|min:0',
            'sueldobasico' => 'nullable|numeric|min:0',
            'total'        => 'nullable|numeric|min:0',
            'codusuario'   => 'required|size:9|alpha_num',
            'anho'         => 'required|integer|min:1900|max:' . date('Y'),
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
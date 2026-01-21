<?php

namespace App\Http\Requests\Persona;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonaRequest extends FormRequest
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
            'dni'           => 'required|max:25|digits:8|unique:personas,dni',
            'ape_pat'       => 'required',
            'ape_mat'       => 'required',
            'primernombre'  => 'required',
            'ubigeo'        => 'digits:6',
            'celular'       => 'required|digits:9'
        ];
    }

    public function messages()
    {
        return [
            'required' => '* Dato Obligatorio',
            'string' => 'Ingrese caracteres alfanuméricos',
            'unique' => 'El valor ya existe',
            'dni.digits' => 'Solo 8 digitos numericos',
            'celular.digits' => 'Solo 9 digitos',
            'ubigeo.digits' => 'Solo 6 digitos',
        ];
    }

}
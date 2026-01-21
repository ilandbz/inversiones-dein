<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'user_id'               => 'required',
            'username'              => 'required|unique:users,username,'.$this->user_id,
            'primernombre'          => 'required|string',
            'apellido_paterno'      => 'required|string',
            'apellido_materno'      => 'required|string',
            'sexo'                  => 'required|string',
            'fecha_nacimiento'      => 'required',
            'estado_civil_id'       => 'required',
            'celular'               => 'required',
            'email'                 => 'required|email',
            'tienehijos'            => 'required',
            'profesion_id'          => 'required',
        ];
    }

    public function messages()
    {
        return [
            'required' => '* Dato Obligatorio',
            'max' => 'Ingrese Máximo :max caracteres',
            'string' => 'Ingrese caracteres alfanuméricos',
            'number' => 'Ingrese solo numeros',
            'unique'    => 'El valor ya existe en la base de datos'

        ];
    }

}
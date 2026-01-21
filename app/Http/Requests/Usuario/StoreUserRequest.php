<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'username'              => 'required|unique:users,name,',
            'dni'                   => 'required|digits:8',
            'apepat'                => 'required|string|max:100',
            'apemat'                => 'required|string|max:100',
            'primernombre'         => 'required|string|max:100',
            'celular'               => 'required|string|max:100',
            'role_id'               => 'required',
        ];
    }

    public function messages()
    {
        return [
            'required' => '* Dato Obligatorio',
            'max' => 'Ingrese Máximo :max caracteres',
            'digits' => 'Ingrese Máximo :digits numeros',
            'string' => 'Ingrese caracteres alfanuméricos',
            'numeric' => 'Ingrese solo numeros',
            'username.unique'    => 'El username ya existe en la base de datos',
            'email'         => 'No es un email valido'

        ];
    }

}
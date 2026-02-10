<?php

namespace App\Http\Requests\Propiedad;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropiedadRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'     => 'required|exists:propiedads,id',
            'nombre' => 'required|string|max:100|unique:propiedads,nombre,' . $this->id
        ];
    }

    public function messages()
    {
        return [
            'required' => '* Dato obligatorio',
            'max'      => 'Máximo :max caracteres',
            'string'   => 'Ingrese caracteres válidos',
            'unique'   => 'El valor ya existe'
        ];
    }
}

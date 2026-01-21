<?php

namespace App\Http\Requests\PagoCampo;

use Illuminate\Foundation\Http\FormRequest;

class StorePagoCampoRequest extends FormRequest
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
            'codigo'     => 'required|max:255',
            'monto'      => 'required|numeric|min:0|max:9999999.99',
            'tipo'       => 'required|in:CREDITO,JUNTA',
            'moradias'   => 'required|integer|min:0',
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
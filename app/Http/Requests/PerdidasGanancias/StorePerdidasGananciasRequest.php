<?php

namespace App\Http\Requests\PerdidasGanancias;

use Illuminate\Foundation\Http\FormRequest;

class StorePerdidasGananciasRequest extends FormRequest
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
            'ventas' => 'required|numeric|min:0',
            'costo' => 'required|numeric|min:0',
            'utilidad' => 'required|numeric|min:0',
            'costonegocio' => 'required|numeric|min:0',
            'utiloperativa' => 'required|numeric|min:0',
            'otrosing' => 'required|numeric|min:0',
            'otrosegr' => 'required|numeric|min:0',
            'gast_fam' => 'required|numeric|min:0',
            'utilidadneta' => 'required|numeric|min:0',
            'utilnetdiaria' => 'required|numeric|min:0',
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
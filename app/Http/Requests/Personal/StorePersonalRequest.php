<?php

namespace App\Http\Requests\Personal;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonalRequest extends FormRequest
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
            'ape_pat'           => 'required',
            'ape_mat'           => 'required',
            'cargo_id'          => 'required|numeric|exists:cargos,id',
            'fecha_inicio'      => 'required|date',
            'sueldo'            => 'required|numeric:min:0',
            'tipo'              => 'required',
            'agencia_id'        => 'required',
            'primernombre'      => 'required',
            'fecha_nac'         => 'required|date|after_or_equal:1900-01-01|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'ubigeo_nac'            => 'required|digits:6|exists:distritos,ubigeo',
            'email'             => 'nullable|email',
            'celular'           => 'required|digits:9',
            'ruc'               => 'nullable|digits:11',
            'tipodomicilio'     => 'required|string|max:50',
            'ubigeodomicilio'  => 'required|digits:6|exists:distritos,ubigeo',
            'tipovia'          => 'required|string|max:35',
            'nombrevia'        => 'nullable|string|max:90',
            'nro'              => 'nullable|string|max:25',
            'interior'         => 'nullable|string|max:25',
            'mz'               => 'nullable|string|max:25',
            'lote'             => 'nullable|string|max:25',
            'tipozona'         => 'string|max:35',
            'nombrezona'       => 'nullable|string|max:50',
            'referencia'       => 'nullable|string|max:90',
            'latitud_longitud' => 'nullable|string|max:60',
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
            'before_or_equal' => 'Debe ser mayor de 18 años',
        ];
    }

}
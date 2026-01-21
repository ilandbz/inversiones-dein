<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
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
            'dni'               => 'required|max:25|digits:8',
            'ape_pat'           => 'required',
            'ape_mat'           => 'required',
            'primernombre'      => 'required',
            'fecha_nac'         => 'required|date|after_or_equal:1900-01-01|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'ubigeo'            => 'required|digits:6|exists:distritos,ubigeo',
            'email'             => 'nullable|email',
            'celular'           => 'required|digits:9',
            'genero'            => 'required|in:M,F',
            'estado_civil'      => 'required|string|max:50',
            'ruc'               => 'nullable|digits:11',
            'grado_instr'       => 'required',
            'tipo_trabajador'   => 'required|string|max:50',
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
            'foto'              => 'nullable|image|max:2048',
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
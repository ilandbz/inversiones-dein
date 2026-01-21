<?php

namespace App\Http\Requests\Ubicacion;

use Illuminate\Foundation\Http\FormRequest;

class StoreUbicacionRequest extends FormRequest
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
            'tipo'             => 'nullable|string|max:30',
            'ubigeo'           => 'required|digits:6|exists:distritos,ubigeo',
            'tipovia'          => 'nullable|string|max:35',
            'nombrevia'        => 'nullable|string|max:90',
            'nro'              => 'nullable|string|max:25',
            'interior'         => 'nullable|string|max:25',
            'mz'               => 'nullable|string|max:25',
            'lote'             => 'nullable|string|max:25',
            'tipozona'         => 'nullable|string|max:35',
            'nombrezona'       => 'nullable|string|max:50',
            'referencia'       => 'nullable|string|max:90',
            'latitud_longitud' => 'nullable|string|max:60',
        ];
    }

    public function messages()
    {
        return [
            'tipo.string'             => 'El campo "Tipo" debe ser un texto.',
            'tipo.max'                => 'El campo "Tipo" no debe superar los 30 caracteres.',
            'ubigeo.required'         => 'El campo "Ubigeo" es obligatorio.',
            'ubigeo.digits'           => 'El campo "Ubigeo" debe tener exactamente 6 dígitos.',
            'tipovia.string'          => 'El campo "Tipo de Vía" debe ser un texto.',
            'tipovia.max'             => 'El campo "Tipo de Vía" no debe superar los 35 caracteres.',
            'nombrevia.string'        => 'El campo "Nombre de la Vía" debe ser un texto.',
            'nombrevia.max'           => 'El campo "Nombre de la Vía" no debe superar los 90 caracteres.',
            'nro.string'              => 'El campo "Número" debe ser un texto.',
            'nro.max'                 => 'El campo "Número" no debe superar los 25 caracteres.',
            'interior.string'         => 'El campo "Interior" debe ser un texto.',
            'interior.max'            => 'El campo "Interior" no debe superar los 25 caracteres.',
            'mz.string'               => 'El campo "Manzana" debe ser un texto.',
            'mz.max'                  => 'El campo "Manzana" no debe superar los 25 caracteres.',
            'lote.string'             => 'El campo "Lote" debe ser un texto.',
            'lote.max'                => 'El campo "Lote" no debe superar los 25 caracteres.',
            'tipozona.string'         => 'El campo "Tipo de Zona" debe ser un texto.',
            'tipozona.max'            => 'El campo "Tipo de Zona" no debe superar los 35 caracteres.',
            'nombrezona.string'       => 'El campo "Nombre de Zona" debe ser un texto.',
            'nombrezona.max'          => 'El campo "Nombre de Zona" no debe superar los 50 caracteres.',
            'referencia.string'       => 'El campo "Referencia" debe ser un texto.',
            'referencia.max'          => 'El campo "Referencia" no debe superar los 90 caracteres.',
            'latitud_longitud.string' => 'El campo "Latitud y Longitud" debe ser un texto.',
            'latitud_longitud.max'    => 'El campo "Latitud y Longitud" no debe superar los 60 caracteres.',
        ];
    }

}
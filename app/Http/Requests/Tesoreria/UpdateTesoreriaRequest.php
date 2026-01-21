<?php

namespace App\Http\Requests\Tesoreria;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTesoreriaRequest extends FormRequest
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
            'tipo_entidad_id' => 'required|exists:tipo_entidads,id',
            'agencia_id'      => 'required|exists:agencias,id',
            'nro'             => 'required|integer|min:1',
            'fecha'           => 'required|date',
            'hora'            => 'required|date_format:H:i:s',
            'tipo'            => 'required|string|max:18|in:Ingreso,Egreso',
            'user_id'         => 'required|exists:users,id',
            'monto'           => 'required|numeric|min:0',
            'saldo'           => 'required|numeric|min:0',
            'descripcion'     => 'required|string|max:65535',
        ];
    }

    public function messages()
    {
        return [
            'tipo_entidad_id.required' => 'El campo tipo entidad es obligatorio.',
            'tipo_entidad_id.exists'   => 'El tipo entidad seleccionado no es válido.',
            'agencia_id.required'      => 'El campo agencia es obligatorio.',
            'agencia_id.exists'        => 'La agencia seleccionada no es válida.',
            'nro.required'             => 'El número es obligatorio.',
            'nro.integer'              => 'El número debe ser un valor entero.',
            'nro.min'                  => 'El número debe ser mayor a 0.',
            'fecha.required'           => 'La fecha es obligatoria.',
            'fecha.date'               => 'La fecha no tiene un formato válido.',
            'hora.required'            => 'La hora es obligatoria.',
            'hora.date_format'         => 'La hora debe estar en formato HH:MM:SS.',
            'tipo.required'            => 'El tipo es obligatorio.',
            'tipo.string'              => 'El tipo debe ser un texto.',
            'tipo.max'                 => 'El tipo no puede tener más de 18 caracteres.',
            'tipo.in'                  => 'El tipo debe ser "Ingreso" o "Egreso".',
            'user_id.required'         => 'El usuario es obligatorio.',
            'user_id.exists'           => 'El usuario seleccionado no es válido.',
            'monto.required'           => 'El monto es obligatorio.',
            'monto.numeric'            => 'El monto debe ser un número.',
            'monto.min'                => 'El monto no puede ser negativo.',
            'saldo.required'           => 'El saldo es obligatorio.',
            'saldo.numeric'            => 'El saldo debe ser un número.',
            'saldo.min'                => 'El saldo no puede ser negativo.',
            'descripcion.required'     => 'La descripción es obligatoria.',
            'descripcion.string'       => 'La descripción debe ser un texto.',
            'descripcion.max'          => 'La descripción no puede superar los 65535 caracteres.',
        ];
    }

}
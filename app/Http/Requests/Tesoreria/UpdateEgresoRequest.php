<?php

namespace App\Http\Requests\Tesoreria;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEgresoRequest extends FormRequest
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
            'tipo'               => 'required|string|in:GASTO,COSTO',
            'tipo_descripcion'  => 'required|string|max:30',
            'tipo_comprobante'  => 'required|string|max:30',
            'ruc'                => 'required|digits:11',
            'nrocomprobante'     => 'required|string|max:20',
            'razonsocial'        => 'required|string|max:90',
            'concepto'           => 'required|string|max:120',
            'monto'              => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'agencia_id.required'        => 'La agencia es obligatoria.',
            'agencia_id.exists'          => 'La agencia seleccionada no existe.',
            'nro.required'               => 'El número de operación es obligatorio.',
            'nro.integer'                => 'El número debe ser un número entero.',
            'nro.min'                    => 'El número debe ser mayor a cero.',
            'tipo.required'              => 'El tipo es obligatorio.',
            'tipo.string'                => 'El tipo debe ser texto.',
            'tipo.in'                    => 'El tipo debe ser INGRE o EGRES.',
            'tipo_descripcion.required' => 'La descripción del tipo es obligatoria.',
            'tipo_descripcion.max'      => 'La descripción del tipo no puede exceder 30 caracteres.',
            'tipo_comprobante.required' => 'El tipo de comprobante es obligatorio.',
            'tipo_comprobante.max'      => 'El tipo de comprobante no puede exceder 30 caracteres.',
            'ruc.required'              => 'El RUC es obligatorio.',
            'ruc.digits'                => 'El RUC debe tener exactamente 11 dígitos.',
            'nrocomprobante.required'   => 'El número de comprobante es obligatorio.',
            'nrocomprobante.max'        => 'El número de comprobante no puede exceder 20 caracteres.',
            'razonsocial.required'      => 'La razón social es obligatoria.',
            'razonsocial.max'           => 'La razón social no puede exceder 90 caracteres.',
            'concepto.required'         => 'El concepto es obligatorio.',
            'concepto.max'              => 'El concepto no puede exceder 90 caracteres.',
            'monto.required'            => 'El monto es obligatorio.',
            'monto.numeric'             => 'El monto debe ser un número.',
            'monto.min'                 => 'El monto no puede ser negativo.',
            'user_id.required'          => 'El usuario es obligatorio.',
            'user_id.exists'            => 'El usuario seleccionado no existe.',
            'fecha.required'            => 'La fecha es obligatoria.',
            'fecha.date'                => 'La fecha no tiene un formato válido.',
            'hora.required'             => 'La hora es obligatoria.',
            'hora.date_format'          => 'La hora debe estar en formato HH:MM:SS.',
        ];
    }

}
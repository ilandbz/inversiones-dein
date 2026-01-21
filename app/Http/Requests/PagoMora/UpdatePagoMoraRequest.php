<?php

namespace App\Http\Requests\PagoMora;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePagoMoraRequest extends FormRequest
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
            'credito_id'   => 'required|exists:desembolsos,credito_id|integer',
            'montopagado'  => 'required|numeric|min:0|max:9999999.99',
            'morapagar'  => 'required|numeric|min:0.10|max:9999999.99',
            'mediopago'    => 'required|string|max:35',
            'moradias'     => 'nullable|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'credito_id.required'   => 'El crédito es obligatorio.',
            'credito_id.exists'     => 'El crédito seleccionado no es válido.',
            'nro.required'          => 'El número es obligatorio.',
            'nro.integer'           => 'El número debe ser un entero.',
            'fecha.required'        => 'La fecha es obligatoria.',
            'fecha.date'            => 'La fecha debe ser válida.',
            'montopagado.required'  => 'El monto pagado es obligatorio.',
            'montopagado.numeric'   => 'El monto pagado debe ser un número.',
            'user_id.required'      => 'El usuario es obligatorio.',
            'user_id.exists'        => 'El usuario no es válido.',
            'mediopago.required'    => 'El medio de pago es obligatorio.',
            'mediopago.string'      => 'El medio de pago debe ser una cadena de texto.',
            'moradias.integer'      => 'Las moradías deben ser un número entero.',
            'morapagar.min'        => 'El monto a pagar por mora no puede ser menor a 0.10',
        ];
    }


}
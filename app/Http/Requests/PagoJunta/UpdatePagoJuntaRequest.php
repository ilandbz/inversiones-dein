<?php

namespace App\Http\Requests\PagoJunta;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePagoJuntaRequest extends FormRequest
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
            'junta_id'   => 'required|exists:juntas,id|integer',
            'montopagado'  => 'required|numeric|min:0|max:9999999.99',
            'mediopago'    => 'required|string|max:35',
        ];
    }

    public function messages()
    {
        return [
            'junta_id.required'   => 'El campo "Junta" es obligatorio.',
            'junta_id.exists'     => 'La junta seleccionada no existe en el sistema.',
            'junta_id.integer'    => 'El campo "Junta" debe ser un número entero.',
    
            'montopagado.required'  => 'El campo "Monto Pagado" es obligatorio.',
            'montopagado.numeric'   => 'El "Monto Pagado" debe ser un número.',
            'montopagado.min'       => 'El "Monto Pagado" no puede ser menor a 0.',
            'montopagado.max'       => 'El "Monto Pagado" no puede superar 9,999,999.99.',
    
            'mediopago.required' => 'El campo "Medio de Pago" es obligatorio.',
            'mediopago.string'   => 'El "Medio de Pago" debe ser un texto.',
            'mediopago.max'      => 'El "Medio de Pago" no puede superar los 35 caracteres.',
        ];
    }

}
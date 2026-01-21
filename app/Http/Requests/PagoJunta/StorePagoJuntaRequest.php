<?php

namespace App\Http\Requests\PagoJunta;

use Illuminate\Foundation\Http\FormRequest;

class StorePagoJuntaRequest extends FormRequest
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
            'totalpagar'  => 'required|numeric|min:0|max:9999999.99',
            'mediopago'    => 'required|string|max:35',
            'moradias'   => 'integer|min:0|max:9999999',
        ];
    }

    public function messages()
    {
        return [
            'junta_id.required'   => 'El campo "Junta" es obligatorio.',
            'junta_id.exists'     => 'La junta seleccionada no existe en el sistema.',
            'junta_id.integer'    => 'El campo "Junta" debe ser un número entero.',
    
            'totalpagar.required'  => 'El campo "Monto Pagado" es obligatorio.',
            'totalpagar.numeric'   => 'El "Monto Pagado" debe ser un número.',
            'totalpagar.min'       => 'El "Monto Pagado" no puede ser menor a 0.',
            'totalpagar.max'       => 'El "Monto Pagado" no puede superar 9,999,999.99.',
    
            'mediopago.required' => 'El campo "Medio de Pago" es obligatorio.',
            'mediopago.string'   => 'El "Medio de Pago" debe ser un texto.',
            'mediopago.max'      => 'El "Medio de Pago" no puede superar los 35 caracteres.',
        ];
    }

}
<?php

namespace App\Http\Requests\Negocio;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNegocioRequest extends FormRequest
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
            'cliente_id' => 'required|integer|exists:clientes,id',
            'razonsocial' => 'required|string|max:80',
            'tel_cel' => 'nullable|integer|digits:9',
            'tipo_actividad_id' => 'required|integer|exists:tipo_actividads,id',
            'descripcion' => 'required|string|max:90',
            'inicioactividad' => 'required|date',
            'digits' => 'Debe ser 9 digitos',
            'ubicacion_id' => 'required',          
        ];
    }

    public function messages()
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio.',
            'cliente_id.exists' => 'El cliente seleccionado no es válido.',
            
            'agencia_id.required' => 'La agencia es obligatoria.',
            'agencia_id.exists' => 'La agencia seleccionada no es válida.',
            
            'asesor_id.required' => 'El asesor es obligatorio.',
            'asesor_id.exists' => 'El asesor seleccionado no es válido.',
    
            'estado.required' => 'El estado es obligatorio.',
            'estado.string' => 'El estado debe ser un texto.',
            'estado.max' => 'El estado no debe superar los 40 caracteres.',
    
            'fecha_reg.required' => 'La fecha de registro es obligatoria.',
            'fecha_reg.date' => 'La fecha de registro debe ser una fecha válida.',
    
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.string' => 'El tipo debe ser un texto.',
            'tipo.max' => 'El tipo no debe superar los 40 caracteres.',
    
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.between' => 'El monto debe estar entre 0 y 999999999.99.',
    
            'producto.required' => 'El producto es obligatorio.',
            'producto.string' => 'El producto debe ser un texto.',
            'producto.max' => 'El producto no debe superar los 30 caracteres.',
    
            'frecuencia.required' => 'La frecuencia es obligatoria.',
            'frecuencia.string' => 'La frecuencia debe ser un texto.',
            'frecuencia.max' => 'La frecuencia no debe superar los 30 caracteres.',
    
            'plazo.required' => 'El plazo es obligatorio.',
            'plazo.integer' => 'El plazo debe ser un número entero.',
            'plazo.min' => 'El plazo debe ser al menos 1.',
            'plazo.max' => 'El plazo no debe superar los 99999.',
    
            'medioorigen.required' => 'El medio de origen es obligatorio.',
            'medioorigen.string' => 'El medio de origen debe ser un texto.',
            'medioorigen.max' => 'El medio de origen no debe superar los 50 caracteres.',
    
            'dondepagara.string' => 'Dónde pagará debe ser un texto.',
            'dondepagara.max' => 'Dónde pagará no debe superar los 40 caracteres.',
    
            'fuenterecursos.required' => 'La fuente de recursos es obligatoria.',
            'fuenterecursos.string' => 'La fuente de recursos debe ser un texto.',
            'fuenterecursos.max' => 'La fuente de recursos no debe superar los 50 caracteres.',
    
            'tasainteres.numeric' => 'La tasa de interés debe ser un número.',
            'tasainteres.between' => 'La tasa de interés debe estar entre 0 y 99.99.',
    
            'total.numeric' => 'El total debe ser un número.',
            'total.between' => 'El total debe estar entre 0 y 999999999.99.',
    
            'costomora.numeric' => 'El costo de mora debe ser un número.',
            'costomora.between' => 'El costo de mora debe estar entre 0 y 99.99.',
    
            'creditos_seleccionados.required_if' => 'Debe seleccionar créditos si el tipo es "Recurrente Con Saldo".',
            'creditos_seleccionados.array' => 'Los créditos seleccionados deben ser un arreglo.',
        ];
    }

}
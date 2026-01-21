<?php

namespace App\Http\Requests\Junta;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJuntaRequest extends FormRequest
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
            'dni_cliente'       => 'required|string|size:8|regex:/^\d{8}$/',
            'apenom_cliente'    => 'required|string|max:50',
            'asesor_id'         => 'required|exists:users,id',
            'tipoplazo'         => 'required|string|max:15',
            'plazo'             => 'required|integer|min:1|max:999',
            'frecuencia'        => 'required|string|max:15',
            'nrocuotas'         => 'required|integer|min:1|max:9999',
            'monto'             => 'required|numeric|min:0|max:999999.99',
            'fechainicio'       => 'required|date',
            'descripcion'       => 'required|string|max:90',
            'estado'            => 'required|string|in:REGISTRADO,APROBADO,FINALIZADO,DEVUELTO',
            'dondepagara'       => 'required|string|in:Oficina,Campo',
        ];
    }

    public function messages()
    {
        return [
            'cliente_id.required'   => '* Cliente es obligatorio.',
            'cliente_id.exists'     => '* Cliente seleccionado no es válido.',
            
            'agencia_id.required'   => '* Agencia es obligatoria.',
            'agencia_id.exists'     => '* Agencia seleccionada no es válida.',
    
            'asesor_id.required'    => '* Asesor es obligatorio.',
            'asesor_id.exists'      => '* Asesor seleccionado no es válido.',
    
            'tipoplazo.required'    => '* Tipo de plazo es obligatorio.',
            'tipoplazo.string'      => '* Tipo de plazo debe ser texto.',
            'tipoplazo.max'         => '* Tipo de plazo debe tener máximo 15 caracteres.',
    
            'plazo.required'        => '* Plazo es obligatorio.',
            'plazo.integer'         => '* Plazo debe ser un número entero.',
            'plazo.min'             => '* Plazo debe ser mínimo 1.',
            'plazo.max'             => '* Plazo no puede ser mayor a 999.',
    
            'frecuencia.required'   => '* Frecuencia es obligatoria.',
            'frecuencia.string'     => '* Frecuencia debe ser texto.',
            'frecuencia.max'        => '* Frecuencia debe tener máximo 15 caracteres.',
    
            'nrocuotas.required'    => '* Número de cuotas es obligatorio.',
            'nrocuotas.integer'     => '* Número de cuotas debe ser un número entero.',
            'nrocuotas.min'         => '* Número de cuotas debe ser mínimo 1.',
            'nrocuotas.max'         => '* Número de cuotas no puede ser mayor a 9999.',
    
            'fecha_registro.required' => '* Fecha de registro es obligatoria.',
            'fecha_registro.date'     => '* Fecha de registro debe ser una fecha válida.',
    
            'hora_registro.required'  => '* Hora de registro es obligatoria.',
            'hora_registro.date_format' => '* Hora de registro debe tener el formato HH:mm:ss.',
    
            'monto.required'         => '* Monto es obligatorio.',
            'monto.numeric'          => '* Monto debe ser un valor numérico.',
            'monto.min'              => '* Monto no puede ser negativo.',
            'monto.max'              => '* Monto no puede superar 999999.99.',
    
            'fechainicio.required'   => '* Fecha de inicio es obligatoria.',
            'fechainicio.date'       => '* Fecha de inicio debe ser una fecha válida.',
    
            'descripcion.string'     => '* Descripción debe ser texto.',
            'descripcion.max'        => '* Descripción debe tener máximo 90 caracteres.',
    
            'estado.required'        => '* Estado es obligatorio.',
            'estado.string'          => '* Estado debe ser texto.',
            'estado.in'              => '* Estado no es válido.',
    
            'dondepagara.required'   => '* Lugar de pago es obligatorio.',
            'dondepagara.string'     => '* Lugar de pago debe ser texto.',
            'dondepagara.in'         => '* Lugar de pago debe ser Oficina o Campo.',
    
            'total.numeric'          => '* Total debe ser un valor numérico.',
            'total.min'              => '* Total no puede ser negativo.',
            'total.max'              => '* Total no puede superar 9999999.99.',
        ];
    }

}
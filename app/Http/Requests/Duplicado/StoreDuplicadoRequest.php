<?php

namespace App\Http\Requests\Duplicado;

use App\Models\Credito;
use App\Models\Junta;
use Illuminate\Foundation\Http\FormRequest;

class StoreDuplicadoRequest extends FormRequest
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
            'tipo'      => 'required|in:CREDITO,JUNTA',
            'codigo' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (request('tipo') === 'CREDITO' &&
                        !Credito::where('id', $value)->whereIn('estado', ['DESEMBOLSADO', 'FINALIZADO'])->exists()) {
                        $fail('El código no existe en la tabla créditos.');
                    }
                    if (request('tipo') === 'JUNTA' &&
                        !Junta::where('id', $value)->whereIn('estado', ['APROBADO', 'FINALIZADO'])->exists()) {
                        $fail('El código no existe en la tabla juntas.');
                    }
                }
            ],
            'monto'     => 'required|numeric|min:1',
        ];
    }


    public function messages()
    {
        return [
            'required'      => '* Dato Obligatorio',
            'max'           => 'Ingrese Máximo :max caracteres',
            'string'        => 'Ingrese caracteres alfanuméricos',
            'numeric'       => 'Ingrese solo números',
            'date'          => 'Ingrese una fecha válida',
            'date_format'   => 'El formato de hora debe ser HH:MM',
            'exists'        => 'El valor seleccionado no existe en la base de datos',
            'in'            => 'El valor seleccionado no es válido',
            'min'           => 'El monto mínimo permitido es :min',
            'integer'       => 'El campo :attribute debe ser un número entero',
        ];
    }

}
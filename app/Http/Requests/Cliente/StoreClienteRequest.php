<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
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
            'origen_labor'      => 'required|string|max:50',
            'ubigeo'            => 'required|digits:6|exists:distritos,ubigeo',
            'direccion'          => 'required|string|max:35',
            'referencia'       => 'nullable|string|max:90',
            'latitud_longitud' => 'nullable|string|max:60',
            'foto' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'], // 4MB (ajusta)
        ];
    }

    public function messages()
    {
        return [
            'required' => '* Dato Obligatorio',
            'max' => 'Ingrese Máximo :max caracteres',
            'string' => 'Ingrese caracteres alfanuméricos',
            'dni.digits' => 'Solo 8 digitos numericos',
            'celular.digits' => 'Solo 9 digitos',
            'ubigeo.digits' => 'Solo 6 digitos',
            'ubigeodomicilio.digits' => 'Solo 6 digitos',
            'number' => 'Ingrese solo numeros',
            'unique' => 'El valor ya existe',
            'fecha_nac.before_or_equal' => 'Debe ser mayor de 18 años.',
            'fecha_nac.after_or_equal' => 'La fecha de nacimiento no puede ser anterior al 1 de enero de 1900.',
            'foto.image' => 'La foto debe ser una imagen.',
            'foto.max'   => 'La foto no debe superar 4MB.',
            'foto.mimes' => 'Formatos permitidos: JPG, PNG, WEBP.',

        ];
    }

}
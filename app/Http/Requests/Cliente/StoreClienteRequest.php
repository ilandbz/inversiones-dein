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
        $rules = [
            'dni'               => 'required|digits:8',
            'ape_pat'           => 'required|string|max:60',
            'ape_mat'           => 'required|string|max:60',
            'primernombre'      => 'required|string|max:70',
            'otrosnombres'      => 'nullable|string|max:70',

            'fecha_nac'         => 'required|date|after_or_equal:1900-01-01|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'ubigeo_nac' => 'required|digits:6|exists:distritos,ubigeo',
            'ubigeo_dom' => 'required|digits:6|exists:distritos,ubigeo',

            'email'             => 'nullable|email|max:120',
            'celular'           => 'required|digits:9',
            'celular2'          => 'nullable|digits:9',

            'genero'            => 'required|in:M,F',
            'estado_civil'      => 'required|string|max:50',
            'ruc'               => 'nullable|digits:11',

            'grado_instr'       => 'required|string|max:80',
            'profesion'         => 'nullable|string|max:80',

            'origen_labor'      => 'required|string|in:INDEPENDIENTE,DEPENDIENTE',
            'ocupacion'         => 'nullable|string|max:80',
            'institucion_lab'   => 'nullable|string|max:120',

            'direccion'         => 'required|string|max:35',
            'referencia'        => 'nullable|string|max:90',
            'latitud_longitud'  => 'nullable|string|max:60',

            'foto' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];


        $rules['referente.primernombre'] = 'required|string|max:70';
        $rules['referente.ape_pat']      = 'required|string|max:60';
        $rules['referente.ape_mat']      = 'required|string|max:60';
        $rules['referente.dni']          = 'required|digits:8';
        $rules['referente.celular']      = 'required|digits:9';
        $rules['referente.parentesco']   = 'required|string|max:60';

        // Opcionales
        $rules['referente.otrosnombres'] = 'nullable|string|max:70';
        $rules['referente.email']        = 'nullable|email|max:120';
        $rules['referente.direccion']    = 'required|string|max:120';

        // ✅ Reglas condicionales para negocio (solo si INDEPENDIENTE)
        $rules['negocio.razonsocial']          = 'required_if:origen_labor,INDEPENDIENTE|string|max:120';
        $rules['negocio.tipo_actividad_id']    = 'required_if:origen_labor,INDEPENDIENTE|integer|exists:actividad_negocios,id';
        $rules['negocio.detalle_actividad_id'] = 'required_if:origen_labor,INDEPENDIENTE|integer|exists:detalle_actividad_negocios,id';
        $rules['negocio.ruc']                  = 'nullable|digits:11';
        $rules['negocio.tel_cel']              = 'nullable|digits:9';
        $rules['negocio.tel_cel_referido']     = 'nullable|digits:9';


        $rules['negocio.inicioactividad']      = 'nullable|date|after_or_equal:1900-01-01|before_or_equal:today';
        $rules['negocio.direccion']            = 'required|string|max:120';



        return $rules;


    }

    public function messages()
    {
        return [
            'required' => '* Dato Obligatorio',
            'required_if' => '* Dato Obligatorio',
            'max' => 'Ingrese Máximo :max caracteres',
            'string' => 'Ingrese caracteres alfanuméricos',
            'integer' => 'Ingrese un valor válido',
            'digits' => 'Ingrese :digits dígitos',
            'email' => 'Email inválido',
            'exists' => 'El valor seleccionado no existe',
            'unique' => 'El valor ya existe',

            'dni.digits' => 'Solo 8 digitos numericos',
            'celular.digits' => 'Solo 9 digitos',
            'celular2.digits' => 'Solo 9 digitos',
            'ubigeo.digits' => 'Solo 6 digitos',
            'ruc.digits' => 'Solo 11 digitos',

            'fecha_nac.before_or_equal' => 'Debe ser mayor de 18 años.',
            'fecha_nac.after_or_equal'  => 'La fecha de nacimiento no puede ser anterior al 1 de enero de 1900.',

            // Foto
            'foto.image' => 'La foto debe ser una imagen.',
            'foto.max'   => 'La foto no debe superar 4MB.',
            'foto.mimes' => 'Formatos permitidos: JPG, PNG, WEBP.',

            // ✅ Negocio (mensajes específicos si quieres)
            'negocio.razonsocial.required_if' => '* Dato Obligatorio',
            'negocio.razonsocial.max'         => 'Ingrese Máximo :max caracteres',
            'negocio.tel_cel.digits'          => 'Solo 9 digitos',
            'negocio.ruc.digits'              => 'Solo 11 digitos',
        ];
    }

}
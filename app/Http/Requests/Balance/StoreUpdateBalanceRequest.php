<?php

namespace App\Http\Requests\Balance;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateBalanceRequest extends FormRequest
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
            'credito_id' => ['required', 'integer', 'exists:creditos,id'],

            'fecha' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'max:30'],

            // Numericos principales
            'activocaja' => ['nullable', 'numeric', 'min:0'],
            'activobancos' => ['nullable', 'numeric', 'min:0'],
            'activoctascobrar' => ['nullable', 'numeric', 'min:0'],
            'activoinventarios' => ['nullable', 'numeric', 'min:0'],
            'activomueble' => ['nullable', 'numeric', 'min:0'],
            'activootrosact' => ['nullable', 'numeric', 'min:0'],
            'activodepre' => ['nullable', 'numeric', 'min:0'],

            'pasivodeudaprove' => ['nullable', 'numeric', 'min:0'],
            'pasivodeudaent' => ['nullable', 'numeric', 'min:0'],
            'pasivolargop' => ['nullable', 'numeric', 'min:0'],
            'otrascuentaspagar' => ['nullable', 'numeric', 'min:0'],

            // Totales (puedes aceptarlos o recalcularlos en backend)
            'total_activo' => ['nullable', 'numeric'],
            'total_pasivo' => ['nullable', 'numeric'],
            'patrimonio' => ['nullable', 'numeric'],
            'paspatrimonio' => ['nullable', 'numeric'],
            'captrabajo' => ['nullable', 'numeric'],

            // Detalle inventario 1–1
            'detinventarios' => ['nullable', 'array'],
            'detinventarios.inv_materiales' => ['nullable', 'numeric', 'min:0'],
            'detinventarios.inv_prodproc' => ['nullable', 'numeric', 'min:0'],
            'detinventarios.inv_prodtermi' => ['nullable', 'numeric', 'min:0'],

            // Detalle muebles 1–N
            'muebles' => ['nullable', 'array'],
            'muebles.*.id' => ['nullable', 'integer'], // para update/sync
            'muebles.*.descripcion' => ['required_with:muebles', 'string', 'max:255'],
            'muebles.*.valor' => ['nullable', 'numeric', 'min:0'],

            // Detalle deudas 1–N
            'deudas' => ['nullable', 'array'],
            'deudas.*.id' => ['nullable', 'integer'],
            'deudas.*.entidad' => ['required_with:deudas', 'string', 'max:150'],
            'deudas.*.saldo' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages()
    {
        return [
            'required' => '* Dato Obligatorio',
            'max' => 'Ingrese Máximo :max caracteres',
            'string' => 'Ingrese caracteres alfanuméricos',
            'number' => 'Ingrese solo numeros',
            'unique' => 'El valor ya existe'
        ];
    }
}

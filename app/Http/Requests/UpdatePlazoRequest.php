<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlazoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id'          => 'required|exists:plazos,id',
            'origen_financiamiento_id' => 'required|exists:origen_financiamientos,id',
            'frecuencia'  => 'required|string|max:50',
            'plazo'       => 'required|integer|min:1',
            'tasainteres' => 'required|numeric|min:0',
            'costomora'   => 'required|numeric|min:0',
            'gastos_administrativos' => 'nullable|numeric|min:0',
        ];
    }
}

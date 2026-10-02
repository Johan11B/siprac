<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFincaRequest extends FormRequest
{
    /**
     * La Policy (FincaPolicy@update) se encarga de la autorización.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_finca'      => ['required', 'string', 'max:80'],
            'vereda'            => ['required', 'string', 'max:50'],
            'municipio'         => ['required', 'string', 'max:50'],
            'latitud'           => ['nullable', 'numeric', 'between:-90,90'],
            'longitud'          => ['nullable', 'numeric', 'between:-180,180'],
            'altitud_msnm'      => ['required', 'integer', 'min:-500', 'max:9000'],
            'area_hectareas'    => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'cultivo_principal' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_finca.required'      => 'El nombre de la finca es obligatorio.',
            'nombre_finca.max'           => 'El nombre no puede superar los 80 caracteres.',
            'vereda.required'            => 'La vereda es obligatoria.',
            'municipio.required'         => 'El municipio es obligatorio.',
            'latitud.between'            => 'La latitud debe estar entre -90 y 90.',
            'longitud.between'           => 'La longitud debe estar entre -180 y 180.',
            'altitud_msnm.required'      => 'La altitud es obligatoria.',
            'altitud_msnm.integer'       => 'La altitud debe ser un numero entero.',
            'area_hectareas.required'    => 'El area en hectareas es obligatoria.',
            'area_hectareas.numeric'     => 'El area debe ser un numero.',
            'area_hectareas.min'         => 'El area debe ser mayor a 0.',
            'cultivo_principal.required' => 'El cultivo principal es obligatorio.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Option; // Import the Option model
use App\Models\Product; // Import the Product model

class StoreOptionRequest extends FormRequest
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
            'product_id'   => [
                'required',
                'exists:products,id',
                // Ensure the product associated is of type 'option_group'
                Rule::exists('products', 'id')->where(function ($query) {
                    $query->where('type', '!=', Product::TYPE_PACK);
                }),
            ],
            'image_url'                     => ['nullable', 'url', 'max:255'],
            'status'                        => ['required', Rule::in([Option::STATUS_ACTIVE, Option::STATUS_INACTIVE])],
            'price'                         => ['required', 'numeric', 'min:0'],
            'translations'                  => ['required', 'array', 'min:1'],
            'translations.*.locale'         => ['required', 'string', 'max:5'],
            'translations.*.name'           => ['required', 'string', 'max:255'],
            'translations.*.description'    => ['nullable', 'string'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'product_id.required'               => 'El ID del producto es obligatorio.',
            'product_id.exists'                 => 'El producto seleccionado no existe o es de tipo pack".',            
            'image_url.url'                     => 'La URL de la imagen debe ser una URL válida.',
            'status.required'                   => 'El estado de la opción es obligatorio.',
            'status.in'                         => 'El estado de la opción no es válido.',
            'price.required'                    => 'El precio de la opción es obligatorio.',
            'price.numeric'                     => 'El precio de la opción debe ser un número.',
            'price.min'                         => 'El precio de la opción no puede ser negativo.',
            'translations.required'             => 'Se requiere al menos una traducción para la opción.',
            'translations.array'                => 'Las traducciones deben ser un array.',
            'translations.min'                  => 'Debe proporcionar al menos una traducción para la opción.',
            'translations.*.locale.required'    => 'El código de idioma para cada traducción es obligatorio.',
            'translations.*.name.required'      => 'El nombre de la opción es obligatorio para cada traducción.',
            'translations.*.name.max'           => 'El nombre de la opción no puede exceder los :max caracteres.',
        ];
    }
}
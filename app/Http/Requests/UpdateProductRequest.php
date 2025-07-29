<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('product')->id;

        $rules = [
            'main_image_url'                => 'nullable|url|max:255',
            'status'                        => 'required|string|in:' . Product::STATUS_ACTIVE . ',' . Product::STATUS_INACTIVE,
            'type'                          => 'required|string|in:' . Product::TYPE_SIMPLE . ',' . Product::TYPE_PACK . ',' . Product::TYPE_OPTION_GROUP,
            // Validación para las traducciones en español
            'translations.es.name'          => 'required|string|max:255',
            'translations.es.description'   => 'nullable|string',                                 
            'price' => 'required|numeric|min:0',
        ];


        // Reglas condicionales para el tipo 'pack'
        if ($this->input('type') === Product::TYPE_PACK) {
            $rules['pack_products'] = ['required', 'array', 'min:1'];
            // Debe ser un ID de producto existente
            $rules['pack_products.*.product_id'] = ['required', 'exists:products,id']; 
            $rules['pack_products.*.quantity'] = ['required', 'integer', 'min:1'];
        }
        
        return $rules;
    }
}

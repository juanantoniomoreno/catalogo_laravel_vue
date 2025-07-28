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
        
        return [
            'main_image_url'                => 'nullable|url|max:255',
            'status'                        => 'required|string|in:' . Product::STATUS_ACTIVE . ',' . Product::STATUS_INACTIVE,
            'type'                          => 'required|string|in:' . Product::TYPE_SIMPLE . ',' . Product::TYPE_PACK . ',' . Product::TYPE_OPTION_GROUP,
            // Validación para las traducciones en español
            'translations.es.name'          => 'required|string|max:255',
            'translations.es.description'   => 'nullable|string',                                 
            'price' => 'required|numeric|min:0',
        ];
    }
}

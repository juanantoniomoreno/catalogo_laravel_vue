<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; 
use App\Models\Offer; 

class StoreOfferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool    {
        
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
            // El precio de la oferta es obligatorio, numérico y no puede ser negativo
            'offer_price'  => ['required', 'numeric', 'min:0'],
            'start_date'   => ['required', 'date', 'after_or_equal:today'], // Fecha de inicio no anterior a hoy
            'end_date'     => ['required', 'date', 'after_or_equal:start_date'], // Fecha de fin no anterior a la de inicio
            // El estado debe ser uno de los valores definidos en el modelo Offer
            'status'       => ['required', Rule::in([Offer::STATUS_ACTIVE, Offer::STATUS_INACTIVE, Offer::STATUS_SCHEDULED, Offer::STATUS_EXPIRED])],
            // product_id es obligatorio y debe existir en la tabla 'products'
            'product_id'   => ['required', 'exists:products,id'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [            
            'offer_price.required'      => 'El precio de la oferta es obligatorio.',
            'offer_price.numeric'       => 'El precio de la oferta debe ser un número.',
            'offer_price.min'           => 'El precio de la oferta no puede ser negativo.',
            'start_date.required'       => 'La fecha de inicio es obligatoria.',
            'start_date.date'           => 'La fecha de inicio debe ser una fecha válida.',
            'start_date.after_or_equal' => 'La fecha de inicio no puede ser anterior al día de hoy.',
            'end_date.required'         => 'La fecha de fin es obligatoria.',
            'end_date.date'             => 'La fecha de fin debe ser una fecha válida.',
            'end_date.after_or_equal'   => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
            'status.required'           => 'El estado de la oferta es obligatorio.',
            'status.in'                 => 'El estado de la oferta no es válido. Los valores permitidos son: activa, inactiva, expirada y programada.',
            'product_id.required'       => 'El ID del producto es obligatorio para la oferta.',
            'product_id.exists'         => 'El producto especificado no existe.',
        ];
    }
}
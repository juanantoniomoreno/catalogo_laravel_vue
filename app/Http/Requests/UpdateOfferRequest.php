<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Necesario para la regla Rule::in()
use App\Models\Offer; // Necesario para acceder a las constantes de estado

class UpdateOfferRequest extends FormRequest
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
            'offer_price'  => ['sometimes', 'numeric', 'min:0'],
            'start_date'   => ['sometimes', 'date', 'after_or_equal:today'], 
            'end_date'     => ['sometimes', 'date', 'after_or_equal:start_date'],
            'status'       => ['sometimes', Rule::in([Offer::STATUS_ACTIVE, Offer::STATUS_INACTIVE, Offer::STATUS_SCHEDULED, Offer::STATUS_EXPIRED])],
            'product_id'   => ['sometimes', 'exists:products,id'],
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
            'offer_price.sometimes'     => 'El precio de la oferta debe ser un número.',
            'offer_price.numeric'       => 'El precio de la oferta debe ser un número.',
            'offer_price.min'           => 'El precio de la oferta no puede ser negativo.',
            'start_date.sometimes'      => 'La fecha de inicio debe ser una fecha válida.',
            'start_date.date'           => 'La fecha de inicio debe ser una fecha válida.',
            'start_date.after_or_equal' => 'La fecha de inicio no puede ser anterior a la actual.',            
            'end_date.sometimes'        => 'La fecha de fin debe ser una fecha válida.',
            'end_date.date'             => 'La fecha de fin debe ser una fecha válida.',
            'end_date.after_or_equal'   => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
            'status.sometimes'          => 'El estado de la oferta no es válido.',
            'status.in'                 => 'El estado de la oferta no es válido. Los valores permitidos son: activa, inactiva, expirada y programada.',
            'product_id.sometimes'      => 'El ID del producto debe ser un número entero.',
            'product_id.exists'         => 'El producto especificado no existe.',
        ];
    }
}
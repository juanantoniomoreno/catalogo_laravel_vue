<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer; // Importa el modelo Offer
use Illuminate\Http\Request;
use App\Http\Requests\StoreOfferRequest; // Importa los Form Requests
use App\Http\Requests\UpdateOfferRequest;
use Illuminate\Support\Facades\Log; // Para logs de errores
use Illuminate\Support\Facades\DB; // Para transacciones de DB

class OfferController extends Controller
{
    /**
     * Display a listing of the offers.
     */
    public function index(Request $request)
    {
        try {
            $offers = Offer::with('product')->orderBy('start_date', 'desc')->get();

            $formattedOffers = $offers->map(function ($offer) {                                
                $formattedOffer = $offer->toArray();
                
                $formattedOffer['product_name'] = null;                

                // Si hay un producto asociado y tiene traducciones
                if ($offer->product && $offer->product->translations->isNotEmpty()) {                    
                    $translation = $offer->product->translations->firstWhere('locale', app()->getLocale());                    
                    
                    if (!$translation) {
                        $translation = $offer->product->translations->first();
                    }

                    if ($translation) {
                        $formattedOffer['product_name'] = $translation->name;                        
                    }
                }
                
                return $formattedOffer;
            });

            return response()->json($formattedOffers);
        } catch (\Exception $e) {
            Log::error(
                sprintf('Error fetching offers: %s ', $e->getMessage()),
                ['trace' => $e->getTraceAsString()]
            );
            return response()->json([
                'message' => 'Internal error when getting offers.'
            ], 500);
        }
    }

    /**
     * Store a newly created offer in storage.
     */
    public function store(StoreOfferRequest $request)
    {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            // Crea la oferta con el 'product_id' y 'price'
            $offer = Offer::create($validatedData);

            DB::commit();
            $offer->load('product');

            return response()->json($offer, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(
                sprintf('Error creating offer: %s', $e->getMessage()),
                ['trace' => $e->getTraceAsString()]
            );
            return response()->json([
                'message' => 'Internal error when creating offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified offer.
     */
    public function show(Offer $offer)
    {
        try {            
            $offer->load(['product.translations']);                 
            
            // Formatea la respuesta para incluir el nombre y descripción traducidos
            $formattedOffer = $offer->toArray();
            $formattedOffer['product_name'] = null;
            
            if ($offer->product && $offer->product->translations->isNotEmpty()) {
                $translation = $offer->product->translations->firstWhere('locale', app()->getLocale());
                if (!$translation) {
                    $translation = $offer->product->translations->first();
                }
                if ($translation) {
                    $formattedOffer['product_name'] = $translation->name;                    
                }
            }
            unset($formattedOffer['product']);

            return response()->json($formattedOffer);
        } catch (\Exception $e) {
            Log::error(
                sprintf('Error fetching offer: %s', $e->getMessage()), 
                [
                    'offer_id' => $offer->id, 
                    'trace' => $e->getTraceAsString()
                ]
            );
            return response()->json([
                'message' => 'Internal error when getting offer'
            ], 500);
        }
    }

    /**
     * Update the specified offer.
     */
    public function update(
        UpdateOfferRequest  $request, 
        Offer               $offer
    ) {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();            
            
            $offer->update($validatedData);

            DB::commit();
            $offer->load('product');

            return response()->json($offer, 200); // 200 OK
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(
                sprintf('Error updating offer: %s', $e->getMessage()), 
                [
                    'offer_id'  => $offer->id, 
                    'trace'     => $e->getTraceAsString()
                ]
            );
            return response()->json([
                'message'   => 'Internal error when updating offer',
                'error'     => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified offer
     */
    public function destroy(Offer $offer) 
    {
        try {
            DB::beginTransaction();

            $offer->delete();

            DB::commit();
            
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(
                sprintf('Error deleting offer: %s', $e->getMessage()), 
                [
                    'offer_id'  => $offer->id, 
                    'trace'     => $e->getTraceAsString()]
                );

            return response()->json([
                'message'   => 'Internal error when deleting an offer',
                'error'     => $e->getMessage()
            ], 500);
        }
    }
}

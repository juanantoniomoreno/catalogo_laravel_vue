<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOptionRequest;
use App\Http\Requests\UpdateOptionRequest;
use App\Models\Option;
use App\Models\OptionTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $options = Option::with(['product.translations', 'translations'])->get();

            $formattedOptions = $options->map(function ($option) {
                $data = $option->toArray();

                // Get translated name and description for the option itself
                $optionTranslation = $option->translations->firstWhere('locale', app()->getLocale());
                if (!$optionTranslation) {
                    $optionTranslation = $option->translations->first();
                }
                $data['name']           = $optionTranslation ? $optionTranslation->name : null;
                $data['description']    = $optionTranslation ? $optionTranslation->description : null;

                // Get translated product name
                $data['product_name'] = null;
                if ($option->product) {
                    $productTranslation = $option->product->translations->firstWhere('locale', app()->getLocale());
                    if (!$productTranslation) {
                        $productTranslation = $option->product->translations->first();
                    }
                    if ($productTranslation) {
                        $data['product_name'] = $productTranslation->name;
                    }
                }

                unset($data['product']);
                unset($data['translations']);

                return $data;
            });

            return response()->json($formattedOptions);
        } catch (\Exception $e) {
            Log::error(
                sprintf('Error fetching options: %s', $e->getMessage()),
                ['trace' => $e->getTraceAsString()]
            );
            return response()->json([
                'message' => 'Error interno del servidor al obtener las opciones.'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOptionRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Create the option
            $option = Option::create([
                'product_id' => $validated['product_id'],
                'image_url'  => $validated['image_url'] ?? null,
                'status'     => $validated['status'],
                'price'      => $validated['price'],
            ]);

            // Create translations for the option
            foreach ($validated['translations'] as $translationData) {
                $option->translations()->create([
                    'locale'      => $translationData['locale'],
                    'name'        => $translationData['name'],
                    'description' => $translationData['description'] ?? null,
                ]);
            }

            DB::commit();

            // Load relations and format for response
            $option->load(['product.translations', 'translations']);
            $formattedOption = $option->toArray();

            $optionTranslation = $option->translations->firstWhere('locale', app()->getLocale());
            if (!$optionTranslation) {
                $optionTranslation = $option->translations->first();
            }
            $formattedOption['name'] = $optionTranslation ? $optionTranslation->name : null;
            $formattedOption['description'] = $optionTranslation ? $optionTranslation->description : null;

            $formattedOption['product_name'] = null;
            if ($option->product) {
                $productTranslation = $option->product->translations->firstWhere('locale', app()->getLocale());
                if (!$productTranslation) {
                    $productTranslation = $option->product->translations->first();
                }
                if ($productTranslation) {
                    $formattedOption['product_name'] = $productTranslation->name;
                }
            }

            unset($formattedOption['product']);
            unset($formattedOption['translations']);

            return response()->json($formattedOption, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(
                sprintf('Error storing option: %s', $e->getMessage()),
                ['trace' => $e->getTraceAsString()]
            );
            return response()->json([
                'message' => 'Error interno del servidor al crear la opción.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Option $option)
    {
        try {            
            $option->load(['product.translations', 'translations']);

            $formattedOption = $option->toArray();

            // Get translated name and description for the option itself
            $optionTranslation = $option->translations->firstWhere('locale', app()->getLocale());
            if (!$optionTranslation) {
                $optionTranslation = $option->translations->first(); // Fallback
            }
            $formattedOption['name'] = $optionTranslation ? $optionTranslation->name : null;
            $formattedOption['description'] = $optionTranslation ? $optionTranslation->description : null;

            // Get translated product name and description
            $formattedOption['product_name'] = null;            
            if ($option->product) {
                $productTranslation = $option->product->translations->firstWhere('locale', app()->getLocale());
                if (!$productTranslation) {
                    $productTranslation = $option->product->translations->first(); // Fallback
                }
                if ($productTranslation) {
                    $formattedOption['product_name'] = $productTranslation->name;                    
                }
            }

            unset($formattedOption['product']);
            // unset($formattedOption['translations']);

            return response()->json($formattedOption);
        } catch (\Exception $e) {
            Log::error(
                sprintf('Error fetching option: %s', $e->getMessage()), 
                [
                    'option_id' => $option->id, 
                    'trace' => $e->getTraceAsString()
                ]
            );
            return response()->json([
                'message' => 'Error interno del servidor al obtener la opción.'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateOptionRequest $request, 
        Option              $option
    ){
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Update option fields
            $option->update([
                'product_id' => $validated['product_id'] ?? $option->product_id,
                'image_url'  => $validated['image_url'] ?? $option->image_url,
                'status'     => $validated['status'] ?? $option->status,
                'price'      => $validated['price'] ?? $option->price,
            ]);

            // Update or create translations for the option
            if (isset($validated['translations'])) {
                foreach ($validated['translations'] as $translationData) {
                    OptionTranslation::updateOrCreate(
                        ['option_id' => $option->id, 'locale' => $translationData['locale']],
                        ['name' => $translationData['name'], 'description' => $translationData['description'] ?? null]
                    );
                }
            }

            DB::commit();

            // Load relations and format for response
            $option->load(['product.translations', 'translations']);
            $formattedOption = $option->toArray();
            
            $optionTranslation = $option->translations->firstWhere('locale', app()->getLocale());
            if (!$optionTranslation) {
                $optionTranslation = $option->translations->first();
            }
            $formattedOption['name'] = $optionTranslation ? $optionTranslation->name : null;
            $formattedOption['description'] = $optionTranslation ? $optionTranslation->description : null;

            $formattedOption['product_name'] = null;            
            if ($option->product) {
                $productTranslation = $option->product->translations->firstWhere('locale', app()->getLocale());
                if (!$productTranslation) {
                    $productTranslation = $option->product->translations->first();
                }
                if ($productTranslation) {
                    $formattedOption['product_name'] = $productTranslation->name;                    
                }
            }

            unset($formattedOption['product']);
            unset($formattedOption['translations']);

            return response()->json($formattedOption, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(
                sprintf('Error updating option: %s',$e->getMessage()), 
                [
                    'option_id' => $option->id, 
                    'trace' => $e->getTraceAsString()
                ]
            );
            return response()->json([
                'message' => 'Error interno del servidor al actualizar la opción.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Option $option)
    {
        DB::beginTransaction();
        try {            
            $option->delete();
            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(
                sprintf('Error deleting option: %s', $e->getMessage()) , 
                [
                    'option_id' => $option->id, 
                    'trace' => $e->getTraceAsString()
                ]
            );
            return response()->json([
                'message' => 'Error interno del servidor al eliminar la opción.'
            ], 500);
        }
    }
}

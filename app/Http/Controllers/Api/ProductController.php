<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


use function Pest\Laravel\json;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {        
        // Carga los productos con sus traducciones, imágenes
        $products = Product::with([
            'translations' => function ($query) {
                $query->where('locale', app()->getLocale());
            },
            'images'
        ])->get();


        // Mapea los productos para incluir el nombre y descripción traducidos directamente        
        $products = $products->map(function ($product) {
            $translation            = $product->translations->first();
            $product->name          = $translation ? $translation->name : '-';
            $product->description   = $translation ? $translation->description : '-';
            unset($product->translations);

            $product->images = $product->images ?: [];
            return $product;
        });

        return response()->json($products);
    }

    /**
     * Display a listing of the filtered products.
     */
    public function getFilteredProducts(Request $request)
    {
        // Obtiene el valor del parámetro de consulta 'filter_type'
        // Si no se proporciona, por defecto es null
        $filterType = $request->query('type', null);
        
        $productsQuery = Product::with([
            'translations' => function ($query) {
                // Asegúrate de que esta lógica sea correcta para obtener la traducción deseada
                $query->where('locale', app()->getLocale());
            },
            'images'
        ])
        ->where('status', Product::STATUS_ACTIVE);

        // Aplica el filtro de tipo condicionalmente
        if ($filterType) {
            // Filtra por productos que son de tipo 'simple'
            $productsQuery->where('type', $filterType);
        }        

        // Ejecuta la consulta y obtiene los productos
        $products = $productsQuery->get();        

        // Mapea los productos para incluir el nombre, descripción y slug traducidos directamente
        $products = $products->map(function ($product) {
            $translation = $product->translations->first();
            $product->name = $translation ? $translation->name : null;
            $product->description = $translation ? $translation->description : null;            
            unset($product->translations);
            return $product;
        });

        return response()->json($products);
    }



    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load([
            'translations' => function ($query) {
                $query->where('locale', app()->getLocale());
            },
            'images',
            'options.translations' => function ($query) {
                $query->where('locale', app()->getLocale());
            },
            'options.price',
            'packProducts.translations' => function ($query) {
                $query->where('locale', app()->getLocale());
            },
            'packProducts.price',
            'offers'
        ]);

        $product->name          = $product->name;
        $product->description   = $product->description;
        $product->price         = $product->price;
        unset($product->translations);

        // Mapear opciones y packProducts para incluir traducciones directamente
        $product->options = $product->options->map(function ($option) {
            $option->name           = $option->name;
            $option->description    = $option->description;
            unset($option->translations);
            return $option;
        });

        if ($product->type === \App\Models\Product::TYPE_PACK) {
            $product->packProducts = $product->packProducts->map(function ($packProduct) {
                $packProduct->name          = $packProduct->name;
                $packProduct->description   = $packProduct->description;
                unset($packProduct->translations);
                return $packProduct;
            });
        }

        return response()->json($product);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validatedProductData = $request->validated();

        try {
            DB::beginTransaction();

            // Crear el Producto principal
            $product = Product::create([
                'main_image_url' => $validatedProductData['main_image_url'] ?? null,
                'status'         => $validatedProductData['status'],
                'type'           => $validatedProductData['type'],
                'price'          => $validatedProductData['price'],
            ]);

            // Crear la Traducción del Producto (para ES en este caso)
            $product->translations()->create([
                'locale'      => 'es',
                'name'        => $validatedProductData['translations']['es']['name'],
                'description' => $validatedProductData['translations']['es']['description'] ?? null,
            ]);            

            // En caso de que el producto sea un pack
            if ($product->type === Product::TYPE_PACK) {
                $packProducts = [];
                foreach ($validatedProductData['pack_products'] as $item) {
                    $packProducts[$item['product_id']] = ['quantity' => $item['quantity']];
                } 

                // Adjuntar los productos al pack
                $product->packProducts()->sync($packProducts);
            }

            DB::commit();

            $product->load([
                'translations' => function ($query) {
                    $query->where('locale', app()->getLocale());
                },                
                'packProducts' => function ($query) {
                    $query->with(['translations' => function($subQuery) {
                        $subQuery->where('locale', app()->getLocale());
                    }]);
                }
            ]);

            // Formatear producto para el frontend
            $translation = $product->translations->first();
            $product->name = $translation ? $translation->name : null;
            $product->description = $translation ? $translation->description : null;            
            unset($product->translations);

            // Si es un pack, añadir los detalles de los ítems directamente al objeto principal
            if ($product->type === Product::TYPE_PACK) {
                $product->items = $product->packProducts->map(function($item) {
                    $itemTranslation = $item->translations->first();
                    return [
                        'id' => $item->id,
                        'name' => $itemTranslation ? $itemTranslation->name : null,
                        'quantity' => $item->pivot->quantity,
                        'main_image_url' => $item->main_image_url,
                    ];
                });
                unset($product->packProducts);
            }

            return response()->json($product, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error
            Log::error('Error creating product: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message'   => 'Error al crear el producto.',
                'error'     => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product) // Usamos el Form Request para validación y autorización
    {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            // Actualizar el Producto principal
            $product->update([
                'main_image_url' => $validatedData['main_image_url'] ?? null,
                'status'         => $validatedData['status'],
                'type'           => $validatedData['type'],
                'price'          => $validatedData['price']
            ]);

            // Actualizar o crear la Traducción del Producto (para ES)
            $product->translations()->updateOrCreate(
                ['locale' => 'es'],
                [
                    'name'        => $validatedData['translations']['es']['name'],
                    'description' => $validatedData['translations']['es']['description'] ?? null,
                ]
            );

            DB::commit();

            // Carga las relaciones para la respuesta
            $product->load([
                'translations' => function ($query) {
                    $query->where('locale', app()->getLocale());
                }
            ]);

            // Formatear el producto para el frontend
            $translation    = $product->translations->first();

            $product->name          = $translation ? $translation->name : null;
            $product->description   = $translation ? $translation->description : null;

            unset($product->translations);
            return response()->json($product);
        } catch (AuthorizationException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating product: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error interno del servidor al actualizar el producto.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();

            $product->translations()->delete();
            $product->delete();

            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting product: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Error interno del servidor al eliminar el producto.'], 500);
        }
    }
}

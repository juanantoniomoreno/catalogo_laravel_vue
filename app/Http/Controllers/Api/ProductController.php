<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load([
            'translations' => function ($query) {
                $query->where('locale', app()->getLocale());
            },
            'images',
            'price',
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

        $product->name = $product->name;
        $product->description = $product->description;
        unset($product->translations);

        // Mapear opciones y packProducts para incluir traducciones directamente
        $product->options = $product->options->map(function ($option) {
            $option->name = $option->name;
            $option->description = $option->description;
            unset($option->translations);
            return $option;
        });

        if ($product->type === \App\Models\Product::TYPE_PACK) {
            $product->packProducts = $product->packProducts->map(function ($packProduct) {
                $packProduct->name = $packProduct->name;
                $packProduct->description = $packProduct->description;
                unset($packProduct->translations);
                return $packProduct;
            });
        }

        return response()->json($product);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        // 1. Validación de los datos del producto
        $validatedProductData = $request->validate([
            'main_image_url'    => 'nullable|url|max:255',
            'status'            => 'required|string|in:' . Product::STATUS_ACTIVE . ',' . Product::STATUS_INACTIVE,
            'type'              => 'required|string|in:' . Product::TYPE_SIMPLE . ',' . Product::TYPE_OPTION_GROUP . ',' . Product::TYPE_PACK,
            // Validación para las traducciones anidadas
            'translations.es.name'          => 'required|string|max:255',
            'translations.es.description'   => 'nullable|string',            
            // Validación para el precio
            'price' => 'required|numeric|min:0'            
        ]);

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

            DB::commit();            

            $product->load('translations');

            // Formatear el producto para el frontend
            $product->name          = $product->name; 
            $product->description   = $product->description; 
            unset($product->translations); 

            return response()->json($product, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error for debugging
            \Log::error('Error creating product: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message'   => 'Error al crear el producto.', 
                'error'     => $e->getMessage()
            ], 500);
        }
    }
}
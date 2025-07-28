<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    // Constantes para tipos de productos (en lugar de Enums)
    public const TYPE_SIMPLE = 'simple';
    public const TYPE_OPTION_GROUP = 'option_group';
    public const TYPE_PACK = 'pack';

    // Constantes para estados (en lugar de Enums)
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'main_image_url',
        'status',
        'type',
        'price'
    ];

    /**
     * Get the translations for the product.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    /**
     * Get the images for the product (gallery).
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }    

    /**
     * Get the options for the product (if it's an 'option_group' type).
     */
    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    /**
     * Get the products that belong to this pack (if it's a 'pack' type).
     */
    public function packProducts(): BelongsToMany
    {
        // 'product_id' es la FK en pack_products que apunta a los productos individuales
        // 'pack_id' es la FK en pack_products que apunta a este producto (el pack)
        return $this->belongsToMany(Product::class, 'pack_products', 'pack_id', 'product_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * Get the offers for the product.
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Helper to get the translated name for the current locale.
     */
    public function getNameAttribute(): ?string
    {
        return $this->translations->where('locale', app()->getLocale())->first()?->name;
    }

    /**
     * Helper to get the translated description for the current locale.
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->translations->where('locale', app()->getLocale())->first()?->description;
    }
}
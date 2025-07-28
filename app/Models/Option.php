<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Option extends Model
{
    use HasFactory;

    // Constantes para estados
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'image_url',
        'status',
        'price'
    ];

    /**
     * Get the product that this option belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the translations for the option.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(OptionTranslation::class);
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
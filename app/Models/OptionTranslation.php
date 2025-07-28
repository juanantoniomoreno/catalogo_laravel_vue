<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionTranslation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'option_id',
        'locale',
        'name',
        'description',
    ];

    /**
     * Get the option that owns the translation.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}
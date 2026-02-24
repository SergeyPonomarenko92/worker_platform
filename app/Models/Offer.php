<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_profile_id',
        'category_id',
        'type',
        'title',
        'description',
        'price_from',
        'price_to',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'business_profile_id' => 'integer',
        'category_id' => 'integer',
        'price_from' => 'integer',
        'price_to' => 'integer',
        'is_active' => 'boolean',
    ];

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }
}

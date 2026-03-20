<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Deal extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS_UK = [
        self::STATUS_DRAFT => 'Чернетка',
        self::STATUS_IN_PROGRESS => 'В процесі',
        self::STATUS_COMPLETED => 'Завершено',
        self::STATUS_CANCELLED => 'Скасовано',
    ];

    protected $fillable = [
        'client_user_id',
        'business_profile_id',
        'offer_id',
        'status',
        'agreed_price',
        'currency',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS_UK[$this->status] ?? $this->status;
    }
}

<?php

namespace MRustamzade\MarketingTouchpoints\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingVisitor extends Model
{
    protected $guarded = [];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return (string) config('marketing-touchpoints.tables.visitors', 'marketing_visitors');
    }

    public function touchpoints(): HasMany
    {
        return $this->hasMany(MarketingTouchpoint::class, 'visitor_id');
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(MarketingConversion::class, 'visitor_id');
    }
}

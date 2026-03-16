<?php

namespace MRustamzade\MarketingTouchpoints\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingConversion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
        'linked_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return (string) config('marketing-touchpoints.tables.conversions', 'marketing_conversions');
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(MarketingVisitor::class, 'visitor_id');
    }
}

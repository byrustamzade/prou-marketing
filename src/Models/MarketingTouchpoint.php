<?php

namespace MRustamzade\MarketingTouchpoints\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingTouchpoint extends Model
{
    protected $guarded = [];

    protected $casts = [
        'query' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return (string) config('marketing-touchpoints.tables.touchpoints', 'marketing_touchpoints');
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(MarketingVisitor::class, 'visitor_id');
    }
}

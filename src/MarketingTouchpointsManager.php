<?php

namespace MRustamzade\MarketingTouchpoints;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MRustamzade\MarketingTouchpoints\Models\MarketingConversion;
use MRustamzade\MarketingTouchpoints\Models\MarketingTouchpoint;
use MRustamzade\MarketingTouchpoints\Models\MarketingVisitor;

class MarketingTouchpointsManager
{
    public function __construct(private readonly Repository $config)
    {
    }

    public function tokenFromRequest(Request $request): string
    {
        $cookieName = (string) $this->config->get('marketing-touchpoints.cookie.name', 'marketing_touch_token');
        $token = trim((string) $request->cookies->get($cookieName, ''));

        return $token !== '' ? $token : (string) Str::uuid();
    }

    public function hasAnyTrackedUtm(Request $request): bool
    {
        foreach ((array) $this->config->get('marketing-touchpoints.track.utm_keys', []) as $utmKey) {
            $value = $request->query($utmKey);

            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }

    public function ensureVisitor(Request $request, ?string $token = null): MarketingVisitor
    {
        $token ??= $this->tokenFromRequest($request);
        $now = now();

        $visitor = MarketingVisitor::query()->firstOrCreate(
            ['token' => $token],
            ['first_seen_at' => $now, 'last_seen_at' => $now]
        );

        $visitor->forceFill([
            'first_seen_at' => $visitor->first_seen_at ?? $now,
            'last_seen_at' => $now,
        ]);

        if ($visitor->isDirty()) {
            $visitor->save();
        }

        return $visitor;
    }

    public function captureTouchpoint(Request $request, ?string $token = null): MarketingTouchpoint
    {
        $token ??= $this->tokenFromRequest($request);
        $visitor = $this->ensureVisitor($request, $token);
        $utm = $this->extractUtmPayload($request);

        return MarketingTouchpoint::query()->create([
            'visitor_id' => $visitor->id,
            'token' => $token,
            'landing_url' => $request->fullUrl(),
            'path' => $request->getPathInfo(),
            'method' => strtoupper($request->method()),
            'referer' => $request->headers->get('referer'),
            'utm_source' => Arr::get($utm, 'utm_source'),
            'utm_medium' => Arr::get($utm, 'utm_medium'),
            'utm_campaign' => Arr::get($utm, 'utm_campaign'),
            'utm_term' => Arr::get($utm, 'utm_term'),
            'utm_content' => Arr::get($utm, 'utm_content'),
            'utm_id' => Arr::get($utm, 'utm_id'),
            'gclid' => Arr::get($utm, 'gclid'),
            'fbclid' => Arr::get($utm, 'fbclid'),
            'msclkid' => Arr::get($utm, 'msclkid'),
            'query' => $request->query(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'occurred_at' => now(),
        ]);
    }

    public function linkOrder(mixed $order, ?string $token = null, array $meta = []): ?MarketingConversion
    {
        if ($token === null && app()->bound('request')) {
            $token = $this->tokenFromRequest(request());
        }

        $token = trim((string) $token);

        if ($token === '') {
            return null;
        }

        $visitor = MarketingVisitor::query()->where('token', $token)->first();

        if ($visitor === null) {
            if (!app()->bound('request')) {
                return null;
            }

            $visitor = $this->ensureVisitor(request(), $token);
        }

        $reference = $this->resolveOrderReference($order);

        if ($reference['identifier'] === null || $reference['identifier'] === '') {
            return null;
        }

        return MarketingConversion::query()->updateOrCreate(
            [
                'visitor_id' => $visitor->id,
                'order_table' => $reference['table'],
                'order_primary_key' => $reference['primary_key'],
                'order_identifier' => $reference['identifier'],
            ],
            [
                'token' => $visitor->token,
                'meta' => $meta === [] ? null : $meta,
                'linked_at' => now(),
            ]
        );
    }

    public function resolveOrderReference(mixed $order): array
    {
        $defaults = $this->configuredOrderDefaults();
        $table = $defaults['table'];
        $primaryKey = $defaults['primary_key'];
        $identifier = null;

        if ($order instanceof Model) {
            $table = $order->getTable();
            $primaryKey = $order->getKeyName();
            $identifier = $order->getKey();
        } elseif (is_array($order)) {
            $table = (string) Arr::get($order, 'table', $table);
            $primaryKey = (string) Arr::get($order, 'primary_key', $primaryKey);
            $identifier = Arr::get($order, 'id');
        } else {
            $identifier = $order;
        }

        return [
            'table' => $table,
            'primary_key' => $primaryKey,
            'identifier' => $identifier === null ? null : (string) $identifier,
        ];
    }

    private function configuredOrderDefaults(): array
    {
        $table = (string) $this->config->get('marketing-touchpoints.orders.table', 'orders');
        $primaryKey = (string) $this->config->get('marketing-touchpoints.orders.primary_key', 'id');
        $modelClass = $this->config->get('marketing-touchpoints.orders.model');

        if (is_string($modelClass) && class_exists($modelClass)) {
            $model = new $modelClass();

            if ($model instanceof Model) {
                $table = $model->getTable();
                $primaryKey = $model->getKeyName();
            }
        }

        return [
            'table' => $table,
            'primary_key' => $primaryKey,
        ];
    }

    private function extractUtmPayload(Request $request): array
    {
        $payload = [];

        foreach ((array) $this->config->get('marketing-touchpoints.track.utm_keys', []) as $utmKey) {
            $payload[$utmKey] = $request->query($utmKey);
        }

        return $payload;
    }
}

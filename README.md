# Laravel Marketing Touchpoints

Track user marketing touchpoints (including UTM params) with a unique visitor token, then link the final order to that token after checkout.

## Features

- Generates a unique token cookie when a visitor arrives.
- Stores each touchpoint in DB with URL, path, referrer, UTM data, and request metadata.
- Links orders to the same visitor token after checkout.
- Supports custom order table and primary key resolution.
- Includes an admin route (`/marketing`) for viewing touchpoints by token or order ID.

## Installation

```bash
composer require rustamzade/laravel-marketing-touchpoints
```

Publish config and migrations:

```bash
php artisan vendor:publish --tag=marketing-touchpoints-config
php artisan vendor:publish --tag=marketing-touchpoints-migrations
```

Run migrations:

```bash
php artisan migrate
```

## Enable Tracking Middleware

Option 1: Enable auto-injection in config:

```php
'middleware' => [
    'auto_track_web' => true,
],
```

Option 2: Add alias manually to your routes/group:

```php
Route::middleware(['web', 'track-touchpoints'])->group(function (): void {
    // your storefront routes
});
```

## Link Order After Checkout

In checkout success flow:

```php
use Rustam\MarketingTouchpoints\Facades\MarketingTouchpoints;

MarketingTouchpoints::linkOrder($order); // Eloquent model
```

Or scalar order ID:

```php
MarketingTouchpoints::linkOrder($orderId);
```

Or pass full order reference:

```php
MarketingTouchpoints::linkOrder([
    'table' => 'shop_orders',
    'primary_key' => 'uuid',
    'id' => $orderUuid,
]);
```

### Optional model trait

Add this to your order model to auto-link when created:

```php
use Rustam\MarketingTouchpoints\Concerns\LinksMarketingTouchpoints;

class Order extends Model
{
    use LinksMarketingTouchpoints;
}
```

## Order Table + Primary Key Resolution

The package resolves order reference in this order:

1. If you pass an Eloquent model to `linkOrder()`, it uses that model's table and key name.
2. Else if `orders.model` is configured, it loads table/key from that model.
3. Else it falls back to `orders.table` and `orders.primary_key`.

Set defaults in `config/marketing-touchpoints.php`:

```php
'orders' => [
    'model' => App\Models\Order::class,
    'table' => 'orders',
    'primary_key' => 'id',
],
```

## Admin Marketing Route

Default route:

- URL: `/marketing`
- middleware: `web`, `auth`
- filters: `?token={token}` or `?order_id={orderId}`

Customize from config:

```php
'route' => [
    'prefix' => 'marketing',
    'middleware' => ['web', 'auth'],
],
```

## Config Summary

- `tables.visitors`, `tables.touchpoints`, `tables.conversions`
- `orders.model`, `orders.table`, `orders.primary_key`
- `middleware.except` to skip tracking admin/internal routes
- `track.only_with_utm` if you only want UTM-tagged touchpoints

## Publish to GitHub

Initialize and push:

```bash
git init
git add .
git commit -m "Initial Laravel marketing touchpoints package"
git branch -M main
git remote add origin git@github.com:<your-username>/laravel-marketing-touchpoints.git
git push -u origin main
```

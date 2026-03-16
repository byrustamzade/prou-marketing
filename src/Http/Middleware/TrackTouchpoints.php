<?php

namespace MRustamzade\MarketingTouchpoints\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use MRustamzade\MarketingTouchpoints\MarketingTouchpointsManager;
use Symfony\Component\HttpFoundation\Response;

class TrackTouchpoints
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var MarketingTouchpointsManager $manager */
        $manager = app(MarketingTouchpointsManager::class);
        $token = $manager->tokenFromRequest($request);

        $request->attributes->set('marketing_touch_token', $token);

        if ($this->shouldTrack($request, $manager)) {
            $manager->captureTouchpoint($request, $token);
        }

        /** @var Response $response */
        $response = $next($request);

        $this->queueTokenCookie($request, $token);

        return $response;
    }

    private function shouldTrack(Request $request, MarketingTouchpointsManager $manager): bool
    {
        if (!(bool) config('marketing-touchpoints.enabled', true)) {
            return false;
        }

        $methods = array_map('strtoupper', (array) config('marketing-touchpoints.track.methods', ['GET']));
        if (!in_array(strtoupper($request->method()), $methods, true)) {
            return false;
        }

        foreach ((array) config('marketing-touchpoints.middleware.except', []) as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        if ((bool) config('marketing-touchpoints.track.only_with_utm', false) && !$manager->hasAnyTrackedUtm($request)) {
            return false;
        }

        return true;
    }

    private function queueTokenCookie(Request $request, string $token): void
    {
        $cookieName = (string) config('marketing-touchpoints.cookie.name', 'marketing_touch_token');
        $minutes = (int) config('marketing-touchpoints.cookie.minutes', 60 * 24 * 365 * 2);
        $path = (string) config('marketing-touchpoints.cookie.path', '/');
        $domain = config('marketing-touchpoints.cookie.domain');
        $secure = config('marketing-touchpoints.cookie.secure');
        $httpOnly = (bool) config('marketing-touchpoints.cookie.http_only', true);
        $sameSite = (string) config('marketing-touchpoints.cookie.same_site', 'lax');

        if ($secure === null) {
            $secure = $request->isSecure();
        }

        Cookie::queue(Cookie::make(
            $cookieName,
            $token,
            $minutes,
            $path,
            $domain,
            (bool) $secure,
            $httpOnly,
            false,
            $sameSite
        ));
    }
}

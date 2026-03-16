<?php

namespace MRustamzade\MarketingTouchpoints\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MRustamzade\MarketingTouchpoints\Models\MarketingVisitor;

class MarketingController extends Controller
{
    public function index(Request $request)
    {
        $token = trim((string) $request->query('token', ''));
        $orderId = trim((string) $request->query('order_id', ''));
        $perPage = max(1, (int) config('marketing-touchpoints.dashboard.per_page', 30));

        $visitorsQuery = MarketingVisitor::query()
            ->withCount(['touchpoints', 'conversions'])
            ->latest('last_seen_at');

        if ($token !== '') {
            $visitorsQuery->where('token', $token);
        }

        if ($orderId !== '') {
            $visitorsQuery->whereHas('conversions', function ($query) use ($orderId): void {
                $query->where('order_identifier', $orderId);
            });
        }

        $visitors = $visitorsQuery->paginate($perPage)->withQueryString();

        $selectedVisitor = null;
        if ($token !== '' || $orderId !== '') {
            $selectedVisitor = $visitors->first();

            if ($selectedVisitor !== null) {
                $selectedVisitor->load([
                    'touchpoints' => fn($query) => $query->latest('occurred_at'),
                    'conversions' => fn($query) => $query->latest('linked_at'),
                ]);
            }
        }

        return view('marketing-touchpoints::index', [
            'visitors' => $visitors,
            'selectedVisitor' => $selectedVisitor,
            'token' => $token,
            'orderId' => $orderId,
        ]);
    }
}

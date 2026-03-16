<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Marketing Touchpoints</title>
    <style>
        :root {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color-scheme: light;
        }
        body {
            margin: 0;
            background: #f7f8fa;
            color: #1f2937;
        }
        .wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }
        .panel {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 16px;
        }
        h1, h2, h3 {
            margin-top: 0;
        }
        .filters {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            align-items: end;
        }
        .filters label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
        }
        input[type="text"] {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
        }
        button {
            border: 0;
            border-radius: 8px;
            padding: 10px 14px;
            background: #0f766e;
            color: #fff;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12px;
        }
        .muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="wrap">
    <h1>Marketing Touchpoints</h1>

    <div class="panel">
        <form method="GET" class="filters">
            <div>
                <label for="token">Visitor token</label>
                <input id="token" type="text" name="token" value="{{ $token }}">
            </div>
            <div>
                <label for="order_id">Order ID</label>
                <input id="order_id" type="text" name="order_id" value="{{ $orderId }}">
            </div>
            <div>
                <button type="submit">Filter</button>
            </div>
        </form>
    </div>

    <div class="panel">
        <h2>Visitors</h2>
        <p class="muted">Total: {{ $visitors->total() }}</p>
        <table>
            <thead>
            <tr>
                <th>Token</th>
                <th>First Seen</th>
                <th>Last Seen</th>
                <th>Touchpoints</th>
                <th>Orders</th>
            </tr>
            </thead>
            <tbody>
            @forelse($visitors as $visitor)
                <tr>
                    <td class="mono">
                        <a href="{{ request()->url() }}?token={{ urlencode($visitor->token) }}">{{ $visitor->token }}</a>
                    </td>
                    <td>{{ optional($visitor->first_seen_at)->toDateTimeString() }}</td>
                    <td>{{ optional($visitor->last_seen_at)->toDateTimeString() }}</td>
                    <td>{{ $visitor->touchpoints_count }}</td>
                    <td>{{ $visitor->conversions_count }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No visitors found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div style="margin-top: 12px;">
            {{ $visitors->links() }}
        </div>
    </div>

    @if($selectedVisitor)
        <div class="panel">
            <h2>Selected Visitor</h2>
            <p class="mono">{{ $selectedVisitor->token }}</p>

            <h3>Touchpoints</h3>
            <table>
                <thead>
                <tr>
                    <th>Time</th>
                    <th>Path</th>
                    <th>UTM Source</th>
                    <th>UTM Medium</th>
                    <th>UTM Campaign</th>
                    <th>Referrer</th>
                </tr>
                </thead>
                <tbody>
                @forelse($selectedVisitor->touchpoints as $touchpoint)
                    <tr>
                        <td>{{ optional($touchpoint->occurred_at)->toDateTimeString() }}</td>
                        <td class="mono">{{ $touchpoint->path }}</td>
                        <td>{{ $touchpoint->utm_source }}</td>
                        <td>{{ $touchpoint->utm_medium }}</td>
                        <td>{{ $touchpoint->utm_campaign }}</td>
                        <td class="mono">{{ $touchpoint->referer }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No touchpoints tracked for this token.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <h3 style="margin-top: 16px;">Orders</h3>
            <table>
                <thead>
                <tr>
                    <th>Linked At</th>
                    <th>Order Table</th>
                    <th>Primary Key</th>
                    <th>Order ID</th>
                </tr>
                </thead>
                <tbody>
                @forelse($selectedVisitor->conversions as $conversion)
                    <tr>
                        <td>{{ optional($conversion->linked_at)->toDateTimeString() }}</td>
                        <td class="mono">{{ $conversion->order_table }}</td>
                        <td class="mono">{{ $conversion->order_primary_key }}</td>
                        <td class="mono">{{ $conversion->order_identifier }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No orders linked yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bunkers Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            padding: 15px 20px;
            color: #1a1a2e;
            background: #ffffff;
            line-height: 1.4;
        }
        .container { max-width: 1200px; margin: 0 auto; }

        /* Print Button */
        .no-print { text-align: center; margin-bottom: 10px; }
        .no-print button {
            background: #1a1a2e; color: white; border: none;
            padding: 7px 24px; border-radius: 4px; font-size: 11px;
            cursor: pointer; margin: 0 4px; font-family: inherit;
        }
        .no-print button:hover { background: #2d2d44; }
        .no-print button.close { background: #6b7280; }
        @media print { .no-print { display: none; } body { padding: 10px 15px; } }

        /* Header */
        .header {
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 2px solid #1a1a2e; padding-bottom: 8px; margin-bottom: 10px;
        }
        .header-left { display: flex; align-items: center; gap: 14px; }
        .header-left img { max-height: 55px; width: auto; max-width: 200px; }
        .header-left h1 { font-size: 18px; font-weight: 700; color: #1a1a2e; letter-spacing: 0.5px; }
        .header-left .sub { font-size: 8px; color: #6b7280; display: block; margin-top: 1px; }
        .header-right { text-align: right; }
        .header-right .title { font-size: 14px; font-weight: 700; color: #1a1a2e; }
        .header-right .sub { font-size: 8px; color: #6b7280; }

        /* Filters */
        .filters {
            background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 4px;
            padding: 5px 12px; margin-bottom: 10px; display: flex; flex-wrap: wrap; gap: 20px; font-size: 11px;
        }
        .filters .item { display: flex; align-items: center; gap: 4px; }
        .filters .label { font-weight: 600; color: #4b5563; }
        .filters .value { color: #1a1a2e; font-weight: 500; }

        /* Section Title */
        .section-title {
            font-size: 11px; font-weight: 700; color: #1a1a2e;
            margin-top: 8px; margin-bottom: 3px; text-transform: uppercase;
            letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px;
        }

        /* Table */
        table {
            width: 100%; border-collapse: collapse; font-size: 8.5px; border: 1px solid #d1d5db;
        }
        th {
            background: #f3f4f6; font-weight: 600; color: #1a1a2e;
            padding: 4px 6px; border: 1px solid #d1d5db; text-align: center;
            text-transform: uppercase; font-size: 6.5px; letter-spacing: 0.3px;
            white-space: nowrap;
        }
        td {
            padding: 3px 6px; border: 1px solid #d1d5db; color: #1a1a2e; text-align: center;
            white-space: nowrap; font-size: 12px;
        }
        .text-left { text-align: left; }
        .total-row td { font-weight: 700; background: #f9fafb; border-top: 2px solid #1a1a2e; }

        /* Summary Grid */
        .summary-grid {
            display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; margin-bottom: 8px;
        }
        .summary-item {
            background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 4px;
            padding: 5px 8px; text-align: center;
        }
        .summary-item .label { font-size: 7px; color: #6b7280; text-transform: uppercase; font-weight: 600; letter-spacing: 0.3px; }
        .summary-item .value { font-size: 14px; font-weight: 700; color: #1a1a2e; margin-top: 1px; }

        /* Footer */
        .footer {
            margin-top: 10px; padding-top: 5px; border-top: 1px solid #e5e7eb;
            font-size: 7px; color: #6b7280; text-align: center;
        }

        /* Status Badge */
        .status { padding: 1px 8px; border-radius: 10px; font-size: 7px; font-weight: 600; }
        .status.active { background: #d1fae5; color: #065f46; }
        .status.empty { background: #fee2e2; color: #991b1b; }
        .status.warning { background: #fef3c7; color: #92400e; }

        /* Profit Color */
        .profit-positive { color: #16a34a; }
        .profit-negative { color: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print">
            <button onclick="window.print()">🖨️ Print / Save as PDF</button>
            <button class="close" onclick="window.close()">✕ Close</button>
        </div>

        <div class="header">
            <div class="header-left">
                @if($logo) <img src="{{ $logo }}" alt="Logo" /> @endif
                <div>
                    <h1>{{ $businessName }}</h1>
                    <span class="sub">Enterprise Resource Planning</span>
                </div>
            </div>
            <div class="header-right">
                <div class="title">Bunkers Performance Report</div>
                <div class="sub">Report Date: {{ now()->format('d M Y h:i A') }}</div>
            </div>
        </div>

        <div class="filters">
            <div class="item"><span class="label">Filters:</span></div>
            <div class="item"><span class="label">Season:</span> <span class="value">{{ $filters['season'] }}</span></div>
            <div class="item"><span class="label">Location:</span> <span class="value">{{ $filters['location'] }}</span></div>
        </div>

        <div class="section-title">Summary</div>
        <div class="summary-grid">
            <div class="summary-item"><div class="label">Bunkers</div><div class="value">{{ $total_bunkers }}</div></div>
            <div class="summary-item"><div class="label">Purchased</div><div class="value">{{ number_format($total_purchased, 0) }} kg</div></div>
            <div class="summary-item"><div class="label">Sold</div><div class="value">{{ number_format($total_sold, 0) }} kg</div></div>
            <div class="summary-item"><div class="label">Available</div><div class="value">{{ number_format($total_available, 0) }} kg</div></div>
            <div class="summary-item"><div class="label">Profit</div><div class="value">Rs. {{ number_format($total_profit, 0) }}</div></div>
        </div>

        <div class="section-title">Bunkers Performance</div>
        <table>
            <thead>
                <tr>
                    <th>Bunker</th><th>Location</th><th>Status</th>
                    <th>Purchased</th><th>Sold</th><th>Available</th>
                    <th>Shrinkage</th><th>Cost/KG</th><th>Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bunkers as $b)
                <tr>
                    <td class="text-left"><strong>{{ $b['name'] }}</strong></td>
                    <td>{{ $b['location'] }}</td>
                    <td><span class="status {{ $b['status'] }}">{{ $b['status'] }}</span></td>
                    <td>{{ number_format($b['purchased'], 0) }}</td>
                    <td>{{ number_format($b['sold'], 0) }}</td>
                    <td>{{ number_format($b['available'], 0) }}</td>
                    <td>{{ $b['shrinkage'] }}</td>
                    <td>Rs. {{ number_format($b['cost_per_kg'], 2) }}</td>
                    <td class="{{ $b['profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">Rs. {{ number_format($b['profit'], 0) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3">TOTAL</td>
                    <td>{{ number_format($total_purchased, 0) }}</td>
                    <td>{{ number_format($total_sold, 0) }}</td>
                    <td>{{ number_format($total_available, 0) }}</td>
                    <td>{{ number_format($total_shrinkage, 0) }} kg</td>
                    <td></td>
                    <td class="{{ $total_profit >= 0 ? 'profit-positive' : 'profit-negative' }}">Rs. {{ number_format($total_profit, 0) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">Generated by {{ $businessName }} ERP &bull; {{ now()->format('d M Y h:i A') }}</div>
    </div>
</body>
</html>
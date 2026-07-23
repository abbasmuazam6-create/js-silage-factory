<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Seasonal Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', 'Helvetica', 'Arial', sans-serif;
            font-size: 18px;
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
            padding: 7px 24px; border-radius: 4px; font-size: 13px;
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

        /* Section Title */
        .section-title {
            font-size: 11px; font-weight: 700; color: #1a1a2e;
            margin-top: 8px; margin-bottom: 3px; text-transform: uppercase;
            letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px;
        }

        /* Summary Grid */
        .summary-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin-bottom: 8px;
        }
        .summary-item {
            background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 4px;
            padding: 5px 8px; text-align: center;
        }
        .summary-item .label { font-size: 7px; color: #6b7280; text-transform: uppercase; font-weight: 600; letter-spacing: 0.3px; }
        .summary-item .value { font-size: 14px; font-weight: 700; color: #1a1a2e; margin-top: 1px; }

        /* Chart */
        .chart-container {
            margin: 6px 0 8px 0; padding: 10px; border: 1px solid #e5e7eb; border-radius: 4px;
            background: #fafbfc;
        }
        .chart-title { font-size: 9px; font-weight: 600; margin-bottom: 6px; color: #1a1a2e; }
        .chart {
            display: flex; align-items: flex-end; gap: 20px; height: 130px; padding: 4px 0;
        }
        .chart-bar {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            height: 100%; justify-content: flex-end;
        }
        .chart-bar .bar {
            width: 100%; max-width: 45px; border-radius: 3px 3px 0 0; min-height: 4px;
            background: #1a56db; transition: height 0.5s;
        }
        .chart-bar .label { font-size: 7.5px; margin-top: 4px; color: #4b5563; font-weight: 500; }
        .chart-bar .value { font-size: 7.5px; font-weight: 600; margin-top: 2px; color: #1a1a2e; }
        .profit-positive { color: #16a34a; }
        .profit-negative { color: #dc2626; }

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

        /* Footer */
        .footer {
            margin-top: 10px; padding-top: 5px; border-top: 1px solid #e5e7eb;
            font-size: 7px; color: #6b7280; text-align: center;
        }
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
                <div class="title">Seasonal Performance Report</div>
                <div class="sub">Report Date: {{ now()->format('d M Y h:i A') }}</div>
            </div>
        </div>

        <div class="section-title">Summary</div>
        <div class="summary-grid">
            <div class="summary-item"><div class="label">Seasons</div><div class="value">{{ $total_seasons }}</div></div>
            <div class="summary-item"><div class="label">Bunkers</div><div class="value">{{ $total_bunkers }}</div></div>
            <div class="summary-item"><div class="label">Revenue</div><div class="value">Rs. {{ number_format($total_revenue, 0) }}</div></div>
            <div class="summary-item"><div class="label">Profit</div><div class="value">Rs. {{ number_format($total_profit, 0) }}</div></div>
        </div>

        <div class="section-title">Profit by Season</div>
        <div class="chart-container">
            <div class="chart">
                @foreach($seasons as $s)
                <div class="chart-bar">
                    <div class="bar" style="height: {{ $max_profit > 0 ? (($s['profit'] / $max_profit) * 100) : 0 }}%; background: {{ $s['profit'] >= 0 ? '#1a56db' : '#dc2626' }};"></div>
                    <div class="value" style="color: {{ $s['profit'] >= 0 ? '#1a56db' : '#dc2626' }};">Rs. {{ number_format($s['profit'], 0) }}</div>
                    <div class="label">{{ $s['name'] }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="section-title">Season Comparison</div>
        <table>
            <thead>
                <tr>
                    <th>Season</th><th>Bunkers</th><th>Purchased</th><th>Sold</th>
                    <th>Revenue</th><th>Cost</th><th>Profit</th><th>Margin</th><th>Shrinkage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($seasons as $s)
                <tr>
                    <td class="text-left"><strong>{{ $s['name'] }}</strong></td>
                    <td>{{ $s['bunkers'] }}</td>
                    <td>{{ number_format($s['purchased'], 0) }}</td>
                    <td>{{ number_format($s['sold'], 0) }}</td>
                    <td>Rs. {{ number_format($s['revenue'], 0) }}</td>
                    <td>Rs. {{ number_format($s['cost'], 0) }}</td>
                    <td class="{{ $s['profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">Rs. {{ number_format($s['profit'], 0) }}</td>
                    <td>{{ number_format($s['margin'], 1) }}%</td>
                    <td>{{ $s['shrinkage'] }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td>{{ $total_bunkers }}</td>
                    <td>{{ number_format($total_purchased, 0) }}</td>
                    <td>{{ number_format($total_sold, 0) }}</td>
                    <td>Rs. {{ number_format($total_revenue, 0) }}</td>
                    <td>Rs. {{ number_format($total_cost, 0) }}</td>
                    <td class="{{ $total_profit >= 0 ? 'profit-positive' : 'profit-negative' }}">Rs. {{ number_format($total_profit, 0) }}</td>
                    <td>{{ $total_revenue > 0 ? number_format(($total_profit / $total_revenue) * 100, 1) : 0 }}%</td>
                    <td>{{ number_format($total_shrinkage, 0) }} kg</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">Generated by {{ $businessName }} ERP &bull; {{ now()->format('d M Y h:i A') }}</div>
    </div>
</body>
</html>
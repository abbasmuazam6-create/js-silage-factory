<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bunker Report - {{ $bunker->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            padding: 12px 15px;
            color: #1a1a2e;
            background: #ffffff;
            line-height: 1.3;
        }
        .container { max-width: 1200px; margin: 0 auto; }

        /* Print Button */
        .no-print { text-align: center; margin-bottom: 8px; }
        .no-print button {
            background: #1a1a2e; color: white; border: none;
            padding: 6px 20px; border-radius: 4px; font-size: 11px;
            cursor: pointer; margin: 0 4px;
        }
        .no-print button:hover { background: #2d2d44; }
        .no-print button.close { background: #6b7280; }
        @media print {
            .no-print { display: none; }
            body { padding: 8px 10px; }
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #1a1a2e;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .header-left { display: flex; align-items: center; gap: 12px; }
        .header-left img { 
            max-height: 70px; 
            width: auto; 
            max-width: 200px;
        }
        .header-left h1 { font-size: 15px; font-weight: 700; color: #1a1a2e; }
        .header-left .sub { font-size: 7px; color: #6b7280; display: block; }
        .header-right { text-align: right; }
        .header-right .title { font-size: 12px; font-weight: 700; color: #1a1a2e; }
        .header-right .sub { font-size: 7px; color: #6b7280; }

        /* Info Bar */
        .info-bar {
            background: #f8f9fa;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 4px 10px;
            margin-bottom: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .info-bar .item { display: flex; align-items: center; gap: 3px; font-size: 14px; }
        .info-bar .label { font-weight: 600; color: #4b5563; }
        .info-bar .value { color: #1a1a2e; }
        .info-bar .status {
            font-weight: 600; padding: 1px 6px; border-radius: 8px; font-size: 7px;
        }
        .info-bar .status.active { background: #d1fae5; color: #065f46; }
        .info-bar .status.empty { background: #fee2e2; color: #991b1b; }
        .info-bar .status.warning { background: #fef3c7; color: #92400e; }

        /* Section Title */
        .section-title {
            font-size: 10px;
            font-weight: 700;
            color: #1a1a2e;
            margin-top: 6px;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ===== TABLES ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            border: 1px solid #d1d5db;
        }
        th {
            background: #f3f4f6;
            font-weight: 600;
            color: #1a1a2e;
            padding: 3px 4px;
            border: 1px solid #d1d5db;
            text-align: center;
            text-transform: uppercase;
            font-size: 7px;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        td {
            padding: 2px 4px;
            border: 1px solid #d1d5db;
            color: #1a1a2e;
            text-align: center;
            white-space: nowrap;
        }
        .text-left { text-align: left; }

        /* Total Row */
        .total-row td {
            font-weight: 700;
            background: #f9fafb;
            border-top: 2px solid #1a1a2e;
        }

        /* ===== SUMMARY TABLE ===== */
        .summary-table {
            border: 2px solid #1a1a2e;
            font-size: 9px;
            width: 100%;
        }
        .summary-table td {
            padding: 4px 8px;
            border: 2px solid #1a1a2e;
            color: #1a1a2e;
            text-align: center;
            white-space: nowrap;
        }
        .summary-table .label {
            font-weight: 600;
            background: #f8f9fa;
            text-align: left;
        }
        .summary-table .value {
            font-weight: 700;
            font-size: 10px;
            color: #1a1a2e;
        }

        /* Profit Row */
        .profit-row td {
            font-weight: 700;
            background: #f0fdf4;
            font-size: 11px;
            border-top: 3px solid #1a1a2e;
            color: #1a1a2e;
        }
        .profit-row.loss td {
            background: #fef2f2;
        }

        /* Footer */
        .footer {
            margin-top: 8px;
            padding-top: 4px;
            border-top: 1px solid #d1d5db;
            font-size: 7px;
            color: #6b7280;
            text-align: center;
        }

        /* Column widths */
        .col-supplier { width: 16%; }
        .col-date { width: 13%; }
        .col-kg { width: 10%; }
        .col-cost { width: 12%; }
        .col-cost-kg { width: 9%; }
        .col-area { width: 8%; }
        .col-type { width: 10%; }
        .col-units { width: 8%; }
        .col-revenue { width: 12%; }
        .col-avg { width: 10%; }
        .col-category { width: 25%; }
        .col-amount { width: 12%; }
    </style>
</head>
<body>

    <div class="container">

        <!-- Print Button -->
        <div class="no-print">
            <button onclick="window.print()">🖨️ Print / Save as PDF</button>
            <button class="close" onclick="window.close()">✕ Close</button>
        </div>

        <!-- ===== HEADER ===== -->
        <div class="header">
            <div class="header-left">
                @if($logo)
                    <img src="{{ $logo }}" alt="Logo" />
                @endif
                <div>
                    <h1>{{ $businessName }}</h1>
                    <span class="sub">Export Quality Products</span>
                </div>
            </div>
            <div class="header-right">
                <div class="title">Bunker Report</div>
                <div class="sub">Report Date: {{ now()->format('d M Y h:i A') }}</div>
            </div>
        </div>

        <!-- ===== BUNKER INFO ===== -->
        <div class="info-bar">
            <div class="item"><span class="label">Bunker:</span> <span class="value">{{ $bunker->name }}</span></div>
            <div class="item"><span class="label">Location:</span> <span class="value">{{ $bunker->location ?? 'No location' }}</span></div>
            <div class="item"><span class="label">Season:</span> <span class="value">{{ $bunker->season->name ?? 'No season' }}</span></div>
            <div class="item">
                <span class="label">Status:</span>
                <span class="status {{ $bunker->status }}">{{ ucfirst($bunker->status) }}</span>
            </div>
        </div>

        <!-- ===== PURCHASES ===== -->
        <div class="section-title">Purchases</div>
        @if($bunker->purchases->count())
            <table>
                <thead>
                    <tr>
                        <th class="col-supplier">Supplier</th>
                        <th class="col-date">Date</th>
                        <th class="col-kg">KG</th>
                        <th class="col-cost">Cost (Rs.)</th>
                        <th class="col-cost-kg">Cost/KG</th>
                        <th class="col-area">Area</th>
                    </tr>
                </thead>
                <tbody>
                    @php $pTotalKg = 0; $pTotalCost = 0; @endphp
                    @foreach($bunker->purchases as $p)
                        @php $pTotalKg += $p->weight_kg; $pTotalCost += $p->cost; @endphp
                        <tr>
                            <td>{{ $p->supplier->name ?? 'Unknown' }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->purchase_date)->format('d M Y') }}</td>
                            <td>{{ number_format($p->weight_kg, 0) }}</td>
                            <td>{{ number_format($p->cost, 0) }}</td>
                            <td>{{ $p->weight_kg > 0 ? number_format($p->cost / $p->weight_kg, 2) : '0.00' }}</td>
                            <td>{{ $p->area ? number_format($p->area, 2) : '-' }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2">TOTAL</td>
                        <td>{{ number_format($pTotalKg, 0) }}</td>
                        <td>{{ number_format($pTotalCost, 0) }}</td>
                        <td>{{ $pTotalKg > 0 ? number_format($pTotalCost / $pTotalKg, 2) : '0.00' }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        @else
            <p style="color:#6b7280;font-size:9px;padding:4px 0;">No purchases recorded.</p>
        @endif

        <!-- ===== EXPENSES ===== -->
        <div class="section-title">Expenses</div>
        @if($bunker->expenses->count())
            <table>
                <thead>
                    <tr>
                        <th class="col-date">Date</th>
                        <th class="col-category">Category</th>
                        <th class="col-amount">Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $eTotal = 0; @endphp
                    @foreach($bunker->expenses as $e)
                        @php $eTotal += $e->amount; @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($e->date)->format('d M Y') }}</td>
                            <td>{{ $e->category->name ?? 'Uncategorized' }}</td>
                            <td>{{ number_format($e->amount, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2">TOTAL</td>
                        <td>{{ number_format($eTotal, 0) }}</td>
                    </tr>
                </tbody>
            </table>
        @else
            <p style="color:#6b7280;font-size:9px;padding:4px 0;">No expenses recorded.</p>
        @endif

        <!-- ===== SALES ===== -->
        <div class="section-title">Sales</div>
        @if($bunker->saleItems->count())
            <table>
                <thead>
                    <tr>
                        <th class="col-type">Type</th>
                        <th class="col-units">Units</th>
                        <th class="col-kg">KG</th>
                        <th class="col-revenue">Revenue (Rs.)</th>
                        <th class="col-avg">Avg Price</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $salesGrouped = $bunker->saleItems->groupBy('sale_type');
                        $grandKg = 0;
                        $grandRevenue = 0;
                        $grandUnits = 0;
                        $types = ['open' => 'Open', 'bags' => 'Bags', 'bales' => 'Bales'];
                    @endphp
                    @foreach($types as $key => $label)
                        @php
                            $items = $salesGrouped->get($key, collect());
                            $kg = $items->sum('weight_kg');
                            $revenue = $items->sum('total_price');
                            $units = $items->sum('units');
                            $avg = $kg > 0 ? $revenue / $kg : 0;
                            $grandKg += $kg;
                            $grandRevenue += $revenue;
                            $grandUnits += $units;
                        @endphp
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ $units > 0 ? number_format($units, 0) : '-' }}</td>
                            <td>{{ number_format($kg, 0) }}</td>
                            <td>{{ number_format($revenue, 0) }}</td>
                            <td>{{ number_format($avg, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>TOTAL</td>
                        <td>{{ number_format($grandUnits, 0) }}</td>
                        <td>{{ number_format($grandKg, 0) }}</td>
                        <td>{{ number_format($grandRevenue, 0) }}</td>
                        <td>{{ $grandKg > 0 ? number_format($grandRevenue / $grandKg, 2) : '0.00' }}</td>
                    </tr>
                </tbody>
            </table>
        @else
            <p style="color:#6b7280;font-size:9px;padding:4px 0;">No sales recorded.</p>
        @endif

        <!-- ===== MOISTURE LOSS ===== -->
        <div class="section-title">Moisture Loss</div>
        @if($bunker->verifications->count())
            <table>
                <thead>
                    <tr>
                        <th class="col-date">Date</th>
                        <th class="col-cost">Loss KG</th>
                        <th class="col-avg">Loss %</th>
                    </tr>
                </thead>
                <tbody>
                    @php $lossKg = 0; @endphp
                    @foreach($bunker->verifications as $v)
                        @php $lossKg += $v->shrinkage_kg; @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($v->date_verified)->format('d M Y') }}</td>
                            <td>{{ number_format($v->shrinkage_kg, 0) }}</td>
                            <td>{{ number_format($v->shrinkage_percentage, 1) }}%</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>TOTAL</td>
                        <td>{{ number_format($lossKg, 0) }}</td>
                        <td>{{ number_format($bunker->shrinkage_percentage, 1) }}%</td>
                    </tr>
                </tbody>
            </table>
        @else
            <p style="color:#6b7280;font-size:9px;padding:4px 0;">No moisture loss records.</p>
        @endif

        <!-- ===== BUNKER SUMMARY ===== -->
        <div class="section-title">Bunker Summary</div>
        <table class="summary-table">
            <tr>
                <td class="label" style="width:20%;text-align:left;">Total Purchased</td>
                <td class="value" style="width:30%;">{{ number_format($bunker->total_purchased_kg, 0) }} kg</td>
                <td class="label" style="width:20%;text-align:left;">Total Sold</td>
                <td class="value" style="width:30%;">{{ number_format($bunker->total_sold_kg, 0) }} kg</td>
            </tr>
            <tr>
                <td class="label" style="text-align:left;">Moisture Loss</td>
                <td class="value">{{ number_format($bunker->shrinkage_kg, 0) }} kg ({{ number_format($bunker->shrinkage_percentage, 1) }}%)</td>
                <td class="label" style="text-align:left;">Cost / KG</td>
                <td class="value">Rs. {{ number_format($bunker->cost_per_kg, 2) }}</td>
            </tr>
            <tr>
                <td class="label" style="text-align:left;">Total Revenue</td>
                <td class="value">Rs. {{ number_format($bunker->total_revenue, 0) }}</td>
                <td class="label" style="text-align:left;">Total Cost</td>
                <td class="value">Rs. {{ number_format($bunker->total_cost_with_expenses, 0) }}</td>
            </tr>
            <tr class="profit-row {{ $bunker->total_profit < 0 ? 'loss' : '' }}">
                <td class="label" style="font-weight:700;font-size:11px;text-align:left;">Total Profit</td>
                <td class="value" style="font-size:13px;font-weight:700;" colspan="3">
                    Rs. {{ number_format($bunker->total_profit, 0) }}
                </td>
            </tr>
        </table>

        <!-- ===== FOOTER ===== -->
        <div class="footer">
            Generated by {{ $businessName }} ERP &bull; {{ now()->format('d M Y h:i A') }}
        </div>

    </div>

</body>
</html>
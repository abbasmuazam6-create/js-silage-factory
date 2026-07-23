<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Report - {{ now()->format('d M Y') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            padding: 20px 25px;
            color: #1a1a2e;
            background: #ffffff;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
        }
        .no-print { text-align: center; margin-bottom: 15px; }
        .no-print button {
            background: #1a1a2e; color: white; border: none;
            padding: 8px 25px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer; margin: 0 4px;
        }
        .no-print button:hover { background: #2d2d44; }
        .no-print button.close { background: #6b7280; }
        @media print { .no-print { display: none; } body { padding: 10px; } }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #1a1a2e;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header-left img { 
            max-height: 70px; 
            width: auto; 
            max-width: 180px;
        }
        .header-left .business-name { 
            font-size: 20px; 
            font-weight: 700; 
            color: #1a1a2e;
        }
        .header-left .tagline { 
            font-size: 10px; 
            color: #6b7280; 
            display: block;
            letter-spacing: 0.5px;
        }
        .header-right { text-align: right; }
        .header-right .title { 
            font-size: 18px; 
            font-weight: 700; 
            color: #1a1a2e;
        }
        .header-right .sub { 
            font-size: 11px; 
            color: #6b7280;
        }

        .filters {
            background: #f8f9fa;
            padding: 8px 14px;
            border-radius: 4px;
            margin-bottom: 12px;
            font-size: 11px;
            color: #4b5563;
        }
        .filters .label { font-weight: 600; color: #1a1a2e; }
        .filters .filter-item { 
            display: inline-block; 
            margin-right: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 10px;
        }
        th {
            background: #f1f5f9;
            font-weight: 700;
            padding: 7px 8px;
            border: 1px solid #d1d5db;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #1a1a2e;
        }
        td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
            color: #1a1a2e;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }

        .status-badge {
            display: inline-block;
            padding: 1px 10px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .status-due { background: #fee2e2; color: #991b1b; }

        .total-row td {
            background: #f8fafc;
            font-weight: 700;
            border-top: 2px solid #1a1a2e;
            padding-top: 7px;
            font-size: 11px;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 10px;
            padding: 10px 14px;
            background: #f8fafc;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }
        .summary .item {
            text-align: center;
        }
        .summary .item .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .summary .item .value {
            font-size: 14px;
            font-weight: 700;
            color: #1a1a2e;
            margin-top: 2px;
        }

        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
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
                <img src="https://i.ibb.co/xKG273Sm/JS-Final-logo.jpg" alt="Logo" />
                <div>
                    <div class="business-name">{{ $businessName }}</div>
                    <span class="tagline">Export Quality Products</span>
                </div>
            </div>
            <div class="header-right">
                <div class="title">SALES REPORT</div>
                <div class="sub">Generated: {{ now()->format('d M Y h:i A') }}</div>
            </div>
        </div>

        <div class="filters">
            @if($filters['season'] ?? false)
                <span class="filter-item"><span class="label">Season:</span> {{ $filters['season'] }}</span>
            @endif
            @if($filters['location'] ?? false)
                <span class="filter-item"><span class="label">Location:</span> {{ $filters['location'] }}</span>
            @endif
            @if($filters['bunker'] ?? false)
                <span class="filter-item"><span class="label">Bunker:</span> {{ $filters['bunker'] }}</span>
            @endif
            @if($filters['customer'] ?? false)
                <span class="filter-item"><span class="label">Customer:</span> {{ $filters['customer'] }}</span>
            @endif
            @if($filters['sale_type'] ?? false)
                <span class="filter-item"><span class="label">Type:</span> {{ ucfirst($filters['sale_type']) }}</span>
            @endif
            @if($filters['date_from'] ?? false)
                <span class="filter-item"><span class="label">From:</span> {{ $filters['date_from'] }}</span>
            @endif
            @if($filters['date_to'] ?? false)
                <span class="filter-item"><span class="label">To:</span> {{ $filters['date_to'] }}</span>
            @endif
            @if(!array_filter($filters ?? []))
                <span class="filter-item"><span class="label">Filters:</span> All Records</span>
            @endif
            <span class="filter-item" style="float:right;color:#6b7280;">
                <span class="label">Total Records:</span> {{ count($sales) }}
            </span>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:13%;">Invoice</th>
                    <th style="width:15%;">Customer</th>
                    <th style="width:12%;">Bunker</th>
                    <th style="width:8%;">Type</th>
                    <th style="width:8%;" class="text-right">KG</th>
                    <th style="width:10%;" class="text-right">Price/KG</th>
                    <th style="width:12%;" class="text-right">Total</th>
                    <th style="width:12%;" class="text-right">Paid</th>
                    <th style="width:10%;" class="text-right">Due</th>
                    <th style="width:10%;" class="text-center">Status</th>
                    <th style="width:10%;" class="text-center">Date</th>
                </tr>
            </thead>
            <tbody>
                @php $totals = ['kg' => 0, 'total' => 0, 'paid' => 0, 'due' => 0]; @endphp
                @foreach($sales as $index => $sale)
                @php
                    $totals['kg'] += $sale->weight_kg ?? 0;
                    $totals['total'] += $sale->total_price ?? 0;
                    $totals['paid'] += $sale->paid_amount ?? 0;
                    $totals['due'] += $sale->due_amount ?? 0;
                    
                    $status = 'paid';
                    $statusClass = 'status-paid';
                    if (($sale->due_amount ?? 0) > 0 && ($sale->paid_amount ?? 0) > 0) {
                        $status = 'partial';
                        $statusClass = 'status-partial';
                    } elseif (($sale->due_amount ?? 0) > 0) {
                        $status = 'due';
                        $statusClass = 'status-due';
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $sale->invoice_number ?? 'N/A' }}</td>
                    <td>{{ $sale->customer->name ?? 'Unknown' }}</td>
                    <td>{{ $sale->bunker->name ?? 'Unknown' }}</td>
                    <td class="text-center">{{ ucfirst($sale->sale_type ?? 'N/A') }}</td>
                    <td class="text-right">{{ number_format($sale->weight_kg ?? 0) }}</td>
                    <td class="text-right">Rs. {{ number_format($sale->price_per_kg ?? 0, 0) }}</td>
                    <td class="text-right">Rs. {{ number_format($sale->total_price ?? 0) }}</td>
                    <td class="text-right">Rs. {{ number_format($sale->paid_amount ?? 0) }}</td>
                    <td class="text-right">Rs. {{ number_format($sale->due_amount ?? 0) }}</td>
                    <td class="text-center"><span class="status-badge {{ $statusClass }}">{{ strtoupper($status) }}</span></td>
                    <td class="text-center">{{ $sale->date ? \Carbon\Carbon::parse($sale->date)->format('d M Y') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" class="text-right">TOTAL</td>
                    <td class="text-right">{{ number_format($totals['kg']) }}</td>
                    <td class="text-right"></td>
                    <td class="text-right">Rs. {{ number_format($totals['total']) }}</td>
                    <td class="text-right">Rs. {{ number_format($totals['paid']) }}</td>
                    <td class="text-right">Rs. {{ number_format($totals['due']) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>

        <div class="summary">
            <div class="item">
                <div class="label">Total Sales</div>
                <div class="value">{{ count($sales) }}</div>
            </div>
            <div class="item">
                <div class="label">Total KG</div>
                <div class="value">{{ number_format($totals['kg']) }}</div>
            </div>
            <div class="item">
                <div class="label">Total Revenue</div>
                <div class="value">Rs. {{ number_format($totals['total']) }}</div>
            </div>
            <div class="item">
                <div class="label">Total Paid</div>
                <div class="value">Rs. {{ number_format($totals['paid']) }}</div>
            </div>
        </div>

        <div class="footer">
            Generated by {{ $businessName }} ERP &bull; {{ now()->format('d M Y h:i A') }}
        </div>
    </div>
</body>
</html>
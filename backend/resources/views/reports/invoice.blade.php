<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $sale->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            padding: 25px 30px;
            color: #1a1a2e;
            background: #ffffff;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            border: 1px solid #e5e7eb;
            padding: 30px;
            border-radius: 4px;
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

        /* Header */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #1a1a2e;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .header-left { display: flex; align-items: center; gap: 18px; }
        .header-left img { 
            max-height: 80px; 
            width: auto; 
            max-width: 200px;
        }
        .header-left h1 { font-size: 22px; font-weight: 700; }
        .header-left .sub { font-size: 11px; color: #6b7280; display: block; }
        .header-right { text-align: right; }
        .header-right .title { font-size: 20px; font-weight: 700; color: #1a1a2e; }
        .header-right .sub { font-size: 12px; color: #6b7280; }

        /* Info Box */
        .info-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 30px;
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 13px;
        }
        .info-box .label { font-weight: 600; color: #4b5563; }
        .info-box .value { color: #1a1a2e; }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 3px 14px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .status-due { background: #fee2e2; color: #991b1b; }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 15px;
        }
        th {
            background: #f3f4f6;
            font-weight: 600;
            padding: 8px 10px;
            border-bottom: 2px solid #1a1a2e;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row td {
            font-weight: 700;
            border-top: 2px solid #1a1a2e;
            padding-top: 8px;
        }

        /* Sale Type label */
        .sale-type-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 400;
        }

        /* Totals */
        .totals {
            margin-top: 8px;
            text-align: right;
            font-size: 14px;
        }
        .totals .line {
            padding: 4px 0;
        }
        .totals .line .label {
            font-weight: 600;
            display: inline-block;
            width: 140px;
            text-align: left;
        }
        .totals .line .value {
            font-weight: 700;
            display: inline-block;
            width: 130px;
            text-align: right;
        }
        .totals .grand-total {
            font-size: 17px;
            border-top: 2px solid #1a1a2e;
            padding-top: 8px;
            margin-top: 6px;
        }
        .totals .grand-total .value {
            color: #16a34a;
        }
        .totals .grand-total .value.due {
            color: #dc2626;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 11px;
            color: #6b7280;
            text-align: center;
        }
        .thank-you {
            font-size: 10px;
            font-weight: 600;
            color: #1a1a2e;
            text-align: center;
            margin-top: 10px;
        }
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
                <img src="https://i.ibb.co/xKG273Sm/JS-Final-logo.jpg" alt="Logo" style="max-height:80px;width:auto;max-width:200px;" />
                <div>
                    <h1>{{ $businessName }}</h1>
                    <span class="sub">Enterprise Resource Planning</span>
                </div>
            </div>
            <div class="header-right">
                <div class="title">INVOICE</div>
                <div class="sub">#{{ $sale->invoice_number }}</div>
                <div class="sub">Date: {{ $sale->date->format('d M Y') }}</div>
            </div>
        </div>

        <!-- ===== INFO ===== -->
        <div class="info-box">
            <div><span class="label">Customer:</span> <span class="value">{{ $sale->customer->name ?? 'Unknown' }}</span></div>
            <div><span class="label">Bunker:</span> <span class="value">{{ $sale->bunker->name ?? 'Unknown' }}</span></div>
            <div><span class="label">Sale Type:</span> <span class="value">{{ ucfirst($sale->sale_type) }}</span></div>
            <div><span class="label">Payment:</span> <span class="value">{{ $sale->paymentType->name ?? 'N/A' }}</span></div>
            <div style="grid-column: span 2;">
                <span class="label">Status:</span>
                @php
                    $status = 'paid';
                    $statusClass = 'status-paid';
                    if ($sale->due_amount > 0 && $sale->paid_amount > 0) {
                        $status = 'partial';
                        $statusClass = 'status-partial';
                    } elseif ($sale->due_amount > 0) {
                        $status = 'due';
                        $statusClass = 'status-due';
                    }
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ strtoupper($status) }}</span>
            </div>
        </div>

        <!-- ===== TABLE ===== -->
        <table>
            <thead>
                <tr>
                    <th style="width:30%;">Description</th>
                    <th style="width:15%;" class="text-right">Units</th>
                    <th style="width:15%;" class="text-right">KG</th>
                    <th style="width:18%;" class="text-right">Price/KG</th>
                    <th style="width:22%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Silage
                        <span class="sale-type-label">({{ ucfirst($sale->sale_type) }})</span>
                        @if($sale->sale_type !== 'open' && $sale->units > 0)
                            <span class="sale-type-label">- {{ $sale->units }} {{ $sale->sale_type }}</span>
                        @endif
                    </td>
                    <td class="text-right">{{ $sale->units > 0 ? number_format($sale->units, 0) : '-' }}</td>
                    <td class="text-right">{{ number_format($sale->weight_kg, 0) }}</td>
                    <td class="text-right">Rs. {{ number_format($sale->price_per_kg, 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($sale->total_price, 0) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- ===== TOTALS ===== -->
        <div class="totals">
            <div class="line">
                <span class="label">Gross Total</span>
                <span class="value">Rs. {{ number_format($sale->total_price, 0) }}</span>
            </div>
            @if(($sale->discount ?? 0) > 0)
            <div class="line" style="color: #dc2626;">
                <span class="label">Discount</span>
                <span class="value">- Rs. {{ number_format($sale->discount ?? 0, 0) }}</span>
            </div>
            @endif
            <div class="line grand-total" style="font-size:17px;border-top:2px solid #1a1a2e;padding-top:8px;margin-top:6px;">
                <span class="label">Net Total</span>
                <span class="value" style="color:#16a34a;">Rs. {{ number_format($sale->total_price - ($sale->discount ?? 0), 0) }}</span>
            </div>
            <div class="line">
                <span class="label">Paid Amount</span>
                <span class="value">Rs. {{ number_format($sale->paid_amount ?? 0, 0) }}</span>
            </div>
            <div class="line">
                <span class="label">Balance Due</span>
                <span class="value {{ ($sale->due_amount ?? 0) > 0 ? 'due' : '' }}" style="{{ ($sale->due_amount ?? 0) > 0 ? 'color:#dc2626;' : 'color:#16a34a;' }}">
                    Rs. {{ number_format($sale->due_amount ?? 0, 0) }}
                </span>
            </div>
        </div>

        <!-- ===== THANK YOU ===== -->
        <div class="thank-you">{{ $invoiceFooter }}</div>

        <!-- ===== FOOTER ===== -->
        <div class="footer">
            Generated by {{ $businessName }} ERP &bull; {{ now()->format('d M Y h:i A') }}
        </div>
    </div>
</body>
</html>
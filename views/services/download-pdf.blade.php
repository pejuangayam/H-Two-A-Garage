<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color:#333; margin: 0; padding: 22px; }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }
        .header img { height: 70px; margin-right: 20px; }
        .header .info { font-size: 12px; line-height: 1.6; text-align: left; }

        /* Details section */
        .details { 
            margin-bottom: 18px; 
            font-size: 13px;
            line-height: 1.5;
        }
        .details .row { 
            margin: 2px 0; 
        }
        .details .label { 
            display: inline-block;
            width: 120px;   /* fixed label width for alignment */
            font-weight: 700; 
        }
        .details .value { 
            display: inline-block;
        }

        /* One line row (Invoice Date & No) */
        .row-inline { width: 100%; margin-top: 10px; }
        .row-inline .left,
        .row-inline .right { display: inline-block; width: 49%; }
        .row-inline .right { text-align: right; }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        colgroup col.no    { width: 9%; }
        colgroup col.item  { width: 41%; }
        colgroup col.qty   { width: 16%; }
        colgroup col.unit  { width: 17%; }
        colgroup col.total { width: 17%; }

        thead th { background: #222; color: #fff; font-size: 13px; padding: 8px; border: none; text-align: center; }
        tbody td { border: 1px solid #ddd; padding: 8px; font-size: 12px; text-align: center; }
        tbody td.text-left { text-align: left; }
        tbody tr:nth-child(even) { background: #f9f9f9; }

        /* Totals rows */
        tr.subtotals td { border-top: none; }
        td.label-cell { text-align: right; padding-right: 10px; font-weight: 700; background: #f7f7f7; }
        td.amount-cell { text-align: right; }

        tr.grand td.label-cell { background: #222; color: #fff; font-size: 15px; }
        tr.grand td.amount-cell { background: #222; color: #fff; font-weight: 700; font-size: 15px; }
    </style>
</head>
<body>

<div class="header">
    <img src="{{ public_path('h2avector.png') }}" alt="Logo">
    <div class="info">
        <strong>H TWO A Garage</strong><br>
        <strong>202203166516 (003412452-X)</strong><br>
        No. 100, Kampung Bayas, Mukim Ulu Melaka<br>
        07000 Langkawi, Kedah<br>
        H/P: 011-3296 2286
    </div>
</div>

<div class="details">
    <div class="row">
        <span class="label">Name:</span>
        <span class="value">{{ $vehicle->name ?? '-' }}</span>
    </div>
    <div class="row">
        <span class="label">No Phone:</span>
        <span class="value">{{ $vehicle->noPhone }}</span>
    </div>
    <div class="row">
        <span class="label">Model:</span>
        <span class="value">{{ $vehicle->model ?? '-' }}</span>
    </div>
    <div class="row">
        <span class="label">Vehicle No:</span>
        <span class="value">{{ $vehicle->vehicle_id }}</span>
    </div>
    <div class="row">
        <span class="label">Kilometer:</span>
        <span class="value">{{ $vehicle->kilometer ? $vehicle->kilometer . ' km' : '-' }}</span>
    </div>

    {{-- Invoice Date (left) and Invoice No (right) --}}
    <div class="row-inline">
        <div class="left">
            <span class="label">Invoice Date:</span>
            <span class="value">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
        </div>
        <div class="right">
            <span class="label">Invoice No:</span>
            <span class="value">{{ str_pad($invoiceNo, 4, '0', STR_PAD_LEFT) }}</span>
        </div>
    </div>
</div>

@php
    $subtotal = $items->sum('total');
    $labour   = $items->sum('labour_total');
    $grand    = $subtotal + $labour;
@endphp

<table>
    <colgroup>
        <col class="no"><col class="item"><col class="qty"><col class="unit"><col class="total">
    </colgroup>
    <thead>
        <tr>
            <th>No</th>
            <th>Item</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $i => $svc)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-left">{{ $svc->items }}</td>
                <td>{{ $svc->quantity }}</td>
                <td>RM{{ number_format($svc->per_price, 2) }}</td>
                <td class="amount-cell">RM{{ number_format($svc->total, 2) }}</td>
            </tr>
        @endforeach

        {{-- Totals --}}
        <tr class="subtotals">
            <td colspan="4" class="label-cell">Subtotal</td>
            <td class="amount-cell">RM{{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr class="subtotals">
            <td colspan="4" class="label-cell">Labour</td>
            <td class="amount-cell">RM{{ number_format($labour, 2) }}</td>
        </tr>
        <tr class="grand">
            <td colspan="4" class="label-cell">Total</td>
            <td class="amount-cell">RM{{ number_format($grand, 2) }}</td>
        </tr>
    </tbody>
</table>

<!-- Only this section has been modified -->
<div style="position: absolute; bottom: 10px; left: 0; width: 100%;">
    <!-- Left side -->
    <div style="display: inline-block; width: 48%; text-align: left; vertical-align: top;">
        <div style="border-top: 1px solid #000; width: 70%; margin-bottom: 8px;"></div>
        <span style="font-weight: 700;">Received by / Diterima oleh</span>
    </div>

    <!-- Right side -->
    <div style="display: inline-block; width: 48%; text-align: right; vertical-align: top;">
        <div style="border-top: 1px solid #000; width: 70%; margin: 0 0 8px auto;"></div>
        <span style="font-weight: 700;">Signature / Tandatangan</span>
    </div>
</div>

</body>
</html>
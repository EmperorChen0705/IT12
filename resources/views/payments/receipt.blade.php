<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acknowledgement Receipt - {{ $payment->booking_id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #555;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .invoice-container {
            background-color: #fcf3cf; /* Light yellow */
            width: 800px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            border: 1px solid #ccc;
            position: relative;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .company-info h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .company-info p {
            margin: 2px 0;
            font-size: 12px;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #d32f2f; /* Reddish color for ACKNOWLEDGEMENT RECEIPT */
        }
        .invoice-details .invoice-no {
            color: #d32f2f;
            font-size: 18px;
            font-weight: bold;
        }
        .sold-to-section {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .row {
            display: flex;
            margin-bottom: 5px;
        }
        .label {
            width: 120px;
            font-weight: bold;
        }
        .value {
            flex: 1;
            border-bottom: 1px solid #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 5px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 14px;
        }
        th {
            text-align: center;
            background-color: #f9e79f;
        }
        .col-desc { width: 50%; }
        .col-qty { width: 10%; text-align: center; }
        .col-price { width: 20%; text-align: right; }
        .col-amount { width: 20%; text-align: right; }
        
        .footer-section {
            display: flex;
            border: 1px solid #000;
            font-size: 12px;
        }
        .footer-left {
            flex: 1;
            border-right: 1px solid #000;
        }
        .footer-right {
            width: 300px;
        }
        .footer-row {
            display: flex;
            border-bottom: 1px solid #000;
        }
        .footer-row:last-child {
            border-bottom: none;
        }
        .footer-label {
            flex: 1;
            padding: 2px 5px;
            border-right: 1px solid #000;
        }
        .footer-value {
            width: 100px;
            padding: 2px 5px;
            text-align: right;
        }
        .signatures {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }
        .sig-block {
            width: 40%;
            text-align: center;
        }
        .sig-line {
            border-bottom: 1px solid #000;
            margin-top: 30px;
            margin-bottom: 5px;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #333;
            color: #fff;
            border: none;
            cursor: pointer;
            font-family: sans-serif;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .invoice-container {
                box-shadow: none;
                width: 100%;
                border: none;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div style="position: fixed; top: 20px; right: 20px; display: flex; gap: 10px;">
        <a href="{{ route('payments.index') }}" class="print-btn" style="text-decoration: none; background: #666;">Back</a>
        <button class="print-btn" onclick="window.print()">Print Receipt</button>
    </div>

    <div class="invoice-container">
        <div class="header">
            <div class="company-info">
                <h1>SubWfour</h1>

                <p>E, Quirino St. cor. Jacinto ext. 8000 Davao City, Philippines</p>
            </div>
            <div class="invoice-details">
                <h2>ACKNOWLEDGEMENT RECEIPT</h2>
                <div class="invoice-no">Invoice No. {{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div>Date: {{ $payment->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        <div class="sold-to-section">
            <div class="row">
                <span class="label">SOLD TO:</span>
                <span class="value">{{ $payment->customer_name }}</span>
            </div>
            <div class="row">
                <span class="label">ADDRESS:</span>
                <span class="value">{{ $payment->email }} / {{ $payment->contact_number }}</span>
            </div>
            <div class="row" style="margin-top: 5px;">
                <span class="label">TERMS:</span>
                <span class="value">{{ ucfirst($payment->method) }}</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="col-desc">Item Description</th>
                    <th class="col-qty">Quantity</th>
                    <th class="col-price">Unit Price</th>
                    <th class="col-amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $items = $payment->service ? $payment->service->items : [];
                    $rowCount = 0;
                @endphp
                
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->item->name ?? 'Item' }}</td>
                        <td class="col-qty">{{ $item->quantity }}</td>
                        <td class="col-price">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="col-amount">{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @php $rowCount++; @endphp
                @endforeach

                @if($payment->service && $payment->service->labor_fee > 0)
                    <tr>
                        <td>Labor Fee via Reference Code: {{ $payment->service->reference_code }}</td>
                        <td class="col-qty">1</td>
                        <td class="col-price">{{ number_format($payment->service->labor_fee, 2) }}</td>
                        <td class="col-amount">{{ number_format($payment->service->labor_fee, 2) }}</td>
                    </tr>
                    @php $rowCount++; @endphp
                @endif

                {{-- Fill empty rows to make it look like the form --}}
                @for($i = $rowCount; $i < 8; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <div class="footer-section">
            <div class="footer-right" style="width: 100%; border: 1px solid #000;">
                <div class="footer-row">
                    <div class="footer-label">Subtotal</div>
                    <div class="footer-value">{{ number_format($payment->amount, 2) }}</div>
                </div>
                <div class="footer-row">
                    <div class="footer-label" style="font-weight: bold;">TOTAL AMOUNT DUE</div>
                    <div class="footer-value" style="font-weight:bold;">{{ number_format($payment->amount, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="signatures">
            <div class="sig-block">
                <p>Received the above items in good order:</p>
                <div class="sig-line"></div>
                <p>Customer's Signature</p>
            </div>
            <div class="sig-block">
                <p>By:</p>
                <div class="sig-line">{{ auth()->user()->name ?? 'Cashier' }}</div>
                <p>Cashier/Authorized Representative</p>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px; font-size: 10px;">
            <p>THIS INVOICE SHALL BE VALID FOR FIVE (5) YEARS FROM THE DATE OF THE PERMIT TO USE.</p>
        </div>
    </div>
</body>
</html>

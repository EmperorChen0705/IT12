<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
        }

        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 20px;
        }

        .details {
            margin-bottom: 20px;
            font-size: 10px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>Inventory Report</h1>
    <div class="details">
        Generated on: {{ now()->format('Y-m-d H:i:s') }}<br>
        Low Stock Filter: {{ $lowStock ? 'Yes' : 'No' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th style="text-align:right;">Current Stock</th>
                <th style="text-align:right;">Unit Price</th>
                <th style="text-align:right;">Total Value</th>
                <th style="text-align:right;">Reorder Lvl</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td style="text-align:right;">{{ $item->quantity }} {{ $item->unit }}</td>
                    <td style="text-align:right;">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                    <td style="text-align:right;">5 (Default)</td>
                    <td>
                        @if($item->quantity <= 0)
                            <span style="color:red; font-weight:bold;">Out of Stock</span>
                        @elseif($item->quantity <= 5)
                            <span style="color:orange; font-weight:bold;">Low Stock</span>
                        @else
                            <span style="color:green;">Good</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
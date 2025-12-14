<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
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
                <th style="width: 80px;">SKU</th>
                <th>Item Name</th>
                <th>Category</th>
                <th style="text-align:right;">Stock</th>
                <th style="text-align:right;">Reorder Lvl</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->category }}</td>
                    <td style="text-align:right;">{{ $item->stock_quantity }}</td>
                    <td style="text-align:right;">{{ $item->reorder_level }}</td>
                    <td>
                        @if($item->stock_quantity <= $item->reorder_level)
                            <span style="color:red; font-weight:bold;">Low Stock</span>
                        @else
                            In Stock
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
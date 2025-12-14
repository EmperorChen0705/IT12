<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Booking Report</title>
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

        .status-badge {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
    </style>
</head>

<body>
    <h1>Booking Report</h1>
    <div class="details">
        Generated on: {{ now()->format('Y-m-d H:i:s') }}<br>
        Status: {{ ucfirst($status ?? 'All') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">Ref #</th>
                <th>Customer</th>
                <th>Service Type</th>
                <th>Plate Number</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
                <tr>
                    <td>{{ $booking->id }}</td>
                    <td>
                        {{ $booking->first_name }} {{ $booking->last_name }}<br>
                        <span style="color:#666;font-size:9px;">{{ $booking->email }}</span>
                    </td>
                    <td>{{ $booking->service_type }}</td>
                    <td>{{ $booking->plate_number ?? '—' }}</td>
                    <td>{{ ucfirst($booking->status) }}</td>
                    <td>{{ $booking->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
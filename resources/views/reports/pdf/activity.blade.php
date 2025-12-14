<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Activity Log Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
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
            padding: 6px;
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
    <h1>Activity Log Report</h1>
    <div class="details">
        Generated on: {{ now()->format('Y-m-d H:i:s') }}<br>
        Date Range: {{ $dateFrom ?? 'All' }} to {{ $dateTo ?? 'All' }}<br>
        User: {{ $user ? $user->name : 'All' }} | Event: {{ $event ?? 'All' }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 120px;">Time</th>
                <th style="width: 100px;">User</th>
                <th style="width: 120px;">Subject</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->occurred_at->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $log->user?->name ?? '—' }}</td>
                    <td>
                        @if($log->subject_type)
                            {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $log->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
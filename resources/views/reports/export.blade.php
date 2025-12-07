<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Report - SubWfour</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #555;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .report-container {
            background-color: #fcf3cf;
            width: 900px;
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
        .report-details {
            text-align: right;
        }
        .report-details h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #d32f2f;
        }
        .report-details .report-date {
            font-size: 14px;
            margin-top: 5px;
        }
        .filter-section {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 12px;
            background-color: #f9e79f;
        }
        .filter-row {
            display: flex;
            margin-bottom: 3px;
        }
        .filter-label {
            width: 120px;
            font-weight: bold;
        }
        .filter-value {
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 10px;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: left;
        }
        th {
            background-color: #f9e79f;
            font-weight: bold;
            text-align: center;
        }
        .col-time { width: 15%; }
        .col-user { width: 12%; }
        .col-subject { width: 15%; }
        .col-desc { width: 58%; }
        
        .section-header {
            font-size: 16px;
            font-weight: bold;
            margin: 25px 0 10px 0;
            padding: 8px;
            background-color: #f9e79f;
            border: 1px solid #000;
            text-align: center;
        }
        
        .footer-info {
            margin-top: 15px;
            font-size: 11px;
            text-align: center;
            padding-top: 10px;
            border-top: 1px solid #000;
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
            text-decoration: none;
            display: inline-block;
        }
        .print-btn:hover {
            background: #555;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .report-container {
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
        <a href="{{ route('reports.index', request()->query()) }}" class="print-btn" style="background: #666;">Back</a>
        <button class="print-btn" onclick="window.print()">Print Report</button>
    </div>

    <div class="report-container">
        <div class="header">
            <div class="company-info">
                <h1>SubWfour</h1>
                <p>Inventory & Booking System</p>
                <p>Activity Report</p>
            </div>
            <div class="report-details">
                <h2>ACTIVITY REPORT</h2>
                <div class="report-date">Generated: {{ now()->format('M d, Y H:i') }}</div>
            </div>
        </div>

        <div class="filter-section">
            <div style="font-weight: bold; margin-bottom: 5px;">FILTER CRITERIA:</div>
            <div class="filter-row">
                <span class="filter-label">Date Range:</span>
                <span class="filter-value">
                    @if($rangeStart || $rangeEnd)
                        {{ $rangeStart ? $rangeStart->format('Y-m-d') : 'Any' }} 
                        to 
                        {{ $rangeEnd ? $rangeEnd->format('Y-m-d') : 'Any' }}
                    @else
                        All Dates
                    @endif
                </span>
            </div>
            <div class="filter-row">
                <span class="filter-label">User:</span>
                <span class="filter-value">{{ $userName ?? 'All Users' }}</span>
            </div>
            <div class="filter-row">
                <span class="filter-label">Event Type:</span>
                <span class="filter-value">{{ $event ?: 'All Events' }}</span>
            </div>
            <div class="filter-row">
                <span class="filter-label">Search Query:</span>
                <span class="filter-value">{{ $search ?: 'None' }}</span>
            </div>
            <div class="filter-row">
                <span class="filter-label">Activity Logs:</span>
                <span class="filter-value">{{ $logs->count() }} records</span>
            </div>
            <div class="filter-row">
                <span class="filter-label">Stock-Out Records:</span>
                <span class="filter-value">{{ $stockOuts->count() }} records</span>
            </div>
        </div>

        <div class="section-header">ACTIVITY LOGS ({{ $logs->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th class="col-time">Time</th>
                    <th class="col-user">User</th>
                    <th class="col-subject">Subject</th>
                    <th class="col-desc">Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="white-space:nowrap; font-size: 10px;">{{ $log->occurred_at->format('Y-m-d H:i:s') }}</td>
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
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px;">No activity records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($stockOuts->isNotEmpty())
            <div class="section-header">STOCK-OUT RECORDS ({{ $stockOuts->count() }})</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;">ID</th>
                        <th style="width: 25%;">Item</th>
                        <th style="width: 20%;">Specs</th>
                        <th style="width: 8%;">Qty</th>
                        <th style="width: 15%;">Removed By</th>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 10%;">Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockOuts as $so)
                        <tr>
                            <td>{{ $so->stockout_id }}</td>
                            <td>{{ $so->item?->name ?? '—' }}</td>
                            <td>{{ $so->item?->specs ?? '—' }}</td>
                            <td style="text-align: center;">{{ $so->quantity }}</td>
                            <td>{{ $so->user?->name ?? '—' }}</td>
                            <td style="white-space:nowrap; font-size: 10px;">{{ \Carbon\Carbon::parse($so->stockout_date)->format('Y-m-d') }}</td>
                            <td style="font-size: 10px;">
                                @if($so->reference_type && $so->reference_id)
                                    {{ class_basename($so->reference_type) }} #{{ $so->reference_id }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="footer-info">
            <p>This report was generated on {{ now()->format('F d, Y \a\t H:i:s') }}</p>
            <p>SubWfour Inventory & Booking System - Activity Report</p>
        </div>
    </div>
</body>
</html>
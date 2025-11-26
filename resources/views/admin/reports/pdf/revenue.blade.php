<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #9333ea;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #9333ea;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #9333ea;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 9px;
            border: 1px solid #ddd;
        }
        td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f3f4f6;
            border-radius: 5px;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            color: #9333ea;
            font-size: 12px;
        }
        .summary p {
            margin: 5px 0;
            font-size: 10px;
        }
        .total-row {
            background-color: #9333ea;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Revenue Report</h1>
        <p>Date Range: {{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}</p>
        <p>Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Property Title</th>
                <th>City</th>
                <th>Owner</th>
                <th>Total Revenue</th>
                <th>Bookings</th>
                <th>Average Booking</th>
            </tr>
        </thead>
        <tbody>
            @foreach($properties as $item)
            <tr>
                <td>{{ Str::limit($item['property']->title, 25) }}</td>
                <td>{{ $item['property']->city }}</td>
                <td>{{ $item['property']->owner->name ?? 'N/A' }}</td>
                <td>{{ number_format($item['total_revenue'], 2) }}</td>
                <td>{{ $item['booking_count'] }}</td>
                <td>{{ number_format($item['average_booking'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3"><strong>TOTAL</strong></td>
                <td><strong>{{ number_format($totalRevenue, 2) }}</strong></td>
                <td><strong>{{ $totalBookings }}</strong></td>
                <td><strong>{{ number_format($overallAverage, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        <h3>Summary</h3>
        <p><strong>Total Properties:</strong> {{ $totalProperties }}</p>
        <p><strong>Total Revenue:</strong> {{ number_format($totalRevenue, 2) }}</p>
        <p><strong>Total Bookings:</strong> {{ $totalBookings }}</p>
        <p><strong>Average Booking Value:</strong> {{ number_format($overallAverage, 2) }}</p>
    </div>
</body>
</html>


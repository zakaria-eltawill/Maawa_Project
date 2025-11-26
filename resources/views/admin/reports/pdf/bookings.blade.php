<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Report</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Bookings Report</h1>
        <p>Date Range: {{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}</p>
        <p>Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Property</th>
                <th>City</th>
                <th>Tenant</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Nights</th>
                <th>Guests</th>
                <th>Total</th>
                <th>Status</th>
                <th>Payment</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr>
                <td>{{ substr($booking->id, 0, 8) }}...</td>
                <td>{{ Str::limit($booking->property->title ?? 'N/A', 20) }}</td>
                <td>{{ $booking->property->city ?? 'N/A' }}</td>
                <td>{{ $booking->tenant->name ?? 'N/A' }}</td>
                <td>{{ $booking->check_in->format('Y-m-d') }}</td>
                <td>{{ $booking->check_out->format('Y-m-d') }}</td>
                <td>{{ $booking->check_in->diffInDays($booking->check_out) }}</td>
                <td>{{ $booking->guests }}</td>
                <td>{{ number_format($booking->total, 2) }}</td>
                <td>{{ $booking->status }}</td>
                <td>{{ $booking->is_paid ? 'Paid' : 'Unpaid' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h3>Summary</h3>
        <p><strong>Total Bookings:</strong> {{ $totalBookings }}</p>
        <p><strong>Total Revenue:</strong> {{ number_format($totalRevenue, 2) }}</p>
    </div>
</body>
</html>


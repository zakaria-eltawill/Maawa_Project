<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $booking->status === 'CONFIRMED' ? __('mail.booking_confirmed_subject', ['id' => $booking->id]) : __('mail.booking_failed_subject', ['id' => $booking->id]) }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .booking-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6b7280;
        }
        .detail-value {
            color: #111827;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .status-confirmed {
            background: #d1fae5;
            color: #065f46;
        }
        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ app()->getLocale() === 'ar' ? 'مأوى' : 'Maawa' }}</h1>
    </div>
    
    <div class="content">
        <h2>{{ __('mail.dear_user', ['name' => $recipient->name]) }}</h2>
        
        @if($booking->status === 'CONFIRMED')
            <p>{{ __('mail.booking_confirmed_html', [
                'title' => $booking->property->title,
                'city' => $booking->property->city,
                'checkin' => $booking->check_in->format('Y-m-d'),
                'checkout' => $booking->check_out->format('Y-m-d'),
                'total' => number_format($booking->total, 2)
            ]) }}</p>
        @else
            <p>{{ __('mail.booking_failed_html', [
                'title' => $booking->property->title,
                'city' => $booking->property->city
            ]) }}</p>
        @endif

        <div class="booking-details">
            <div class="detail-row">
                <span class="detail-label">{{ __('mail.booking_id') }}:</span>
                <span class="detail-value">#{{ $booking->id }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __('mail.property_title') }}:</span>
                <span class="detail-value">{{ $booking->property->title }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __('mail.city') }}:</span>
                <span class="detail-value">{{ $booking->property->city }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __('mail.check_in') }}:</span>
                <span class="detail-value">{{ $booking->check_in->format('Y-m-d') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __('mail.check_out') }}:</span>
                <span class="detail-value">{{ $booking->check_out->format('Y-m-d') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __('mail.guests') }}:</span>
                <span class="detail-value">{{ $booking->guests }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __('mail.total') }}:</span>
                <span class="detail-value">{{ number_format($booking->total, 2) }} LYD</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __('mail.status') }}:</span>
                <span class="detail-value">
                    <span class="status-badge {{ $booking->status === 'CONFIRMED' ? 'status-confirmed' : 'status-failed' }}">
                        {{ $booking->status === 'CONFIRMED' ? __('mail.status_confirmed') : __('mail.status_failed') }}
                    </span>
                </span>
            </div>
        </div>

        <p>{{ __('mail.thank_you') }}</p>
    </div>
    
    <div class="footer">
        <p>{{ __('mail.thank_you') }}</p>
        <p>&copy; {{ date('Y') }} {{ app()->getLocale() === 'ar' ? 'مأوى' : 'Maawa' }}. {{ __('mail.all_rights_reserved') }}</p>
    </div>
</body>
</html>


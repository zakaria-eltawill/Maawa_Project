{{ __('mail.dear_user', ['name' => $recipient->name]) }}

@if($booking->status === 'CONFIRMED')
{{ __('mail.booking_confirmed_html', [
    'title' => $booking->property->title,
    'city' => $booking->property->city,
    'checkin' => $booking->check_in->format('Y-m-d'),
    'checkout' => $booking->check_out->format('Y-m-d'),
    'total' => number_format($booking->total, 2)
]) }}
@else
{{ __('mail.booking_failed_html', [
    'title' => $booking->property->title,
    'city' => $booking->property->city
]) }}
@endif

{{ __('mail.booking_id') }}: #{{ $booking->id }}
{{ __('mail.property_title') }}: {{ $booking->property->title }}
{{ __('mail.city') }}: {{ $booking->property->city }}
{{ __('mail.check_in') }}: {{ $booking->check_in->format('Y-m-d') }}
{{ __('mail.check_out') }}: {{ $booking->check_out->format('Y-m-d') }}
{{ __('mail.guests') }}: {{ $booking->guests }}
{{ __('mail.total') }}: {{ number_format($booking->total, 2) }} LYD
{{ __('mail.status') }}: {{ $booking->status === 'CONFIRMED' ? __('mail.status_confirmed') : __('mail.status_failed') }}

{{ __('mail.thank_you') }}

---
{{ app()->getLocale() === 'ar' ? 'مأوى' : 'Maawa' }}


@extends('layouts.admin')
@section('title', __('admin.bookings'))
@section('content')
<div class="p-4 bg-white rounded shadow-sm">
    <h2 class="text-2xl font-bold mb-4">{{ __('admin.bookings') }}</h2>
    
    @if($bookings->isEmpty())
        <p class="text-gray-600">{{ __('admin.no_data') }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Property</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tenant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guests</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($bookings as $booking)
                    <tr>
                        <td class="px-6 py-4 text-sm">{{ $booking->property->title ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $booking->tenant->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $booking->check_in->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 text-sm">{{ $booking->check_out->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 text-sm">{{ $booking->guests }}</td>
                        <td class="px-6 py-4 text-sm">{{ number_format($booking->total, 2) }} LYD</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($booking->status === 'CONFIRMED') bg-green-100 text-green-800
                                @elseif($booking->status === 'PENDING') bg-yellow-100 text-yellow-800
                                @elseif($booking->status === 'ACCEPTED') bg-blue-100 text-blue-800
                                @elseif($booking->status === 'REJECTED') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $booking->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection



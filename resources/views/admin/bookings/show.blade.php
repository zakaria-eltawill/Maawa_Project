@extends('layouts.admin')

@section('title', __('admin.booking_detail'))

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a 
        href="{{ route('admin.bookings.index') }}"
        class="inline-flex items-center text-purple-600 hover:text-purple-700 font-medium">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Bookings
    </a>
</div>

<!-- Booking Details Card -->
<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-3xl font-bold text-gray-900">Booking Details</h2>
        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
            @if($booking->status === 'CONFIRMED') bg-green-100 text-green-800
            @elseif($booking->status === 'PENDING') bg-yellow-100 text-yellow-800
            @elseif($booking->status === 'ACCEPTED') bg-blue-100 text-blue-800
            @elseif($booking->status === 'REJECTED') bg-red-100 text-red-800
            @elseif($booking->status === 'CANCELED') bg-gray-100 text-gray-800
            @elseif($booking->status === 'EXPIRED') bg-orange-100 text-orange-800
            @elseif($booking->status === 'COMPLETED') bg-purple-100 text-purple-800
            @else bg-gray-100 text-gray-800
            @endif">
            {{ $booking->status }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Property Information -->
        <div class="space-y-6">
            <h3 class="text-xl font-semibold text-gray-900 border-b-2 border-purple-200 pb-3">Property Information</h3>
            
            @php
                $photos = $booking->property->photos ?? [];
                $thumbnail = !empty($photos) 
                    ? (is_array($photos[0]) ? ($photos[0]['url'] ?? null) : $photos[0])
                    : null;
            @endphp
            
            @if($thumbnail)
                <div class="mb-4">
                    <img 
                        src="{{ $thumbnail }}" 
                        alt="{{ $booking->property->title }}"
                        class="w-full h-64 object-cover rounded-lg shadow-md border-2 border-purple-100"
                        onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'256\'%3E%3Crect fill=\'%23f3f4f6\' width=\'400\' height=\'256\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-family=\'sans-serif\' font-size=\'14\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                </div>
            @endif
            
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-600">Title</label>
                    <p class="text-lg text-gray-900">{{ $booking->property->title ?? 'N/A' }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-600">Type</label>
                    <p class="text-lg text-gray-900">{{ ucfirst($booking->property->type ?? 'N/A') }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-600">Location</label>
                    <p class="text-lg text-gray-900">{{ $booking->property->city ?? 'N/A' }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-600">Price per Night</label>
                    <p class="text-lg font-semibold text-purple-600">{{ number_format($booking->property->price ?? 0, 2) }} LYD</p>
                </div>
            </div>
        </div>

        <!-- Tenant & Booking Information -->
        <div class="space-y-6">
            <h3 class="text-xl font-semibold text-gray-900 border-b-2 border-purple-200 pb-3">Tenant Information</h3>
            
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-600">Name</label>
                    <p class="text-lg text-gray-900">{{ $booking->tenant->name ?? 'N/A' }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-600">Email</label>
                    <p class="text-lg text-gray-900">{{ $booking->tenant->email ?? 'N/A' }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-600">Phone Number</label>
                    <p class="text-lg text-gray-900">{{ $booking->tenant->phone_number ?? 'N/A' }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-600">Region</label>
                    <p class="text-lg text-gray-900">{{ $booking->tenant->region ?? 'N/A' }}</p>
                </div>
            </div>

            <h3 class="text-xl font-semibold text-gray-900 border-b-2 border-purple-200 pb-3 mt-8">Booking Information</h3>
            
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-600">Booking ID</label>
                    <p class="text-sm font-mono text-gray-900">{{ $booking->id }}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Check In</label>
                        <p class="text-lg text-gray-900">{{ $booking->check_in->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Check Out</label>
                        <p class="text-lg text-gray-900">{{ $booking->check_out->format('M d, Y') }}</p>
                    </div>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-600">Number of Guests</label>
                    <p class="text-lg text-gray-900">{{ $booking->guests }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-600">Total Nights</label>
                    <p class="text-lg text-gray-900">{{ $booking->check_in->diffInDays($booking->check_out) }}</p>
                </div>
                
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-4 rounded-lg border-2 border-purple-200">
                    <label class="text-sm font-medium text-gray-600">Total Price</label>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($booking->total, 2) }} LYD</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-600">Created At</label>
                    <p class="text-sm text-gray-900">{{ $booking->created_at->format('M d, Y - H:i A') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



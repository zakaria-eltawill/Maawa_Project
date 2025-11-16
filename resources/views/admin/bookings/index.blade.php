@extends('layouts.admin')

@section('title', __('admin.bookings'))

@section('content')
<!-- Filters -->
<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8 mb-8">
    <form method="GET" action="{{ route('admin.bookings.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.property') }}</label>
            <input 
                type="text" 
                name="property" 
                value="{{ request('property') }}"
                placeholder="Search property..."
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.tenant') }}</label>
            <input 
                type="text" 
                name="tenant" 
                value="{{ request('tenant') }}"
                placeholder="Search tenant..."
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Check In</label>
            <input 
                type="date" 
                name="check_in" 
                value="{{ request('check_in') }}"
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Check Out</label>
            <input 
                type="date" 
                name="check_out" 
                value="{{ request('check_out') }}"
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select 
                name="status"
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                <option value="">All Statuses</option>
                @foreach(['PENDING', 'ACCEPTED', 'CONFIRMED', 'REJECTED', 'CANCELED', 'EXPIRED', 'COMPLETED'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-5 flex gap-3">
            <button 
                type="submit"
                class="bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 px-8 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200">
                {{ __('admin.filter') }}
            </button>
            @if(request()->anyFilled(['property', 'tenant', 'check_in', 'check_out', 'status']))
                <a 
                    href="{{ route('admin.bookings.index') }}"
                    class="px-8 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition">
                    {{ __('admin.clear') }}
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Bookings Table -->
<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100">
    <div class="overflow-x-auto">
        <table class="min-w-[1200px] w-full" role="table">
            <thead class="bg-gradient-to-r from-purple-50 to-pink-50">
                <tr>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.photo') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.property') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.tenant') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Check In</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Check Out</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Guests</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.total') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($bookings as $booking)
                    <tr class="hover:bg-purple-50 transition">
                        <td class="px-8 py-5 whitespace-nowrap">
                            @php
                                $photos = $booking->property->photos ?? [];
                                $thumbnail = !empty($photos) 
                                    ? (is_array($photos[0]) ? ($photos[0]['url'] ?? null) : $photos[0])
                                    : null;
                            @endphp
                            @if($thumbnail)
                                <img 
                                    src="{{ $thumbnail }}" 
                                    alt="{{ $booking->property->title }}"
                                    class="w-20 h-20 object-cover rounded-lg shadow-md border-2 border-purple-100 hover:shadow-lg transition"
                                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'80\' height=\'80\'%3E%3Crect fill=\'%23f3f4f6\' width=\'80\' height=\'80\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-family=\'sans-serif\' font-size=\'10\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                            @else
                                <div class="w-20 h-20 bg-gradient-to-br from-purple-100 to-pink-100 rounded-lg flex items-center justify-center border-2 border-purple-200">
                                    <svg class="w-10 h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td class="px-8 py-5">
                            <div class="text-sm font-medium text-gray-900">{{ $booking->property->title ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $booking->property->city ?? '' }}</div>
                        </td>
                        <td class="px-8 py-5">
                            <div class="text-sm font-medium text-gray-900">{{ $booking->tenant->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $booking->tenant->phone_number ?? '' }}</div>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-600">
                            {{ $booking->check_in->format('M d, Y') }}
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-600">
                            {{ $booking->check_out->format('M d, Y') }}
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-600">
                            {{ $booking->guests }}
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm font-semibold text-gray-900">
                            {{ number_format($booking->total, 2) }} LYD
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
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
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm">
                            <a 
                                href="{{ route('admin.bookings.show', $booking->id) }}"
                                class="text-purple-600 hover:text-purple-700 font-medium">
                                {{ __('admin.view') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-8 py-16 text-center">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <p class="text-lg font-medium">{{ __('admin.no_data') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($bookings->hasPages())
        <div class="px-8 py-6 border-t border-gray-200">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection



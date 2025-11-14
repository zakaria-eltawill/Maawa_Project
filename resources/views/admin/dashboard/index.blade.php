@extends('layouts.admin')

@section('title', __('admin.dashboard'))

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Pending Proposals Card -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-6 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.cards.pending_proposals') }}</p>
                <p class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">{{ $stats['pending_proposals'] ?? 0 }}</p>
            </div>
            <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>
        <a href="{{ route('admin.proposals.index', ['status' => 'PENDING']) }}" class="mt-4 inline-flex items-center text-sm text-purple-600 hover:text-purple-700 font-medium">
            {{ __('admin.view') }}
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    </div>

    <!-- Today's Bookings Card -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-6 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.cards.todays_bookings') }}</p>
                <p class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">{{ $stats['todays_bookings'] ?? 0 }}</p>
            </div>
            <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="mt-4 inline-flex items-center text-sm text-purple-600 hover:text-purple-700 font-medium">
            {{ __('admin.view') }}
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    </div>

    <!-- Pending Payments Card -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-6 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.cards.pending_payments') }}</p>
                <p class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">{{ $stats['pending_payments'] ?? 0 }}</p>
            </div>
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
        <a href="{{ route('admin.bookings.index', ['status' => 'CONFIRMED']) }}" class="mt-4 inline-flex items-center text-sm text-purple-600 hover:text-purple-700 font-medium">
            {{ __('admin.view') }}
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Total Properties -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-purple-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.nav.properties') }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_properties'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Total Bookings -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-purple-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.nav.bookings') }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_bookings'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Total Users -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-purple-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.nav.users') }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_users'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Recent Proposals -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                {{ __('admin.nav.proposals') }}
            </h3>
            <a href="{{ route('admin.proposals.index') }}" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                {{ __('admin.view') }} →
            </a>
        </div>
        @if($recentProposals->count() > 0)
            <div class="space-y-3">
                @foreach($recentProposals as $proposal)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-purple-50 transition">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">
                                {{ $proposal->owner->name ?? 'N/A' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ ucfirst(strtolower($proposal->type)) }} - 
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $proposal->status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $proposal->status === 'APPROVED' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $proposal->status === 'REJECTED' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $proposal->status }}
                                </span>
                            </p>
                        </div>
                        <span class="text-xs text-gray-400">{{ $proposal->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 text-center py-4">{{ __('admin.no_data') }}</p>
        @endif
    </div>

    <!-- Recent Bookings -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                {{ __('admin.nav.bookings') }}
            </h3>
            <a href="{{ route('admin.bookings.index') }}" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                {{ __('admin.view') }} →
            </a>
        </div>
        @if($recentBookings->count() > 0)
            <div class="space-y-3">
                @foreach($recentBookings as $booking)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-pink-50 transition">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">
                                {{ $booking->tenant->name ?? 'N/A' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $booking->property->title ?? 'N/A' }} - 
                                <span class="font-medium">{{ number_format($booking->total, 2) }} LYD</span>
                            </p>
                        </div>
                        <span class="text-xs text-gray-400">{{ $booking->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 text-center py-4">{{ __('admin.no_data') }}</p>
        @endif
    </div>
</div>

<!-- Additional Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white/90 backdrop-blur-sm rounded-xl shadow-lg border border-purple-100 p-4 text-center">
        <p class="text-xs text-gray-600 mb-1">{{ __('admin.nav.users') }} ({{ __('admin.roles.owner') }})</p>
        <p class="text-2xl font-bold text-purple-600">{{ $stats['total_owners'] ?? 0 }}</p>
    </div>
    <div class="bg-white/90 backdrop-blur-sm rounded-xl shadow-lg border border-purple-100 p-4 text-center">
        <p class="text-xs text-gray-600 mb-1">{{ __('admin.nav.users') }} ({{ __('admin.roles.tenant') }})</p>
        <p class="text-2xl font-bold text-pink-600">{{ $stats['total_tenants'] ?? 0 }}</p>
    </div>
    <div class="bg-white/90 backdrop-blur-sm rounded-xl shadow-lg border border-purple-100 p-4 text-center">
        <p class="text-xs text-gray-600 mb-1">{{ __('admin.statuses.APPROVED') }} {{ __('admin.nav.proposals') }}</p>
        <p class="text-2xl font-bold text-green-600">{{ $stats['approved_proposals'] ?? 0 }}</p>
    </div>
    <div class="bg-white/90 backdrop-blur-sm rounded-xl shadow-lg border border-purple-100 p-4 text-center">
        <p class="text-xs text-gray-600 mb-1">{{ __('admin.statuses.COMPLETED') }} {{ __('admin.nav.bookings') }}</p>
        <p class="text-2xl font-bold text-indigo-600">{{ $stats['completed_bookings'] ?? 0 }}</p>
    </div>
</div>

<!-- Welcome Card -->
<div class="bg-gradient-to-r from-purple-600 via-pink-500 to-purple-600 rounded-2xl shadow-2xl p-8 text-white relative overflow-hidden">
    <!-- Decorative Elements -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-24 -mb-24"></div>
    
    <div class="relative z-10">
        <div class="flex items-center space-x-4 mb-4">
            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold mb-2">{{ __('admin.welcome') }}</h3>
                <p class="text-white/90 mb-4">{{ __('auth.welcome_message') }}</p>
                <div class="flex gap-4 text-sm">
                    <div>
                        <span class="font-semibold">{{ $todayProposals }}</span> {{ __('admin.nav.proposals') }} {{ __('admin.today') }}
                    </div>
                    <div>
                        <span class="font-semibold">{{ $todayBookings }}</span> {{ __('admin.nav.bookings') }} {{ __('admin.today') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

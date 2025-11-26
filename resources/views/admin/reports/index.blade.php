@extends('layouts.admin')

@section('title', __('admin.reports'))

@section('content')
@php($isRtl = app()->getLocale() === 'ar')
<!-- Report Generation Form -->
<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8 mb-8">
    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
        <svg class="w-5 h-5 {{ $isRtl ? 'ml-2' : 'mr-2' }} text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        {{ __('admin.generate_report') }}
    </h3>
    <form method="POST" action="{{ route('admin.reports.export') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.report_type') }}</label>
            <select 
                name="type" 
                required
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                <option value="bookings">{{ __('admin.report_bookings') }}</option>
                <option value="occupancy">{{ __('admin.report_occupancy') }}</option>
                <option value="revenue">{{ __('admin.report_revenue') }}</option>
            </select>
        </div>
        <input type="hidden" name="format" value="csv">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.from_date') }}</label>
            <input 
                type="date" 
                name="from" 
                value="{{ old('from', request('from')) }}"
                required
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.to_date') }}</label>
            <input 
                type="date" 
                name="to" 
                value="{{ old('to', request('to')) }}"
                required
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
        </div>
        <div class="flex items-end">
            <button 
                type="submit"
                class="group relative w-full h-[48px] bg-gradient-to-r from-purple-600 via-purple-500 to-pink-600 text-white px-6 rounded-xl font-semibold shadow-lg hover:shadow-2xl hover:shadow-purple-500/50 transform hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-2 overflow-hidden">
                <!-- Animated background gradient -->
                <div class="absolute inset-0 bg-gradient-to-r from-pink-600 via-purple-500 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                
                <!-- Shine effect on hover -->
                <div class="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
                
                <!-- Content -->
                <span class="relative z-10 flex items-center gap-2">
                    <svg class="w-5 h-5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="group-hover:translate-x-0.5 transition-transform duration-300">{{ __('admin.generate_report') }}</span>
                </span>
            </button>
        </div>
    </form>
</div>

<!-- Report Types Info Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
    <!-- Bookings Report Card -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg {{ $isRtl ? 'ml-4' : 'mr-4' }}">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h4 class="text-lg font-bold text-gray-800">{{ __('admin.report_bookings') }}</h4>
        </div>
        <p class="text-sm text-gray-600 mb-2">{{ __('admin.report_bookings_desc') }}</p>
        <ul class="text-xs text-gray-500 space-y-1">
            <li>• {{ __('admin.city') }}</li>
            <li>• {{ __('admin.date_range') }}</li>
            <li>• {{ __('admin.status') }}</li>
        </ul>
    </div>

    <!-- Occupancy Report Card -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg {{ $isRtl ? 'ml-4' : 'mr-4' }}">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
            </div>
            <h4 class="text-lg font-bold text-gray-800">{{ __('admin.report_occupancy') }}</h4>
        </div>
        <p class="text-sm text-gray-600 mb-2">{{ __('admin.report_occupancy_desc') }}</p>
        <ul class="text-xs text-gray-500 space-y-1">
            <li>• {{ __('admin.nights_booked') }}</li>
            <li>• {{ __('admin.per_property') }}</li>
            <li>• {{ __('admin.date_range') }}</li>
        </ul>
    </div>

    <!-- Revenue Report Card -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg {{ $isRtl ? 'ml-4' : 'mr-4' }}">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h4 class="text-lg font-bold text-gray-800">{{ __('admin.report_revenue') }}</h4>
        </div>
        <p class="text-sm text-gray-600 mb-2">{{ __('admin.report_revenue_desc') }}</p>
        <ul class="text-xs text-gray-500 space-y-1">
            <li>• {{ __('admin.payments') }}</li>
            <li>• {{ __('admin.revenue_summary') }}</li>
            <li>• {{ __('admin.date_range') }}</li>
        </ul>
    </div>
</div>

<!-- Recent Exports -->
<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
        <svg class="w-5 h-5 {{ $isRtl ? 'ml-2' : 'mr-2' }} text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        {{ __('admin.recent_exports') }}
    </h3>
    
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($exports->isEmpty())
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-sm text-gray-500">{{ __('admin.no_exports_yet') }}</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.type') }}</th>
                        <th class="px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.format') }}</th>
                        <th class="px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.date_range') }}</th>
                        <th class="px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.status') }}</th>
                        <th class="px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.created_at') }}</th>
                        <th class="px-6 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($exports as $export)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ __('admin.report_type_' . $export->type) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 uppercase">
                            {{ $export->filters['format'] ?? 'csv' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($export->filters['from'])->format('Y-m-d') }} {{ __('admin.to') }} {{ \Carbon\Carbon::parse($export->filters['to'])->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($export->status === 'READY')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ __('admin.statuses.READY') }}
                                </span>
                            @elseif($export->status === 'QUEUED')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    {{ __('admin.processing') }}
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    {{ __('admin.statuses.FAILED') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $export->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center gap-2 {{ $isRtl ? 'flex-row-reverse' : '' }}">
                                @if($export->status === 'READY')
                                    <a href="{{ route('admin.reports.download', $export->id) }}" 
                                       class="text-purple-600 hover:text-purple-900 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        {{ __('admin.download') }}
                                    </a>
                                @elseif($export->status === 'FAILED')
                                    <span class="text-red-600 text-xs" title="{{ $export->error_message }}">{{ \Illuminate\Support\Str::limit($export->error_message, 30) }}</span>
                                @else
                                    <span class="text-gray-400">{{ __('admin.processing') }}...</span>
                                @endif
                                
                                @if($export->status !== 'QUEUED')
                                    <form action="{{ route('admin.reports.destroy', $export->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('admin.confirm_delete_export') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 flex items-center gap-1" title="{{ __('admin.delete') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

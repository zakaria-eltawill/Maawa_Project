@extends('layouts.admin')

@section('title', __('admin.reports'))

@section('content')
@php($isRtl = app()->getLocale() === 'ar')
<!-- Report Generation Form -->
<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8 mb-8">
    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        {{ __('admin.generate_report') }}
    </h3>
    <form method="POST" action="{{ route('admin.reports.export') }}" class="grid grid-cols-1 md:grid-cols-5 gap-6">
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
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
            <select 
                name="format" 
                required
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                <option value="csv">CSV</option>
                <option value="pdf">PDF</option>
            </select>
        </div>
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
                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 px-6 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Generate Report
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
        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Range</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($exports as $export)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 capitalize">
                            {{ $export->type }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 uppercase">
                            {{ $export->filters['format'] ?? 'csv' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($export->filters['from'])->format('Y-m-d') }} to {{ \Carbon\Carbon::parse($export->filters['to'])->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($export->status === 'READY')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Ready
                                </span>
                            @elseif($export->status === 'QUEUED')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Processing
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Failed
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $export->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($export->status === 'READY')
                                <a href="{{ route('admin.reports.download', $export->id) }}" 
                                   class="text-purple-600 hover:text-purple-900 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download
                                </a>
                            @elseif($export->status === 'FAILED')
                                <span class="text-red-600 text-xs" title="{{ $export->error_message }}">{{ \Illuminate\Support\Str::limit($export->error_message, 30) }}</span>
                            @else
                                <span class="text-gray-400">Processing...</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

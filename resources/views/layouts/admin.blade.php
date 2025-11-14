<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <title>{{ app()->getLocale() === 'ar' ? 'مأوى' : 'Maawa' }} - @yield('title', __('admin.dashboard'))</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 text-gray-900" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
@php($isRtl = app()->getLocale() === 'ar')
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-white/95 backdrop-blur-sm border-e border-purple-100 min-h-screen shadow-xl flex flex-col">
        <!-- Logo -->
        <div class="p-6 border-b border-purple-100">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-pink-500 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <h1 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    {{ app()->getLocale() === 'ar' ? 'مأوى' : 'Maawa' }}
                </h1>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-1" role="navigation" aria-label="{{ __('admin.nav.dashboard') }}">
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center gap-3 px-5 py-3.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                {{ __('admin.nav.dashboard') }}
            </a>
            <a href="{{ route('admin.proposals.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.proposals.*') ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                {{ __('admin.nav.proposals') }}
            </a>
            <a href="{{ route('admin.properties.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.properties.*') ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                {{ __('admin.nav.properties') }}
            </a>
            <a href="{{ route('admin.bookings.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.bookings.*') ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                {{ __('admin.nav.bookings') }}
            </a>
            <a href="{{ route('admin.users.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                {{ __('admin.nav.users') }}
            </a>
            <a href="{{ route('admin.audit.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.audit.*') ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                {{ __('admin.nav.audit') }}
            </a>
            <a href="{{ route('admin.reports.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                {{ __('admin.nav.reports') }}
            </a>
            <a href="{{ route('admin.notifications.index') }}" 
               class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.notifications.*') ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                <span class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span>{{ __('admin.nav.notifications') }}</span>
                </span>
                @if(($adminUnreadNotificationsCount ?? 0) > 0)
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-600 shadow-sm">
                        {{ $adminUnreadNotificationsCount }}
                    </span>
                @endif
            </a>
        </nav>

        <!-- Sidebar User Card -->
        <div class="p-5 border-t border-purple-100">
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-100 rounded-2xl p-4 shadow-inner flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-pink-500 rounded-full flex items-center justify-center text-white font-semibold">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <!-- Top Header -->
        <header class="flex justify-between items-center mb-8 pb-6 border-b border-purple-200">
            <h2 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">@yield('title', __('admin.dashboard'))</h2>
            <div class="flex items-center gap-4">
                <!-- Language Switcher -->
                <div class="inline-flex items-center bg-white rounded-full p-1 shadow-md border border-purple-100">
                    <a href="{{ route('locale.switch', 'en') }}" 
                       class="px-4 py-1.5 text-sm font-medium rounded-full transition-all duration-200 {{ app()->getLocale() === 'en' ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-md' : 'text-gray-600 hover:text-purple-600' }}">
                        {{ __('admin.english') }}
                    </a>
                    <a href="{{ route('locale.switch', 'ar') }}" 
                       class="px-4 py-1.5 text-sm font-medium rounded-full transition-all duration-200 {{ app()->getLocale() === 'ar' ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-md' : 'text-gray-600 hover:text-purple-600' }}">
                        {{ __('admin.arabic') }}
                    </a>
                </div>
                
                <!-- Logout Button -->
                <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-md border border-purple-100 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
                        </svg>
                        {{ __('admin.logout') }}
                    </button>
                </form>
            </div>
        </header>

        <!-- Flash Messages / Toasts -->
        @if(session('status'))
            <div class="p-5 mb-8 bg-green-50 text-green-800 border-l-4 border-green-500 rounded-r-lg shadow-sm" 
                 role="alert" 
                 aria-live="polite"
                 x-data="{ show: true }"
                 x-show="show"
                 x-transition>
                <div class="flex justify-between items-center">
                    <span>{{ session('status') }}</span>
                    <button @click="show = false" 
                            class="text-green-600 hover:text-green-800"
                            aria-label="{{ __('admin.cancel') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-5 mb-8 bg-red-50 text-red-800 border-l-4 border-red-500 rounded-r-lg shadow-sm" 
                 role="alert" 
                 aria-live="polite"
                 x-data="{ show: true }"
                 x-show="show"
                 x-transition>
                <div class="flex justify-between items-center">
                    <span>{{ session('error') }}</span>
                    <button @click="show = false" 
                            class="text-red-600 hover:text-red-800"
                            aria-label="{{ __('admin.cancel') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </main>
</div>

<!-- Alpine.js for interactivity -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('scripts')
</body>
</html>

@extends('layouts.plain')

@section('title', __('auth.login_title'))

@section('content')
<div class="min-h-screen flex">
    <!-- Left Panel - Welcome Section -->
    <div class="hidden lg:flex lg:w-2/3 bg-gradient-to-br from-purple-600 via-pink-500 to-purple-800 relative overflow-hidden">
        <!-- Decorative Shapes -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-20 -left-20 w-96 h-96 bg-gradient-to-br from-orange-400 to-yellow-300 rounded-full opacity-20 blur-3xl"></div>
            <div class="absolute top-40 right-10 w-72 h-72 bg-gradient-to-br from-pink-400 to-red-300 rounded-full opacity-30 blur-2xl"></div>
            <div class="absolute bottom-20 left-10 w-80 h-80 bg-gradient-to-br from-yellow-400 to-orange-300 rounded-full opacity-25 blur-3xl"></div>
            <div class="absolute bottom-40 right-20 w-64 h-64 bg-gradient-to-br from-pink-500 to-purple-400 rounded-full opacity-20 blur-2xl"></div>
        </div>
        
        <!-- Content -->
        <div class="relative z-10 flex flex-col justify-center px-12 text-white">
            <div class="max-w-lg">
                <h1 class="text-5xl font-bold mb-6 leading-tight">
                    {{ __('auth.login_heading') }}
                </h1>
                <p class="text-lg text-white/90 leading-relaxed mb-8">
                    {{ __('auth.welcome_message') }}
                </p>
                
                <!-- Features List -->
                <div class="space-y-4">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-white/90">{{ __('auth.feature_1') }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-white/90">{{ __('auth.feature_2') }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-white/90">{{ __('auth.feature_3') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel - Login Form -->
    <div class="w-full lg:w-1/3 flex flex-col justify-center bg-white px-8 py-12">
        <!-- Language Switcher - Top Right -->
        <div class="flex justify-end mb-8">
            <div class="inline-flex items-center bg-gray-100 rounded-full p-1">
                <a href="{{ route('locale.switch', 'en') }}" 
                   class="px-4 py-1.5 text-sm font-medium rounded-full transition-all duration-200 {{ app()->getLocale() === 'en' ? 'bg-purple-600 text-white shadow-md' : 'text-gray-600 hover:text-purple-600' }}">
                    {{ __('admin.english') }}
                </a>
                <a href="{{ route('locale.switch', 'ar') }}" 
                   class="px-4 py-1.5 text-sm font-medium rounded-full transition-all duration-200 {{ app()->getLocale() === 'ar' ? 'bg-purple-600 text-white shadow-md' : 'text-gray-600 hover:text-purple-600' }}">
                    {{ __('admin.arabic') }}
                </a>
            </div>
        </div>

        <!-- Login Form -->
        <div class="max-w-md mx-auto w-full">
            <h2 class="text-2xl font-bold text-purple-600 mb-8 text-center">{{ __('auth.login_title') }}</h2>

            <!-- Error Message -->
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg" role="alert" aria-live="polite">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-6">
                @csrf

                <!-- Email Field -->
                <div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <input 
                            id="email"
                            name="email" 
                            type="email" 
                            value="{{ old('email') }}"
                            class="w-full pl-12 pr-4 py-3.5 border-2 border-purple-200 rounded-xl bg-purple-50/50 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all duration-200 text-gray-800 placeholder-gray-400" 
                            required 
                            autofocus
                            autocomplete="email"
                            placeholder="{{ __('auth.email') }}"
                            aria-required="true"
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input 
                            id="password"
                            name="password" 
                            type="password" 
                            class="w-full pl-12 pr-4 py-3.5 border-2 border-purple-200 rounded-xl bg-purple-50/50 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all duration-200 text-gray-800 placeholder-gray-400" 
                            required
                            autocomplete="current-password"
                            placeholder="{{ __('auth.password') }}"
                            aria-required="true"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-pink-500 to-purple-600 text-white py-3.5 px-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 uppercase tracking-wide">
                    {{ __('auth.login_button') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

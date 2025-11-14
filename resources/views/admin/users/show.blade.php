@extends('layouts.admin')

@section('title', __('admin.user_detail'))

@section('content')
<div class="grid md:grid-cols-2 gap-6">
    <!-- User Details -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-6">
        <div class="flex items-center mb-6">
            <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center text-white font-bold text-2xl mr-4">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h2>
                <p class="text-sm text-gray-600">{{ $user->email }}</p>
            </div>
        </div>
        
        <div class="space-y-4">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.id') }}</p>
                <p class="text-sm text-gray-900 font-mono">{{ $user->id }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.role') }}</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : '' }}
                        {{ $user->role === 'owner' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $user->role === 'tenant' ? 'bg-green-100 text-green-800' : '' }}">
                        {{ __('admin.roles.' . $user->role) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.status') }}</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $user->is_active ? __('admin.active') : __('admin.inactive') }}
                    </span>
                </div>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.created_at') }}</p>
                <p class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y H:i') }}</p>
            </div>
            @if($user->email_verified_at)
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.email_verified_at') }}</p>
                <p class="text-sm text-gray-900">{{ $user->email_verified_at->format('M d, Y H:i') }}</p>
            </div>
            @endif
        </div>

        @if($user->id !== auth()->id())
        <div class="mt-6 pt-6 border-t border-gray-200">
            <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST">
                @csrf
                <button 
                    type="submit"
                    class="w-full {{ $user->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white py-2 px-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200"
                    onclick="return confirm('{{ $user->is_active ? __('admin.confirm_deactivate') : __('admin.confirm_activate') }}')">
                    {{ $user->is_active ? __('admin.deactivate') : __('admin.activate') }}
                </button>
            </form>
        </div>
        @endif
    </div>

    <!-- Statistics -->
    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white/90 backdrop-blur-sm rounded-xl shadow-lg border border-purple-100 p-4 text-center">
                <p class="text-xs text-gray-600 mb-1">{{ __('admin.nav.properties') }}</p>
                <p class="text-2xl font-bold text-purple-600">{{ $user->properties->count() }}</p>
            </div>
            <div class="bg-white/90 backdrop-blur-sm rounded-xl shadow-lg border border-purple-100 p-4 text-center">
                <p class="text-xs text-gray-600 mb-1">{{ __('admin.nav.bookings') }}</p>
                <p class="text-2xl font-bold text-pink-600">{{ $user->bookings->count() }}</p>
            </div>
            <div class="bg-white/90 backdrop-blur-sm rounded-xl shadow-lg border border-purple-100 p-4 text-center">
                <p class="text-xs text-gray-600 mb-1">{{ __('admin.nav.proposals') }}</p>
                <p class="text-2xl font-bold text-indigo-600">{{ $user->proposals->count() }}</p>
            </div>
            <div class="bg-white/90 backdrop-blur-sm rounded-xl shadow-lg border border-purple-100 p-4 text-center">
                <p class="text-xs text-gray-600 mb-1">{{ __('admin.rating') }}</p>
                <p class="text-2xl font-bold text-green-600">{{ $user->reviews->count() }}</p>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('admin.recent_activity') }}</h3>
            <div class="space-y-3 text-sm text-gray-600">
                <p>{{ __('admin.created_at') }}: {{ $user->created_at->diffForHumans() }}</p>
                <p>{{ __('admin.updated_at') }}: {{ $user->updated_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

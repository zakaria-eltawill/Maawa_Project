@extends('layouts.admin')

@section('title', __('admin.property_detail'))

@section('content')
<!-- Photos Gallery -->
@php
    $photos = $property->photos ?? [];
@endphp
@if(!empty($photos))
<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8 mb-8">
    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
        <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        {{ __('admin.photos') }}
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($photos as $index => $photo)
            @php
                $photoUrl = is_array($photo) ? ($photo['url'] ?? null) : $photo;
            @endphp
            @if($photoUrl)
                <div class="relative group cursor-pointer" 
                     @click="$dispatch('open-image-modal', { url: {{ json_encode($photoUrl) }}, title: {{ json_encode($property->title) }} })">
                    <img 
                        src="{{ $photoUrl }}" 
                        alt="{{ $property->title }} - Photo {{ $index + 1 }}"
                        class="w-full h-32 object-cover rounded-xl shadow-md border-2 border-purple-100 hover:shadow-xl hover:scale-105 transition-all duration-300"
                        onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'128\'%3E%3Crect fill=\'%23f3f4f6\' width=\'200\' height=\'128\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-family=\'sans-serif\' font-size=\'12\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 rounded-xl transition-all duration-300 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                        </svg>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
@endif

<div class="grid md:grid-cols-2 gap-6">
    <!-- Property Details -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">{{ __('admin.property_detail') }}</h2>
        
        <div class="space-y-4">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.id') }}</p>
                <p class="text-sm text-gray-900 font-mono">{{ $property->id }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.title') }}</p>
                <p class="text-lg font-semibold text-gray-900">{{ $property->title }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.description') }}</p>
                <p class="text-sm text-gray-900">{{ $property->description }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.city') }}</p>
                    <p class="text-sm text-gray-900">{{ $property->city }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.type') }}</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                        {{ __('admin.property_types.' . $property->type) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.price') }}</p>
                    <p class="text-lg font-bold text-purple-600">{{ number_format($property->price, 2) }} LYD</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.version') }}</p>
                    <p class="text-sm text-gray-900">{{ $property->version }}</p>
                </div>
            </div>
            @if($property->location_url || ($property->location_lat && $property->location_lng))
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.location') }}</p>
                @if($property->location_url)
                    <a 
                        href="{{ $property->location_url }}"
                        target="_blank"
                        class="inline-flex items-center text-sm text-purple-600 hover:text-purple-700 font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ __('admin.view_on_map') }}
                    </a>
                @endif
                @if($property->location_lat && $property->location_lng)
                    <p class="text-sm text-gray-900 mt-2">
                        {{ number_format($property->location_lat, 6) }}, {{ number_format($property->location_lng, 6) }}
                    </p>
                @endif
            </div>
            @endif
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.owner') }}</p>
                <a href="{{ route('admin.users.show', $property->owner_id) }}" class="text-sm text-purple-600 hover:text-purple-700">
                    {{ $property->owner->name ?? 'N/A' }}
                </a>
            </div>
            <div class="pt-4 mt-4 border-t border-gray-200 space-y-3">
                <a 
                    href="{{ route('admin.properties.edit', $property->id) }}"
                    class="w-full inline-flex justify-center items-center bg-gradient-to-r from-purple-600 to-pink-600 text-white py-2.5 px-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-2 14h2m-7-7h14" />
                    </svg>
                    {{ __('admin.edit_property') }}
                </a>
                <form 
                    action="{{ route('admin.properties.destroy', $property->id) }}" 
                    method="POST" 
                    onsubmit="return confirm('{{ __('admin.confirm_delete_property') }}')">
                    @csrf
                    @method('DELETE')
                    <button 
                        type="submit"
                        class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        {{ __('admin.delete') }} {{ __('admin.properties') }}
                    </button>
                </form>
            </div>
            @if($property->amenities)
            <div>
                <p class="text-sm font-medium text-gray-600 mb-2">{{ __('admin.amenities') }}</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($property->amenities as $amenity)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $amenity }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Additional Info -->
    <div class="space-y-6">
        <!-- Owner Info -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
            <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('admin.owner') }}</h3>
            <div class="space-y-2">
                <p class="text-sm text-gray-600">{{ __('admin.name') }}</p>
                <p class="text-sm font-medium text-gray-900">{{ $property->owner->name ?? 'N/A' }}</p>
                <p class="text-sm text-gray-600">{{ __('admin.email') }}</p>
                <p class="text-sm text-gray-900">{{ $property->owner->email ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Statistics -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
            <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('admin.statistics') }}</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">{{ __('admin.nav.bookings') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $property->bookings->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">{{ __('admin.reviews_count') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $property->reviews->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">{{ __('admin.created_at') }}</span>
                    <span class="text-sm text-gray-900">{{ $property->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div 
     x-data="{ 
         show: false, 
         imageUrl: '', 
         title: '',
         open(imageUrl, title) {
             this.imageUrl = imageUrl;
             this.title = title;
             this.show = true;
             document.body.style.overflow = 'hidden';
         },
         close() {
             this.show = false;
             document.body.style.overflow = '';
         }
     }"
     @open-image-modal.window="open($event.detail.url, $event.detail.title)"
     @keydown.escape.window="close()"
     x-show="show"
     x-cloak
     class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4"
     @click.self="close()"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="max-w-4xl w-full relative" @click.stop>
        <button 
            @click="close()" 
            class="absolute -top-12 right-0 text-white hover:text-gray-300 z-10 bg-black/50 rounded-full p-2 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <img x-bind:src="imageUrl" x-bind:alt="title" class="max-w-full max-h-[90vh] mx-auto rounded-lg shadow-2xl">
        <p x-text="title" class="text-white text-center mt-4 text-lg"></p>
    </div>
</div>
@endsection

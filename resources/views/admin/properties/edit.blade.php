@extends('layouts.admin')

@section('title', __('admin.edit_property'))

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
            {{ __('admin.edit_property') }}
        </h2>
        <a href="{{ route('admin.properties.show', $property->id) }}" class="text-sm text-purple-600 hover:text-purple-700 font-semibold">
            &larr; {{ __('admin.back_to_detail') }}
        </a>
    </div>

    <form method="POST" action="{{ route('admin.properties.update', $property->id) }}" class="space-y-8" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4zM3 7v10l9 4 9-4V7m-9 4v10" />
                </svg>
                {{ __('admin.property_information') }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.title') }}</label>
                    <input type="text" name="title" value="{{ old('title', $property->title) }}" class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition" required>
                    @error('title')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.city') }}</label>
                    <input type="text" name="city" value="{{ old('city', $property->city) }}" class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition" required>
                    @error('city')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.type') }}</label>
                    <select name="type" class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition" required>
                        @foreach(['apartment', 'villa', 'chalet'] as $type)
                            <option value="{{ $type }}" {{ old('type', $property->type) === $type ? 'selected' : '' }}>
                                {{ __('admin.property_types.' . $type) }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.price') }}</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $property->price) }}" class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition" required>
                    @error('price')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.owner') }}</label>
                    <select name="owner_id" class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition" required>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" {{ old('owner_id', $property->owner_id) === $owner->id ? 'selected' : '' }}>
                                {{ $owner->name }} &mdash; {{ $owner->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('owner_id')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.description') }}</label>
                    <textarea name="description" rows="4" class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">{{ old('description', $property->description) }}</textarea>
                    @error('description')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5s-3 1.343-3 3 1.343 3 3 3z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.5-7.5 10.5-7.5 10.5S4.5 18 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
                {{ __('admin.location') }}
            </h3>
            <p class="text-sm text-gray-500 mb-4">{{ __('admin.location_url_hint') }}</p>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.location_url') }}</label>
            <input 
                type="url" 
                name="location_url" 
                value="{{ old('location_url', $property->location_url) }}"
                placeholder="https://maps.google.com/..."
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
            @error('location_url')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.latitude') }} <span class="text-xs text-gray-400">({{ __('admin.optional') }})</span></label>
                    <input type="number" step="0.000001" name="location_lat" value="{{ old('location_lat', $property->location_lat) }}" class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                    @error('location_lat')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.longitude') }} <span class="text-xs text-gray-400">({{ __('admin.optional') }})</span></label>
                    <input type="number" step="0.000001" name="location_lng" value="{{ old('location_lng', $property->location_lng) }}" class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                    @error('location_lng')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h14a2 2 0 012 2v14l-7-3-7 3V5z" />
                </svg>
                {{ __('admin.amenities_photos') }}
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center justify-between">
                        <span>{{ __('admin.amenities') }}</span>
                        <span class="text-xs text-gray-400">{{ __('admin.amenities_hint') }}</span>
                    </label>
                    <textarea name="amenities" rows="4" class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition" placeholder="wifi, parking, balcony">{{ old('amenities', implode("\n", $property->amenities ?? [])) }}</textarea>
                    @error('amenities')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                </div>
                <div>
                    <p class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.current_photos') }}</p>
                    <div class="grid grid-cols-2 gap-4">
                        @forelse($property->photos ?? [] as $photo)
                            @php
                                $photoUrl = is_array($photo) ? ($photo['url'] ?? null) : $photo;
                            @endphp
                            <div class="bg-white border border-purple-100 rounded-xl p-3 shadow-sm flex flex-col gap-3">
                                <img src="{{ $photoUrl }}" alt="{{ $property->title }}" class="w-full h-28 object-cover rounded-lg border border-purple-100" onerror="this.style.display='none'">
                                <label class="inline-flex items-center gap-2 text-sm text-red-600">
                                    <input type="checkbox" name="remove_photos[]" value="{{ $photoUrl }}" class="rounded border-red-300 text-red-600 focus:ring-red-500">
                                    {{ __('admin.remove_photo') }}
                                </label>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('admin.no_photos_uploaded') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.upload_new_photos') }}</label>
                <input type="file" name="photos[]" accept="image/*" multiple class="w-full px-5 py-3 border-2 border-dashed border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition bg-purple-50/50">
                <p class="text-xs text-gray-400 mt-2">{{ __('admin.photos_upload_hint') }}</p>
                @error('photos')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                @error('photos.*')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.properties.show', $property->id) }}" class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition">
                {{ __('admin.cancel') }}
            </a>
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200">
                {{ __('admin.update_property') }}
            </button>
        </div>
    </form>
</div>
@endsection

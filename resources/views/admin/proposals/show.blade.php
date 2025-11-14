@extends('layouts.admin')

@section('title', __('admin.proposal_detail'))

@section('content')
<div class="grid md:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="md:col-span-2 space-y-6">
        <!-- Proposal Info Card -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">{{ __('admin.proposal_detail') }}</h2>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.id') }}</p>
                    <p class="text-sm text-gray-900 font-mono">{{ $proposal->id }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.owner') }}</p>
                    <p class="text-sm text-gray-900">{{ $proposal->owner->name ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500">{{ $proposal->owner->email ?? '' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.type') }}</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $proposal->type === 'ADD' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $proposal->type === 'EDIT' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $proposal->type === 'DELETE' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ __('admin.types.' . $proposal->type) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.status') }}</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $proposal->status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $proposal->status === 'APPROVED' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $proposal->status === 'REJECTED' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ __('admin.statuses.' . $proposal->status) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.submitted_at') }}</p>
                    <p class="text-sm text-gray-900">{{ $proposal->created_at->format('M d, Y H:i') }}</p>
                </div>
                @if($proposal->property)
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.property_detail') }}</p>
                    <a href="{{ route('admin.properties.show', $proposal->property_id) }}" class="text-sm text-purple-600 hover:text-purple-700">
                        {{ $proposal->property->title ?? 'N/A' }}
                    </a>
                </div>
                @endif
            </div>

            @if($proposal->notes)
            <div class="mb-6">
                <p class="text-sm font-medium text-gray-600 mb-2">{{ __('admin.notes') }}</p>
                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $proposal->notes }}</p>
            </div>
            @endif

            @if($proposal->reason)
            <div class="mb-6">
                <p class="text-sm font-medium text-gray-600 mb-2">{{ __('admin.reason') }}</p>
                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $proposal->reason }}</p>
            </div>
            @endif
        </div>

        <!-- Payload/Diff Card -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('admin.diff') }}</h2>

            @php
                $payload = $proposal->payload ?? [];
                $hasStructuredFields = !empty(array_intersect(array_keys($payload), [
                    'title', 'description', 'city', 'type', 'price', 'version', 'location', 'amenities', 'photos'
                ]));
            @endphp

            @if(in_array($proposal->type, ['ADD', 'EDIT']) && $hasStructuredFields)
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4zM3 7v10l9 4 9-4V7m-9 4v10" />
                            </svg>
                            {{ __('admin.property_detail') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if(isset($payload['title']))
                                <div>
                                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.title') }}</p>
                                    <p class="text-base font-semibold text-gray-900">{{ $payload['title'] }}</p>
                                </div>
                            @endif
                            @if(isset($payload['city']))
                                <div>
                                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.city') }}</p>
                                    <p class="text-base text-gray-900">{{ $payload['city'] }}</p>
                                </div>
                            @endif
                            @if(isset($payload['type']))
                                <div>
                                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.type') }}</p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                        {{ __('admin.property_types.' . $payload['type']) }}
                                    </span>
                                </div>
                            @endif
                            @if(isset($payload['price']))
                                <div>
                                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.price') }}</p>
                                    <p class="text-base font-semibold text-gray-900">{{ number_format($payload['price'], 2) }} LYD</p>
                                </div>
                            @endif
                            @if(isset($payload['version']))
                                <div>
                                    <p class="text-sm font-medium text-gray-600 mb-1">{{ __('admin.version') }}</p>
                                    <p class="text-base text-gray-900">{{ $payload['version'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if(isset($payload['description']))
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-2">{{ __('admin.description') }}</p>
                            <p class="text-base text-gray-800 leading-relaxed bg-gray-50 rounded-xl p-4">{{ $payload['description'] }}</p>
                        </div>
                    @endif

                    @if(isset($payload['location']['latitude']) || isset($payload['location']['longitude']))
                        <div class="bg-white border border-purple-100 rounded-2xl p-6 shadow-sm">
                            <h4 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5s-3 1.343-3 3 1.343 3 3 3z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.5-7.5 10.5-7.5 10.5S4.5 18 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                {{ __('admin.location') }}
                            </h4>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('admin.latitude') }} / {{ __('admin.longitude') }}</p>
                                    <p class="text-base font-semibold text-gray-900">
                                        {{ $payload['location']['latitude'] ?? '—' }},
                                        {{ $payload['location']['longitude'] ?? '—' }}
                                    </p>
                                </div>
                                @if(isset($payload['location']['latitude']) && isset($payload['location']['longitude']))
                                    <a 
                                        href="https://www.google.com/maps?q={{ $payload['location']['latitude'] }},{{ $payload['location']['longitude'] }}"
                                        target="_blank"
                                        class="text-purple-600 hover:text-purple-700 font-medium text-sm flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ __('admin.view_on_map') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if(!empty($payload['amenities']) && is_array($payload['amenities']))
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-2">{{ __('admin.amenities') }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($payload['amenities'] as $amenity)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100">
                                        {{ $amenity }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!empty($payload['photos']) && is_array($payload['photos']))
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-3">{{ __('admin.photos') }}</p>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @foreach($payload['photos'] as $index => $photo)
                                    @php
                                        $photoUrl = is_array($photo) ? ($photo['url'] ?? null) : $photo;
                                    @endphp
                                    <div class="relative group">
                                        @if($photoUrl)
                                            <img 
                                                src="{{ $photoUrl }}" 
                                                alt="{{ $payload['title'] ?? 'Photo' }} {{ $index + 1 }}"
                                                class="w-full h-28 object-cover rounded-xl shadow-md border-2 border-purple-100" 
                                                onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'128\'%3E%3Crect fill=\'%23f3f4f6\' width=\'200\' height=\'128\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-family=\'sans-serif\' font-size=\'12\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                                        @else
                                            <div class="w-full h-28 bg-gradient-to-br from-purple-100 to-pink-100 rounded-xl border-2 border-dashed border-purple-200 flex items-center justify-center">
                                                <svg class="w-8 h-8 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 7l3.38 10.14A2 2 0 008.3 19h7.4a2 2 0 001.92-1.86L21 7M10 11v4m4-4v4m-6-7V5a2 2 0 012-2h4a2 2 0 012 2v3" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-gray-50 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ json_encode($proposal->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            @endif
        </div>
    </div>

    <!-- Review Form Sidebar -->
    @if($proposal->status === 'PENDING')
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('admin.decision') }}</h2>
        <form method="POST" action="{{ route('admin.proposals.review', $proposal->id) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.decision') }}</label>
                <select 
                    name="decision" 
                    class="w-full px-4 py-2 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                    required>
                    <option value="">{{ __('admin.decision') }}</option>
                    <option value="APPROVED">{{ __('admin.approve') }}</option>
                    <option value="REJECTED">{{ __('admin.reject') }}</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.notes') }}</label>
                <textarea 
                    name="notes" 
                    maxlength="500" 
                    rows="4"
                    placeholder="{{ __('admin.notes_optional') }}"
                    class="w-full px-4 py-2 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition resize-none"></textarea>
            </div>
            <button 
                type="submit"
                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 px-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200">
                {{ __('admin.submit') }}
            </button>
        </form>
    </div>
    @else
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-6">
        <div class="text-center py-8">
            <div class="w-16 h-16 bg-{{ $proposal->status === 'APPROVED' ? 'green' : 'red' }}-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-{{ $proposal->status === 'APPROVED' ? 'green' : 'red' }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($proposal->status === 'APPROVED')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    @endif
                </svg>
            </div>
            <p class="text-lg font-semibold text-gray-800 mb-2">
                {{ __('admin.statuses.' . $proposal->status) }}
            </p>
            @if($proposal->applied_at)
                <p class="text-sm text-gray-500">
                    {{ __('admin.applied_at') }}: {{ $proposal->applied_at->format('M d, Y H:i') }}
                </p>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

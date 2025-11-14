@extends('layouts.admin')

@section('title', __('admin.properties'))

@section('content')
<!-- Filters -->
<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8 mb-8">
    <form method="GET" action="{{ route('admin.properties.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.city') }}</label>
            <input 
                type="text" 
                name="city" 
                value="{{ request('city') }}"
                placeholder="{{ __('admin.city') }}"
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.type') }}</label>
            <select 
                name="type"
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                <option value="">{{ __('admin.type') }}</option>
                @foreach(['apartment', 'villa', 'chalet'] as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                        {{ __('admin.property_types.' . $type) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.owner_id') }}</label>
            <input 
                type="text" 
                name="owner_id" 
                value="{{ request('owner_id') }}"
                placeholder="{{ __('admin.owner_id') }}"
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
        </div>
        <div class="flex items-end gap-3">
            <button 
                type="submit"
                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 px-6 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200">
                {{ __('admin.filter') }}
            </button>
            @if(request()->anyFilled(['city', 'type', 'owner_id']))
                <a 
                    href="{{ route('admin.properties.index') }}"
                    class="px-5 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition">
                    {{ __('admin.clear') }}
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Properties Table -->
<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100">
    <div class="overflow-x-auto">
        <table class="min-w-[960px] w-full" role="table">
            <thead class="bg-gradient-to-r from-purple-50 to-pink-50">
                <tr>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.photo') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.id') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.title') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.city') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.type') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.price') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.owner') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.created_at') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($properties as $property)
                    <tr class="hover:bg-purple-50 transition">
                        <td class="px-8 py-5 whitespace-nowrap">
                            @php
                                $photos = $property->photos ?? [];
                                $thumbnail = !empty($photos) 
                                    ? (is_array($photos[0]) ? ($photos[0]['url'] ?? null) : $photos[0])
                                    : null;
                            @endphp
                            @if($thumbnail)
                                <img 
                                    src="{{ $thumbnail }}" 
                                    alt="{{ $property->title }}"
                                    class="w-16 h-16 object-cover rounded-lg shadow-md border-2 border-purple-100 hover:shadow-lg transition"
                                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'64\' height=\'64\'%3E%3Crect fill=\'%23f3f4f6\' width=\'64\' height=\'64\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-family=\'sans-serif\' font-size=\'10\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                            @else
                                <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-pink-100 rounded-lg flex items-center justify-center border-2 border-purple-200">
                                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-600 font-mono">
                            {{ substr($property->id, 0, 8) }}...
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $property->title }}</div>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-600">{{ $property->city }}</td>
                        <td class="px-8 py-5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                {{ __('admin.property_types.' . $property->type) }}
                            </span>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm font-semibold text-gray-900">
                            {{ number_format($property->price, 2) }} LYD
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-600">
                            {{ $property->owner->name ?? 'N/A' }}
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-500">
                            {{ $property->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm space-x-3">
                            <a 
                                href="{{ route('admin.properties.show', $property->id) }}"
                                class="text-purple-600 hover:text-purple-700 font-medium">
                                {{ __('admin.view') }}
                            </a>
                            <a 
                                href="{{ route('admin.properties.edit', $property->id) }}"
                                class="text-indigo-600 hover:text-indigo-700 font-medium">
                                {{ __('admin.edit') }}
                            </a>
                            <form 
                                action="{{ route('admin.properties.destroy', $property->id) }}" 
                                method="POST" 
                                class="inline"
                                onsubmit="return confirm('{{ __('admin.confirm_delete_property') }}')">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit"
                                    class="text-red-600 hover:text-red-700 font-medium">
                                    {{ __('admin.delete') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-8 py-16 text-center">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
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
    @if($properties->hasPages())
        <div class="px-8 py-6 border-t border-gray-200">
            {{ $properties->links() }}
        </div>
    @endif
</div>
@endsection

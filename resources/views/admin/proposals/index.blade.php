@extends('layouts.admin')

@section('title', __('admin.proposals'))

@section('content')
<!-- Filters -->
<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8 mb-8">
    <form method="GET" action="{{ route('admin.proposals.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.owner_id') }}</label>
            <input 
                type="text" 
                name="owner_id" 
                value="{{ request('owner_id') }}"
                placeholder="{{ __('admin.owner_id') }}"
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.status') }}</label>
            <select 
                name="status"
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                <option value="">{{ __('admin.status') }}</option>
                @foreach(['PENDING', 'APPROVED', 'REJECTED'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                        {{ __('admin.statuses.' . $status) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.type') }}</label>
            <select 
                name="type"
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                <option value="">{{ __('admin.type') }}</option>
                @foreach(['ADD', 'EDIT', 'DELETE'] as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                        {{ __('admin.types.' . $type) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.from_date') }}</label>
            <input 
                type="date" 
                name="from" 
                value="{{ request('from') }}"
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.to_date') }}</label>
            <input 
                type="date" 
                name="to" 
                value="{{ request('to') }}"
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
        </div>
        <div class="flex items-end gap-3">
            <button 
                type="submit"
                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 px-6 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200">
                {{ __('admin.filter') }}
            </button>
            @if(request()->anyFilled(['owner_id', 'status', 'type', 'from', 'to']))
                <a 
                    href="{{ route('admin.proposals.index') }}"
                    class="px-5 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition">
                    {{ __('admin.clear') }}
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Proposals Table -->
<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full" role="table">
            <thead class="bg-gradient-to-r from-purple-50 to-pink-50">
                <tr>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.id') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.owner') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.type') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.status') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.submitted_at') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($proposals as $proposal)
                    <tr class="hover:bg-purple-50 transition">
                        <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-600 font-mono">
                            {{ substr($proposal->id, 0, 8) }}...
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $proposal->owner->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $proposal->owner->email ?? '' }}</div>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $proposal->type === 'ADD' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $proposal->type === 'EDIT' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $proposal->type === 'DELETE' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ __('admin.types.' . $proposal->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $proposal->status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $proposal->status === 'APPROVED' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $proposal->status === 'REJECTED' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ __('admin.statuses.' . $proposal->status) }}
                            </span>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-500">
                            {{ $proposal->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm">
                            <a 
                                href="{{ route('admin.proposals.show', $proposal->id) }}"
                                class="text-purple-600 hover:text-purple-700 font-medium">
                                {{ __('admin.view') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-8 py-16 text-center">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
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
    @if($proposals->hasPages())
        <div class="px-8 py-6 border-t border-gray-200">
            {{ $proposals->links() }}
        </div>
    @endif
</div>
@endsection

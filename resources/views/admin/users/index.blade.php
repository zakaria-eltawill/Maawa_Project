@extends('layouts.admin')

@section('title', __('admin.users'))

@section('content')
<!-- Filters -->
<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8 mb-8">
    <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.search') }}</label>
            <input 
                type="text" 
                name="q" 
                value="{{ request('q') }}"
                placeholder="{{ __('admin.name') }} / {{ __('admin.email') }}"
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.role') }}</label>
            <select 
                name="role"
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                <option value="">{{ __('admin.role') }}</option>
                @foreach(['tenant', 'owner', 'admin'] as $role)
                    <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                        {{ __('admin.roles.' . $role) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.status') }}</label>
            <select 
                name="status"
                class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                <option value="">{{ __('admin.status') }}</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('admin.inactive') }}</option>
            </select>
        </div>
        <div class="flex items-end gap-3">
            <button 
                type="submit"
                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 px-6 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200">
                {{ __('admin.filter') }}
            </button>
            @if(request()->anyFilled(['q', 'role', 'status']))
                <a 
                    href="{{ route('admin.users.index') }}"
                    class="px-5 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition">
                    {{ __('admin.clear') }}
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full" role="table">
            <thead class="bg-gradient-to-r from-purple-50 to-pink-50">
                <tr>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.id') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.name') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.email') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.role') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.status') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.created_at') }}</th>
                    <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr class="hover:bg-purple-50 transition">
                        <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-600 font-mono">
                            {{ substr($user->id, 0, 8) }}...
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-semibold text-sm mr-4">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                            </div>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-600">{{ $user->email }}</td>
                        <td class="px-8 py-5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $user->role === 'owner' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $user->role === 'tenant' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ __('admin.roles.' . $user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? __('admin.active') : __('admin.inactive') }}
                            </span>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-sm space-x-3">
                            <a 
                                href="{{ route('admin.users.show', $user->id) }}"
                                class="text-purple-600 hover:text-purple-700 font-medium">
                                {{ __('admin.view') }}
                            </a>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button 
                                        type="submit"
                                        class="{{ $user->is_active ? 'text-red-600 hover:text-red-700' : 'text-green-600 hover:text-green-700' }} font-medium"
                                        onclick="return confirm('{{ $user->is_active ? __('admin.confirm_deactivate') : __('admin.confirm_activate') }}')">
                                        {{ $user->is_active ? __('admin.deactivate') : __('admin.activate') }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-8 py-16 text-center">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
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
    @if($users->hasPages())
        <div class="px-8 py-6 border-t border-gray-200">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection

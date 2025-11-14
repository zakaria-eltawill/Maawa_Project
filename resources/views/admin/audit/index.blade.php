@extends('layouts.admin')

@section('title', __('admin.audit'))

@section('content')
<div class="space-y-8">
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
        <form method="GET" action="{{ route('admin.audit.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.actor') }}</label>
                <input type="text" name="actor" value="{{ request('actor') }}" placeholder="{{ __('admin.actor_placeholder') }}" class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.entity_type') }}</label>
                <select name="entity_type" class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                    <option value="">{{ __('admin.all') }}</option>
                    @foreach($entityTypes as $type)
                        <option value="{{ $type }}" {{ request('entity_type') === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.action') }}</label>
                <select name="action" class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
                    <option value="">{{ __('admin.all') }}</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.from_date') }}</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.to_date') }}</label>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full px-5 py-3 border-2 border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition">
            </div>
            <div class="md:col-span-5 flex flex-wrap items-center gap-3">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200">
                    {{ __('admin.filter') }}
                </button>
                @if(request()->anyFilled(['actor', 'entity_type', 'action', 'from', 'to']))
                    <a href="{{ route('admin.audit.index') }}" class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition">
                        {{ __('admin.clear') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100">
        @if($audits->isEmpty())
            <div class="p-8 text-center text-gray-500">
                {{ __('admin.no_audit_entries') }}
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full" role="table">
                    <thead class="bg-gradient-to-r from-purple-50 to-pink-50">
                        <tr>
                            <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.timestamp') }}</th>
                            <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.actor') }}</th>
                            <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.action') }}</th>
                            <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.entity') }}</th>
                            <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.summary') }}</th>
                            <th class="px-8 py-5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($audits as $audit)
                            <tr class="hover:bg-purple-50 transition">
                                <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-600">
                                    <div class="font-medium text-gray-900">{{ $audit->created_at->timezone('Africa/Tripoli')->format('M d, Y H:i') }}</div>
                                    <div class="text-xs text-gray-500">{{ $audit->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-600">
                                    {{ $audit->actor_name ?? __('admin.system') }}<br>
                                    <span class="text-xs text-gray-400">{{ $audit->actor_email }}</span>
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-700 font-medium">
                                    {{ $audit->action }}
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-600">
                                    <div class="font-semibold text-gray-800">{{ ucfirst($audit->entity_type) }}</div>
                                    <div class="text-xs text-gray-400">{{ $audit->entity_name ?? $audit->entity_id }}</div>
                                </td>
                                <td class="px-8 py-5 text-sm text-gray-600">
                                    {{ $audit->summary }}
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-sm">
                                    <a href="{{ route('admin.audit.show', $audit->id) }}" class="text-purple-600 hover:text-purple-700 font-medium">{{ __('admin.view') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-8 py-6 border-t border-gray-200">
                {{ $audits->links() }}
            </div>
        @endif
    </div>
</div>
@endsection



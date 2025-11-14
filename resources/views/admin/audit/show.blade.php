@extends('layouts.admin')

@section('title', __('admin.audit_entry'))

@section('content')
<div class="space-y-8">
    <a href="{{ route('admin.audit.index') }}" class="inline-flex items-center text-sm text-purple-600 hover:text-purple-700 font-semibold">
        &larr; {{ __('admin.back_to_audit') }}
    </a>

    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ __('admin.audit_entry') }}</h2>
                <p class="text-sm text-gray-500">{{ $audit->created_at->timezone('Africa/Tripoli')->format('M d, Y H:i:s') }} ({{ $audit->created_at->diffForHumans() }})</p>
            </div>
            <span class="px-4 py-2 bg-purple-100 text-purple-700 rounded-full text-sm font-semibold">{{ $audit->action }}</span>
        </div>

        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <dt class="text-sm font-medium text-gray-600">{{ __('admin.actor') }}</dt>
                <dd class="text-base text-gray-900">
                    {{ $audit->actor_name ?? __('admin.system') }}<br>
                    <span class="text-xs text-gray-400">{{ $audit->actor_email ?? '-' }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-600">{{ __('admin.entity') }}</dt>
                <dd class="text-base text-gray-900">
                    {{ ucfirst($audit->entity_type) }}<br>
                    <span class="text-xs text-gray-400">{{ $audit->entity_name ?? $audit->entity_id }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-600">IP</dt>
                <dd class="text-base text-gray-900">{{ $audit->ip_address ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-600">User Agent</dt>
                <dd class="text-xs text-gray-500 break-all">{{ $audit->user_agent ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    @if(!empty($audit->metadata))
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ __('admin.metadata') }}</h3>
            <pre class="bg-gray-50 rounded-xl p-4 text-sm overflow-x-auto">{{ json_encode($audit->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ __('admin.before') }}</h3>
            <pre class="bg-gray-50 rounded-xl p-4 text-sm overflow-x-auto">{{ json_encode($audit->before ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ __('admin.after') }}</h3>
            <pre class="bg-gray-50 rounded-xl p-4 text-sm overflow-x-auto">{{ json_encode($audit->after ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>

    @if(!empty($audit->diff))
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 p-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ __('admin.changes') }}</h3>
            <div class="space-y-4">
                @foreach($audit->diff as $field => $change)
                    <div class="bg-gray-50 border border-purple-100 rounded-xl p-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">{{ $field }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-xs uppercase text-gray-400 mb-1">{{ __('admin.before') }}</p>
                                <pre class="bg-white rounded-lg border border-gray-200 p-3 overflow-x-auto">{{ json_encode($change['before'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            <div>
                                <p class="text-xs uppercase text-gray-400 mb-1">{{ __('admin.after') }}</p>
                                <pre class="bg-white rounded-lg border border-gray-200 p-3 overflow-x-auto">{{ json_encode($change['after'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection



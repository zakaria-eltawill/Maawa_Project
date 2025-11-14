@extends('layouts.admin')
@section('title', __('admin.notifications'))

@section('content')
@php($isRtl = app()->getLocale() === 'ar')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h3 class="text-2xl font-bold text-gray-900">{{ __('admin.notifications') }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ __('admin.notification_log') }}</p>
        </div>
        <div class="inline-flex bg-white/80 rounded-full shadow-md border border-purple-100 p-1">
            @php($tabs = ['' => __('admin.all'), 'unread' => __('admin.notifications_unread'), 'read' => __('admin.notifications_read')])
            @foreach($tabs as $value => $label)
                @php($active = ($status ?? '') === $value)
                <a href="{{ route('admin.notifications.index', array_filter(['status' => $value])) }}"
                   class="px-4 py-1.5 text-sm font-medium rounded-full transition-all duration-200 {{ $active ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow' : 'text-gray-600 hover:text-purple-600' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if(($adminUnreadNotificationsCount ?? 0) > 0)
        <div class="flex justify-end">
            <form method="POST" action="{{ route('admin.notifications.read_all') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-semibold rounded-full shadow-lg hover:shadow-xl transition transform hover:scale-[1.02]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ __('admin.notifications_mark_all_read') }}
                </button>
            </form>
        </div>
    @endif

    @if($notifications->isEmpty())
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-purple-100 p-12 text-center text-gray-500">
            <svg class="mx-auto w-12 h-12 text-purple-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-lg font-semibold text-gray-700 mb-1">{{ __('admin.notifications_empty') }}</p>
            <p class="text-sm">{{ __('admin.no_data') }}</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($notifications as $notification)
                @php($data = $notification->data ?? [])
                @php($params = $data['params'] ?? [])
                @php($titleKey = $data['title_key'] ?? null)
                @php($bodyKey = $data['body_key'] ?? null)
                @php($title = $titleKey ? __($titleKey, $params) : ($notification->title ?? ''))
                @php($body = $bodyKey ? __($bodyKey, $params) : ($notification->message ?? ''))
                @php($isUnread = !$notification->is_read)
                @php($created = $notification->created_at?->copy()->locale(app()->getLocale()))
                @php($route = $data['route'] ?? null)
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border {{ $isUnread ? 'border-purple-200' : 'border-transparent' }} p-6 hover:shadow-2xl transition-all duration-300">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white shadow-md">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-800 flex items-center gap-3">
                                        {{ $title }}
                                        @if($isUnread)
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-700">
                                                {{ __('admin.notifications_unread_badge') }}
                                            </span>
                                        @endif
                                    </h4>
                                    @if($body)
                                        <p class="text-sm text-gray-600">{{ $body }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="text-xs text-gray-500 flex flex-wrap items-center gap-3">
                                @if($created)
                                    <span>
                                        {{ __('admin.notifications_received_at') }}:
                                        <strong class="text-gray-700">{{ $created->translatedFormat('d M Y, h:i A') }}</strong>
                                    </span>
                                    <span>• {{ $created->diffForHumans() }}</span>
                                @endif
                                <span>• {{ $notification->type }}</span>
                                @if(!empty($data['proposal_id']))
                                    <span>• ID: {{ $data['proposal_id'] }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col items-stretch gap-3">
                            @if($route)
                                <a href="{{ $route }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full border border-purple-200 text-sm font-semibold text-purple-600 hover:bg-purple-50 transition" target="_blank" rel="noopener">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h6m0 0v6m0-6L10 16m2 5H6a2 2 0 01-2-2V6a2 2 0 012-2h7"></path>
                                    </svg>
                                    {{ __('admin.notifications_view') }}
                                </a>
                            @endif

                            @if($isUnread)
                                <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-semibold shadow hover:shadow-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        {{ __('admin.notifications_mark_read') }}
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full bg-slate-100 text-slate-600 text-sm font-semibold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ __('admin.notifications_read') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div>
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection



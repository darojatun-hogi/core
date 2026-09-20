@extends('layouts.dashboard.master')
@section('title', 'Notifications')
@section('content')
	<div class="rounded-box border border-base-300 bg-base-100 p-3 shadow-sm">
		<div class="flex items-center justify-between mb-4">
			<div class="flex gap-2">
				<a href="{{ route('notifications.index') }}"
					class="btn btn-sm {{ !request('filter') ? 'btn-primary' : 'btn-outline' }}">All</a>
				<a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
					class="btn btn-sm {{ request('filter') === 'unread' ? 'btn-primary' : 'btn-outline' }}">Unread</a>
				<a href="{{ route('notifications.index', ['filter' => 'read']) }}"
					class="btn btn-sm {{ request('filter') === 'read' ? 'btn-primary' : 'btn-outline' }}">Read</a>
			</div>

			<form action="{{ route('notifications.markAllRead') }}" method="POST">
				@csrf
				<button type="submit" class="btn btn-sm btn-outline">
					Mark all as read
				</button>
			</form>
		</div>

		<div class="flex flex-col gap-3">
			@forelse ($notifications as $notification)
				<a href="{{ route('notifications.read', $notification->id) }}"
					class="flex items-start gap-3 py-3 {{ $notification->read_at ? '' : 'bg-base-200/50' }} px-2 rounded-box border">
					<div class="relative shrink-0">
						<div class="avatar avatar-placeholder @unless ($notification->read_at) avatar-busy-top @endunless">
							<div class="bg-neutral text-neutral-content size-11 rounded-full">
								<span class="text-sm">
									{{ Str::of($notification->data['actor_name'] ?? '?')->substr(0, 2)->upper() }}
								</span>
							</div>
						</div>
					</div>
					<div class="flex-1">
						<h6 class="text-base">{{ $notification->data['title'] }}</h6>
						<small class="text-base-content/50">{{ $notification->data['message'] }}</small>
					</div>
					<small class="text-base-content/40 whitespace-nowrap">
						{{ $notification->created_at->diffForHumans() }}
					</small>
				</a>
			@empty
				<div class="py-8 text-center text-base-content/50">
					No notifications found.
				</div>
			@endforelse
		</div>

		<div class="mt-4">
			{{ $notifications->links('vendor.pagination.flyonui') }}
		</div>
	</div>
@endsection

@extends('layouts.dashboard.master')
@section('title', 'Notifikasi')
@section('content')
	@php($filter = request('filter'))

	<div class="flex flex-col gap-6">
		<x-page-header title="Notifikasi" description="Perubahan akses dan akun yang melibatkan kamu.">
			<x-slot:actions>
				<form action="{{ route('notifications.markAllRead') }}" method="POST">
					@csrf
					<button type="submit" class="btn btn-soft">
						<span class="icon-[tabler--checks] size-5"></span>
						Tandai semua dibaca
					</button>
				</form>
			</x-slot:actions>
		</x-page-header>

		<div class="overflow-hidden rounded-box border border-base-content/10 bg-base-100">
			<nav class="flex gap-1 border-b border-base-content/10 px-3 pt-3" aria-label="Filter notifikasi">
				@foreach ([null => 'Semua', 'unread' => 'Belum dibaca', 'read' => 'Sudah dibaca'] as $key => $label)
					<a href="{{ route('notifications.index', array_filter(['filter' => $key])) }}"
						@if ($filter === ($key ?: null)) aria-current="page" @endif
						@class([
							'-mb-px border-b-2 px-3 pb-2.5 pt-1 text-sm font-medium transition-colors',
							'border-primary text-primary' => $filter === ($key ?: null),
							'border-transparent text-base-content/70 hover:text-base-content' => $filter !== ($key ?: null),
						])>{{ $label }}</a>
				@endforeach
			</nav>

			@if ($notifications->count())
				<ul class="divide-y divide-base-content/10">
					@foreach ($notifications as $notification)
						<li>
							<a href="{{ route('notifications.read', $notification->id) }}"
								class="flex items-start gap-3 px-5 py-4 transition-colors hover:bg-base-200 sm:px-6">
								<span @class([
									'mt-2 size-2 shrink-0 rounded-full',
									'bg-primary' => ! $notification->read_at,
									'bg-transparent' => $notification->read_at,
								])>
									@unless ($notification->read_at)
										<span class="sr-only">Belum dibaca</span>
									@endunless
								</span>
								<span class="min-w-0 flex-1">
									<span @class(['block', 'font-semibold' => ! $notification->read_at])>{{ $notification->data['title'] }}</span>
									<span class="mt-0.5 block text-sm text-base-content/70">{{ $notification->data['message'] }}</span>
								</span>
								<time datetime="{{ $notification->created_at->toIso8601String() }}"
									class="shrink-0 text-sm text-base-content/70" title="{{ $notification->created_at->translatedFormat('d M Y, H:i') }}">
									{{ $notification->created_at->diffForHumans() }}
								</time>
							</a>
						</li>
					@endforeach
				</ul>
			@elseif ($filter === 'unread')
				<x-empty-state icon="icon-[tabler--checks]" title="Semua sudah dibaca"
					description="Notifikasi baru akan muncul di sini." />
			@else
				<x-empty-state icon="icon-[tabler--bell-off]" title="Belum ada notifikasi"
					description="Kamu akan diberi tahu di sini saat akses atau akunmu berubah." />
			@endif
		</div>

		{{ $notifications->links('vendor.pagination.flyonui') }}
	</div>
@endsection

@php($initials = Str::of(auth()->user()->name)->explode(' ')->filter()->take(2)->map(fn ($part) => Str::substr($part, 0, 1))->implode(''))

<nav class="navbar sticky top-0 z-10 h-16 min-h-16 border-b border-base-content/10 bg-base-100 px-4 sm:px-6">
	<div class="flex flex-1 items-center gap-2">
		<button type="button" class="btn btn-text btn-square sm:hidden" aria-haspopup="dialog" aria-expanded="false"
			aria-controls="app-sidebar" aria-label="Buka navigasi" data-overlay="#app-sidebar">
			<span class="icon-[tabler--menu-2] size-5"></span>
		</button>
		<span class="text-lg font-bold tracking-tight sm:hidden">Elips</span>
	</div>

	<div class="navbar-end flex items-center gap-1">
		<button type="button" class="btn btn-text btn-square" data-theme-toggle aria-label="Ganti tema terang/gelap">
			<span class="icon-[tabler--moon] size-5 dark:hidden"></span>
			<span class="icon-[tabler--sun] hidden size-5 dark:block"></span>
		</button>

		{{-- Notifikasi --}}
		<div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
			<button id="dropdown-notifications" type="button" class="dropdown-toggle btn btn-text btn-square"
				aria-haspopup="menu" aria-expanded="false"
				aria-label="Notifikasi{{ $unreadNotificationsCount > 0 ? ', ' . $unreadNotificationsCount . ' belum dibaca' : '' }}">
				<span class="indicator">
					@if ($unreadNotificationsCount > 0)
						<span class="indicator-item size-2 rounded-full bg-error"></span>
					@endif
					<span class="icon-[tabler--bell] size-5"></span>
				</span>
			</button>
			<div class="dropdown-menu dropdown-open:opacity-100 hidden w-80 max-w-[calc(100vw-2rem)] p-0" role="menu"
				aria-orientation="vertical" aria-labelledby="dropdown-notifications">
				<div class="flex items-center justify-between border-b border-base-content/10 px-4 py-3">
					<span class="font-semibold">Notifikasi</span>
					@if ($unreadNotificationsCount > 0)
						<span class="text-sm text-base-content/70">{{ $unreadNotificationsCount }} belum dibaca</span>
					@endif
				</div>
				<div class="max-h-80 overflow-y-auto p-1">
					@forelse ($navbarNotifications as $notification)
						<a href="{{ route('notifications.read', $notification->id) }}"
							class="dropdown-item items-start gap-3 rounded-field py-2.5">
							<span @class([
								'mt-1.5 size-2 shrink-0 rounded-full',
								'bg-primary' => ! $notification->read_at,
								'bg-transparent' => $notification->read_at,
							]) aria-hidden="true"></span>
							<span class="min-w-0 flex-1">
								<span @class(['block truncate', 'font-semibold' => ! $notification->read_at])>
									{{ $notification->data['title'] }}
								</span>
								<span class="block truncate text-sm text-base-content/70">{{ $notification->data['message'] }}</span>
							</span>
						</a>
					@empty
						<p class="px-4 py-6 text-center text-sm text-base-content/70">Belum ada notifikasi.</p>
					@endforelse
				</div>
				<a href="{{ route('notifications.index') }}"
					class="block border-t border-base-content/10 px-4 py-3 text-center text-sm font-medium text-primary hover:bg-base-200">
					Lihat semua notifikasi
				</a>
			</div>
		</div>

		{{-- Akun --}}
		<div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
			<button id="dropdown-account" type="button" class="dropdown-toggle btn btn-text gap-2 px-1.5" aria-haspopup="menu"
				aria-expanded="false" aria-label="Menu akun">
				<span class="avatar avatar-placeholder">
					<span class="size-8 rounded-full bg-primary/15 text-primary">
						<span class="text-sm font-semibold">{{ $initials }}</span>
					</span>
				</span>
				<span class="max-w-32 truncate text-sm font-medium max-md:hidden">{{ auth()->user()->name }}</span>
				<span class="icon-[tabler--chevron-down] size-4 text-base-content/60 max-md:hidden"></span>
			</button>
			<ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu" aria-orientation="vertical"
				aria-labelledby="dropdown-account">
				<li class="dropdown-header flex-col items-start gap-0.5 normal-case">
					<span class="font-semibold text-base-content">{{ auth()->user()->name }}</span>
					<span class="text-sm text-base-content/70">{{ auth()->user()->email }}</span>
					@if ($accountRole = auth()->user()->roles->first())
						<span class="badge badge-soft badge-sm mt-1.5">{{ $accountRole->name }}</span>
					@endif
				</li>
				<li>
					<a class="dropdown-item" href="{{ route('profile.edit') }}">
						<span class="icon-[tabler--user-circle] size-5"></span>
						Profil saya
					</a>
				</li>
				<li class="dropdown-footer border-t border-base-content/10 pt-1">
					<form method="POST" class="w-full" action="{{ route('logout') }}">
						@csrf
						<button type="submit" class="dropdown-item text-error">
							<span class="icon-[tabler--logout-2] size-5"></span>
							Keluar
						</button>
					</form>
				</li>
			</ul>
		</div>
	</div>
</nav>

<nav class="navbar bg-base-100 shadow-base-300/20 shadow-sm px-4">
	{{-- Mobile menu button --}}
	<button type="button" class="btn btn-text max-sm:btn-square sm:hidden" aria-haspopup="dialog" aria-expanded="false"
		aria-controls="collapsible-mini-sidebar" data-overlay="#collapsible-mini-sidebar">
		<span class="icon-[tabler--menu-2] size-5"></span>
	</button>
	{{-- End Mobile menu button --}}
	<div class="flex flex-1 items-center">
		<a class="link text-base-content link-neutral text-xl font-bold no-underline" href="#">
			@yield('title', 'Elips')
		</a>
	</div>
	<div class="navbar-end flex items-center gap-4">
		<div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
			<button id="dropdown-scrollable" type="button"
				class="dropdown-toggle btn btn-text btn-circle dropdown-open:bg-base-content/10 size-10" aria-haspopup="menu"
				aria-expanded="false" aria-label="Dropdown">
				<div class="indicator">
					@if ($unreadNotificationsCount > 0)
						<span class="indicator-item bg-error size-2 rounded-full"></span>
					@endif
					<span class="icon-[tabler--bell] text-base-content size-5.5"></span>
				</div>
			</button>
			<div class="dropdown-menu dropdown-open:opacity-100 hidden" role="menu" aria-orientation="vertical"
				aria-labelledby="dropdown-scrollable">
				<div class="dropdown-header justify-center">
					<h6 class="text-base-content text-base">Notifications</h6>
				</div>
				<div class="overflow-auto text-base-content/80 max-h-56 max-md:max-w-60">
					@forelse ($navbarNotifications as $notification)
						<a href="{{ route('notifications.read', $notification->id) }}"
							class="dropdown-item {{ $notification->read_at ? '' : 'bg-base-200/50' }}">
							<div class="relative shrink-0">
								<div class="avatar avatar-placeholder @unless ($notification->read_at) avatar-busy-top @endunless">
									<div class="bg-neutral text-neutral-content size-12 rounded-full">
										<span class="text-sm">
											{{ Str::of($notification->data['actor_name'] ?? '?')->substr(0, 2)->upper() }}
										</span>
									</div>
								</div>
							</div>
							<div class="w-60">
								<h6 class="truncate text-base">{{ $notification->data['title'] }}</h6>
								<small class="text-base-content/50 truncate">{{ $notification->data['message'] }}</small>
							</div>
						</a>
					@empty
						<div class="dropdown-item justify-center text-base-content/50">
							No notifications yet.
						</div>
					@endforelse
				</div>
				<a href="{{ route('notifications.index') }}" class="dropdown-footer justify-center gap-1">
					<span class="icon-[tabler--eye] size-4"></span>
					View all
				</a>
			</div>
		</div>
		<div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
			<button id="dropdown-scrollable" type="button" class="dropdown-toggle flex items-center" aria-haspopup="menu"
				aria-expanded="false" aria-label="Dropdown">
				<div class="avatar">
					<div class="size-9.5 rounded-full">
						<img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png" alt="avatar 1" />
					</div>
				</div>
			</button>
			<ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu" aria-orientation="vertical"
				aria-labelledby="dropdown-avatar">
				<li class="dropdown-header gap-2">
					<div class="avatar">
						<div class="w-10 rounded-full">
							<img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png" alt="avatar" />
						</div>
					</div>
					<div>
						<h6 class="text-base-content text-base font-semibold">{{ auth()->user()->name }}</h6>
						<small class="text-base-content/50">{{ auth()->user()->roles->first()->name }}</small>
					</div>
				</li>
				<li>
					<a class="dropdown-item" href="{{ route('profile.edit') }}">
						<span class="icon-[tabler--user]"></span>
						My Profile
					</a>
				</li>
				<li class="dropdown-footer gap-2">
					<form method="POST" class="w-full flex items-center p-0" action="{{ route('logout') }}">
						@csrf
						<button type="submit" class="btn btn-error btn-soft btn-block w-full">
							<span class="icon-[tabler--logout-2]"></span>
							Sign Out
						</button>
					</form>
				</li>
			</ul>
		</div>
	</div>
</nav>

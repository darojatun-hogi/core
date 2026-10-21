{{-- Submenu memakai collapse (bukan dropdown) supaya tetap terbuka saat salah satu halamannya aktif --}}
@php($accessOpen = request()->routeIs('users.*', 'roles.*'))

<aside id="app-sidebar"
	class="overlay [--auto-close:sm] overlay-minified:w-17 overlay-open:translate-x-0 drawer drawer-start hidden w-64 border-e border-base-content/10 bg-base-100 sm:relative sm:z-0 sm:flex sm:translate-x-0 sm:shadow-none"
	role="dialog" tabindex="-1" aria-label="Navigasi utama">

	<div class="drawer-header flex h-16 w-full items-center justify-between gap-3 px-5 py-0 overlay-minified:px-3.5">
		<a href="{{ route('dashboard') }}" class="text-xl font-bold tracking-tight text-base-content overlay-minified:hidden">
			Elips
		</a>
		<button type="button" class="btn btn-text btn-square btn-sm max-sm:hidden" aria-haspopup="dialog" aria-expanded="false"
			aria-controls="app-sidebar" aria-label="Perkecil navigasi" data-overlay-minifier="#app-sidebar">
			<span class="icon-[tabler--layout-sidebar-left-collapse] size-5 overlay-minified:hidden"></span>
			<span class="icon-[tabler--layout-sidebar-left-expand] hidden size-5 overlay-minified:block"></span>
		</button>
		<button type="button" class="btn btn-text btn-square btn-sm sm:hidden" aria-label="Tutup navigasi"
			data-overlay="#app-sidebar">
			<span class="icon-[tabler--x] size-5"></span>
		</button>
	</div>

	<div class="drawer-body px-3 pb-4 pt-2 overlay-minified:px-2">
		<ul class="menu gap-1 p-0">
			<li>
				<a href="{{ route('dashboard') }}" @class(['menu-active' => request()->routeIs('dashboard')])
					@if (request()->routeIs('dashboard')) aria-current="page" @endif>
					<span class="icon-[tabler--smart-home] size-5 shrink-0"></span>
					<span class="overlay-minified:hidden">Beranda</span>
				</a>
			</li>

			{{-- Mode client SSO: user/role/permission app ini dikelola di hub --}}
			@if (! Route::has('users.index') && Route::has('access.manage') && \App\Support\ManagedApp::canManage(auth()->user(), config('access.app')))
				<li>
					<a href="{{ route('access.manage') }}" target="_blank" rel="noopener">
						<span class="icon-[tabler--shield-lock] size-5 shrink-0"></span>
						<span class="grow overlay-minified:hidden">Kelola Akses</span>
						<span class="icon-[tabler--external-link] size-4 text-base-content/60 overlay-minified:hidden"
							aria-label="dibuka di tab baru"></span>
					</a>
				</li>
			@endif

			@canAny(['users.view', 'roles.view'])
				@if (Route::has('users.index'))
					<li>
						<button type="button" id="menu-access-toggle" @class(['collapse-toggle w-full', 'open' => $accessOpen])
							aria-expanded="{{ $accessOpen ? 'true' : 'false' }}" aria-controls="menu-access"
							data-collapse="#menu-access">
							<span class="icon-[tabler--shield-half] size-5 shrink-0"></span>
							<span class="grow text-start overlay-minified:hidden">Akses</span>
							<span
								class="icon-[tabler--chevron-down] size-4 transition-transform duration-200 collapse-open:rotate-180 overlay-minified:hidden"></span>
						</button>
						<ul id="menu-access" aria-labelledby="menu-access-toggle"
							@class([
								'collapse w-auto overflow-hidden transition-[height] duration-200 mt-1 overlay-minified:ms-0 overlay-minified:ps-0 overlay-minified:before:hidden',
								'open' => $accessOpen,
								'hidden' => ! $accessOpen,
							])>
							@can('users.view')
								<li>
									<a href="{{ route('users.index') }}" @class(['menu-active' => request()->routeIs('users.*')])
										@if (request()->routeIs('users.*')) aria-current="page" @endif title="Pengguna">
										<span class="icon-[tabler--users] size-5 shrink-0"></span>
										<span class="overlay-minified:hidden">Pengguna</span>
									</a>
								</li>
							@endcan
							@can('roles.view')
								<li>
									<a href="{{ route('roles.index') }}" @class(['menu-active' => request()->routeIs('roles.*')])
										@if (request()->routeIs('roles.*')) aria-current="page" @endif title="Role & Permission">
										<span class="icon-[tabler--key] size-5 shrink-0"></span>
										<span class="overlay-minified:hidden">Role & Permission</span>
									</a>
								</li>
							@endcan
						</ul>
					</li>
				@endif
			@endcanany

			@can('activity-log.view')
				<li>
					<a href="{{ route('activity-log.index') }}" @class(['menu-active' => request()->routeIs('activity-log.*')])
						@if (request()->routeIs('activity-log.*')) aria-current="page" @endif>
						<span class="icon-[tabler--history] size-5 shrink-0"></span>
						<span class="overlay-minified:hidden">Log Aktivitas</span>
					</a>
				</li>
			@endcan

			<li>
				<a href="{{ route('notifications.index') }}" @class(['menu-active' => request()->routeIs('notifications.*')])
					@if (request()->routeIs('notifications.*')) aria-current="page" @endif>
					<span class="icon-[tabler--bell] size-5 shrink-0"></span>
					<span class="grow overlay-minified:hidden">Notifikasi</span>
					@if ($unreadNotificationsCount > 0)
						<span class="badge badge-primary badge-sm overlay-minified:hidden">{{ $unreadNotificationsCount }}</span>
					@endif
				</a>
			</li>
		</ul>
	</div>
</aside>

<aside id="collapsible-mini-sidebar"
	class="overlay [--auto-close:sm] overlay-minified:w-17 sm:shadow-none overlay-open:translate-x-0 drawer drawer-start hidden w-66 sm:relative sm:z-0 sm:flex sm:translate-x-0 border-e border-base-content/20"
	role="dialog" tabindex="-1">

	<div class="drawer-header overlay-minified:px-3.75 py-2 w-full flex items-center justify-between gap-3">
		<h3 class="drawer-title text-xl font-semibold overlay-minified:hidden">FlyonUI</h3>
		<div class="hidden sm:block">
			<button type="button" class="btn btn-circle btn-text" aria-haspopup="dialog" aria-expanded="false"
				aria-controls="collapsible-mini-sidebar" aria-label="Minify navigation"
				data-overlay-minifier="#collapsible-mini-sidebar">
				<span class="icon-[tabler--menu-2] size-5"></span>
				<span class="sr-only">Navigation Toggle</span>
			</button>
		</div>
	</div>

	<div class="drawer-body px-2 pt-4">
		<ul class="menu p-0">
			<li>
				<a href="{{ url('/') }}" @class(['active' => request()->is('/')])>
					<span class="icon-[tabler--home] size-5"></span>
					<span class="overlay-minified:hidden">Home</span>
				</a>
			</li>
			<li>
				<a href="{{ url('/users') }}" @class(['active' => request()->is('/users')])>
					<span class="icon-[tabler--users] size-5"></span>
					<span class="overlay-minified:hidden">User</span>
				</a>
			</li>
			@can('roles.manage')
				<li>
					<a href="{{ url('/roles') }}" @class(['active' => request()->is('/roles')])>
						<span class="icon-[tabler--shield] size-5"></span>
						<span class="overlay-minified:hidden">Roles & Permissions</span>
					</a>
				</li>
			@endcan
			{{-- <li
				class="dropdown relative [--adaptive:none] [--strategy:static] overlay-minified:[--adaptive:adaptive] overlay-minified:[--strategy:fixed] overlay-minified:[--offset:15] overlay-minified:[--trigger:hover] overlay-minified:[--placement:right-start]">
				<button id="dropdown-default" type="button" class="dropdown-toggle" aria-haspopup="menu" aria-expanded="false"
					aria-label="Dropdown">
					<span class="icon-[tabler--apps] size-5"></span>
					<span class="overlay-minified:hidden">Apps</span>
					<span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 overlay-minified:hidden"></span>
				</button>
				<ul
					class="dropdown-menu mt-0 shadow-none overlay-minified:shadow-md overlay-minified:shadow-base-300/20 dropdown-open:opacity-100 hidden min-w-60 overlay-minified:before:absolute overlay-minified:before:-start-4 overlay-minified:before:top-0 overlay-minified:before:h-full overlay-minified:before:w-4 before:bg-transparent"
					role="menu" aria-orientation="vertical" aria-labelledby="dropdown-default">
					<li>
						<a href="{{ url('/mail') }}">
							<span class="icon-[tabler--mail] size-5"></span>
							Email
						</a>
					</li>
					<li>
						<a href="{{ url('/calendar') }}">
							<span class="icon-[tabler--calendar] size-5"></span>
							Calendar
						</a>
					</li>
				</ul>
			</li> --}}

			<li>
				<form method="POST" action="{{ route('logout') }}" class="w-full flex items-center p-0">
					@csrf
					<button type="submit" class="flex w-full items-center gap-3 px-4 py-2 text-start">
						<span class="icon-[tabler--logout-2] size-5"></span>
						<span class="overlay-minified:hidden">Sign Out</span>
					</button>
				</form>
			</li>
		</ul>
	</div>
</aside>

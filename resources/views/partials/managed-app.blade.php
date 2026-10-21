{{-- Pemilih app yang dikelola (hub SSO). Hanya tampil bila user boleh mengelola lebih dari satu app. --}}
@php($managedApps = \App\Support\ManagedApp::for(auth()->user()))
@php($currentApp = \App\Support\ManagedApp::current())
@if (count($managedApps) > 1)
	<nav class="flex flex-wrap items-center gap-2" aria-label="Pilih aplikasi yang dikelola">
		<span class="text-sm text-base-content/70">Aplikasi</span>
		<div class="flex flex-wrap gap-1 rounded-field border border-base-content/10 bg-base-100 p-1">
			@foreach ($managedApps as $app)
				<a href="{{ request()->url() }}?app={{ $app }}" @if ($app === $currentApp) aria-current="page" @endif
					@class([
						'rounded-selector px-3 py-1.5 text-sm font-medium transition-colors',
						'bg-primary text-primary-content' => $app === $currentApp,
						'text-base-content/80 hover:bg-base-200' => $app !== $currentApp,
					])>
					{{ \App\Support\ManagedApp::label($app) }}
				</a>
			@endforeach
		</div>
	</nav>
@elseif (\App\Support\Sso::isHub() && count(\App\Support\ManagedApp::all()) > 1)
	<p class="text-sm text-base-content/70">Aplikasi: <span class="font-semibold text-base-content">{{ \App\Support\ManagedApp::label($currentApp) }}</span></p>
@endif

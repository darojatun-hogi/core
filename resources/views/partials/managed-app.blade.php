{{-- Pemilih app yang dikelola (hub SSO). Hanya tampil bila user boleh mengelola lebih dari satu app. --}}
@php($managedApps = \App\Support\ManagedApp::for(auth()->user()))
@php($currentApp = \App\Support\ManagedApp::current())
@if (count($managedApps) > 1)
	<div class="flex flex-wrap items-center gap-2">
		<span class="text-sm text-base-content/70">Aplikasi:</span>
		@foreach ($managedApps as $app)
			<a href="{{ request()->url() }}?app={{ $app }}"
				@class(['btn btn-sm', 'btn-primary' => $app === $currentApp, 'btn-ghost border-base-300' => $app !== $currentApp])>
				{{ \App\Support\ManagedApp::label($app) }}
			</a>
		@endforeach
	</div>
@elseif (\App\Support\Sso::isHub() && count(\App\Support\ManagedApp::all()) > 1)
	<p class="text-sm text-base-content/70">Aplikasi: <span class="font-semibold">{{ \App\Support\ManagedApp::label($currentApp) }}</span></p>
@endif

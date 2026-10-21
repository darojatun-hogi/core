@extends('layouts.dashboard.master')
@section('title', 'Beranda')
@section('content')
	<div class="flex flex-col gap-8">
		<x-page-header :title="'Halo, ' . Str::before(auth()->user()->name, ' ')"
			:description="config('sso.hub') && config('sso.apps') ? 'Pilih aplikasi yang ingin dibuka. Kamu tidak perlu masuk lagi.' : null" />

		{{-- Hub SSO: daftar app yang bisa dibuka dengan akun yang sama --}}
		@if (config('sso.hub') && config('sso.apps'))
			<section aria-labelledby="apps-heading">
				<h2 id="apps-heading" class="sr-only">Aplikasi</h2>
				<ul class="divide-y divide-base-content/10 overflow-hidden rounded-box border border-base-content/10 bg-base-100">
					@foreach (config('sso.apps') as $app)
						<li>
							<a href="{{ $app['url'] }}"
								class="group flex items-center gap-4 px-5 py-4 transition-colors hover:bg-base-200 focus-visible:bg-base-200">
								<span
									class="flex size-10 shrink-0 items-center justify-center rounded-field bg-primary/10 text-primary">
									<span class="{{ $app['icon'] }} size-5"></span>
								</span>
								<span class="min-w-0 flex-1">
									<span class="block font-medium">{{ $app['name'] }}</span>
									<span class="block truncate text-sm text-base-content/70">{{ parse_url($app['url'], PHP_URL_HOST) }}</span>
								</span>
								<span
									class="icon-[tabler--chevron-right] size-5 text-base-content/50 transition-transform group-hover:translate-x-0.5"
									aria-hidden="true"></span>
							</a>
						</li>
					@endforeach
				</ul>
			</section>
		@endif

		<section aria-labelledby="account-heading" class="grid gap-4 sm:grid-cols-2">
			<h2 id="account-heading" class="sr-only">Akun</h2>
			<a href="{{ route('profile.edit') }}"
				class="flex items-start gap-3 rounded-box border border-base-content/10 bg-base-100 p-5 transition-colors hover:bg-base-200">
				<span class="icon-[tabler--user-circle] mt-0.5 size-5 shrink-0 text-base-content/70"></span>
				<span>
					<span class="block font-medium">Profil & password</span>
					<span class="mt-0.5 block text-sm text-base-content/70">Ubah nama, username, email, atau password akunmu.</span>
				</span>
			</a>
			<a href="{{ route('notifications.index') }}"
				class="flex items-start gap-3 rounded-box border border-base-content/10 bg-base-100 p-5 transition-colors hover:bg-base-200">
				<span class="icon-[tabler--bell] mt-0.5 size-5 shrink-0 text-base-content/70"></span>
				<span>
					<span class="block font-medium">Notifikasi</span>
					<span class="mt-0.5 block text-sm text-base-content/70">
						@if ($unreadNotificationsCount > 0)
							{{ $unreadNotificationsCount }} notifikasi belum dibaca.
						@else
							Semua notifikasi sudah dibaca.
						@endif
					</span>
				</span>
			</a>
		</section>
	</div>
@endsection

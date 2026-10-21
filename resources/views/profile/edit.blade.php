@extends('layouts.dashboard.master')
@section('title', 'Profil Saya')
@section('content')
	<div class="flex flex-col gap-8">
		<x-page-header title="Profil saya" description="Data ini dipakai di semua aplikasi Elips." />

		<section class="grid gap-6 lg:grid-cols-3" aria-labelledby="profile-info-heading">
			<div>
				<h2 id="profile-info-heading" class="font-semibold">Informasi akun</h2>
				<p class="mt-1 text-sm text-base-content/70">Nama, username, dan email yang dipakai untuk masuk.</p>
			</div>
			<form action="{{ route('profile.update') }}" method="POST"
				class="rounded-box border border-base-content/10 bg-base-100 lg:col-span-2">
				@csrf
				@method('PUT')

				<div class="space-y-4 p-5 sm:p-6">
					<x-field name="name" label="Nama lengkap" :value="$user->name" required autocomplete="name" />
					<x-field name="username" label="Username" :value="$user->username" required autocomplete="username"
						hint="Huruf, angka, tanda hubung, atau garis bawah." />
					<x-field name="email" type="email" label="Email" :value="$user->email" required autocomplete="email" />
				</div>
				<div class="flex justify-end border-t border-base-content/10 px-5 py-4 sm:px-6">
					<button type="submit" class="btn btn-primary">Simpan perubahan</button>
				</div>
			</form>
		</section>

		<section class="grid gap-6 lg:grid-cols-3" aria-labelledby="password-heading">
			<div>
				<h2 id="password-heading" class="font-semibold">Password</h2>
				<p class="mt-1 text-sm text-base-content/70">Masukkan password saat ini untuk mengganti dengan yang baru.</p>
			</div>
			<form action="{{ route('profile.password.update') }}" method="POST"
				class="rounded-box border border-base-content/10 bg-base-100 lg:col-span-2">
				@csrf
				@method('PUT')

				<div class="space-y-4 p-5 sm:p-6">
					<x-field name="current_password" type="password" label="Password saat ini" required
						autocomplete="current-password" />
					<div class="grid gap-4 sm:grid-cols-2">
						<x-field name="password" type="password" label="Password baru" required autocomplete="new-password" />
						<x-field name="password_confirmation" type="password" label="Ulangi password baru" required
							autocomplete="new-password" />
					</div>
				</div>
				<div class="flex justify-end border-t border-base-content/10 px-5 py-4 sm:px-6">
					<button type="submit" class="btn btn-primary">Ganti password</button>
				</div>
			</form>
		</section>
	</div>
@endsection

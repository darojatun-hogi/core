@extends('layouts.dashboard.master')
@section('title', 'Ubah Pengguna')
@section('content')
	<div class="mx-auto flex max-w-2xl flex-col gap-6">
		<x-page-header :title="'Ubah ' . $user->name" description="Perubahan nama, username, dan email berlaku di semua aplikasi Elips."
			:back="route('users.show', $user)" />

		<form action="{{ route('users.update', $user->id) }}" method="POST"
			class="rounded-box border border-base-content/10 bg-base-100">
			@csrf
			@method('PUT')

			<div class="space-y-4 p-5 sm:p-6">
				<x-field name="name" label="Nama lengkap" :value="$user->name" required autocomplete="off" />
				<div class="grid gap-4 sm:grid-cols-2">
					<x-field name="username" label="Username" :value="$user->username" required autocomplete="off"
						hint="Huruf, angka, tanda hubung, atau garis bawah." />
					<x-field name="email" type="email" label="Email" :value="$user->email" required autocomplete="off" />
				</div>
				@include('users._roles-select', ['selected' => old('roles', $user->roles->pluck('name')->toArray())])
			</div>

			<div class="space-y-4 border-t border-base-content/10 p-5 sm:p-6">
				<div>
					<h2 class="font-semibold">Ganti password</h2>
					<p class="mt-1 text-sm text-base-content/70">Kosongkan kalau password tidak diganti.</p>
				</div>
				<div class="grid gap-4 sm:grid-cols-2">
					<x-field name="password" type="password" label="Password baru" autocomplete="new-password" />
					<x-field name="password_confirmation" type="password" label="Ulangi password baru"
						autocomplete="new-password" />
				</div>
			</div>

			<div class="flex items-center justify-end gap-2 border-t border-base-content/10 px-5 py-4 sm:px-6">
				<a href="{{ route('users.show', $user) }}" class="btn btn-text">Batal</a>
				<button type="submit" class="btn btn-primary">Simpan perubahan</button>
			</div>
		</form>
	</div>
@endsection

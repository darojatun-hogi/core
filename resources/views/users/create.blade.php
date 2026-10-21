@extends('layouts.dashboard.master')
@section('title', 'Tambah Pengguna')
@section('content')
	<div class="mx-auto flex max-w-2xl flex-col gap-6">
		<x-page-header title="Tambah pengguna" description="Buat akun baru dan langsung beri akses ke aplikasi ini."
			:back="route('users.index')" />

		<form action="{{ route('users.store') }}" method="POST" class="rounded-box border border-base-content/10 bg-base-100">
			@csrf

			<div class="space-y-4 p-5 sm:p-6">
				<x-field name="name" label="Nama lengkap" required autocomplete="off" />
				<div class="grid gap-4 sm:grid-cols-2">
					<x-field name="username" label="Username" required autocomplete="off"
						hint="Huruf, angka, tanda hubung, atau garis bawah." />
					<x-field name="email" type="email" label="Email" required autocomplete="off" />
				</div>
				<div class="grid gap-4 sm:grid-cols-2">
					<x-field name="password" type="password" label="Password" required autocomplete="new-password" />
					<x-field name="password_confirmation" type="password" label="Ulangi password" required
						autocomplete="new-password" />
				</div>
				@include('users._roles-select', ['selected' => old('roles', [\App\Support\ManagedApp::current() . '.user'])])
			</div>

			<div class="flex items-center justify-end gap-2 border-t border-base-content/10 px-5 py-4 sm:px-6">
				<a href="{{ route('users.index') }}" class="btn btn-text">Batal</a>
				<button type="submit" class="btn btn-primary">Simpan pengguna</button>
			</div>
		</form>
	</div>
@endsection

@extends('layouts.auth.master')
@section('title', 'Verifikasi Email')
@section('content')
	<span class="icon-[tabler--mail-forward] mb-4 size-9 text-primary" aria-hidden="true"></span>
	<h1 class="text-2xl font-semibold tracking-tight">Cek email kamu</h1>
	<p class="mt-2 text-sm text-base-content/70">
		Link verifikasi sudah dikirim ke <strong class="text-base-content">{{ auth()->user()->email }}</strong>.
		Buka link itu untuk mengaktifkan akun. Kalau tidak ada di inbox, cek folder spam.
	</p>

	<form method="POST" action="{{ route('verification.send') }}" class="mt-6">
		@csrf
		<button type="submit" class="btn btn-primary btn-block">
			<span class="icon-[tabler--refresh] size-4"></span>
			Kirim ulang link verifikasi
		</button>
	</form>

	<form method="POST" action="{{ route('logout') }}" class="mt-2">
		@csrf
		<button type="submit" class="btn btn-text btn-block">Keluar</button>
	</form>
@endsection

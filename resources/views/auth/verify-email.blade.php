@extends('layouts.auth.master')
@section('content')
	<div class="mb-6 text-center">
		<div class="mb-3 flex justify-center">
			<div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/15">
				<span class="icon-[tabler--mail] text-4xl text-primary"></span>
			</div>
		</div>
		<h1 class="text-2xl font-semibold text-base-content">Verifikasi Email</h1>
		<p class="mt-1 text-sm text-base-content/60">
			Link verifikasi telah dikirim ke <strong>{{ auth()->user()->email }}</strong>. Silakan cek inbox kamu.
		</p>
	</div>

	<p class="mb-3 text-center text-sm text-base-content/50">Tidak menerima email?</p>

	<form method="POST" action="{{ route('verification.send') }}">
		@csrf
		<button type="submit" class="btn btn-primary w-full">
			<span class="icon-[tabler--refresh]"></span>
			Kirim Ulang Link Verifikasi
		</button>
	</form>

	<form method="POST" action="{{ route('logout') }}" class="mt-3">
		@csrf
		<button type="submit" class="btn btn-outline w-full">Keluar</button>
	</form>
@endsection

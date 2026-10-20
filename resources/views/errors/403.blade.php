@extends('layouts.auth.master')
@section('content')
	<div class="text-center">
		<div class="mb-3 flex justify-center">
			<div class="flex h-16 w-16 items-center justify-center rounded-full bg-error/15">
				<span class="icon-[tabler--lock] text-4xl text-error"></span>
			</div>
		</div>
		<h1 class="text-2xl font-semibold text-base-content">Akses Ditolak</h1>
		<p class="mt-1 text-sm text-base-content/60">
			{{ $exception->getMessage() ?: 'Kamu tidak punya akses ke halaman ini.' }}
		</p>
	</div>

	<div class="mt-6 flex flex-col gap-3">
		<a href="{{ \App\Support\Sso::hubUrl('dashboard') }}" class="btn btn-primary w-full">Ke Halaman Akun</a>
		@auth
			<form method="POST" action="{{ route('logout') }}">
				@csrf
				<button type="submit" class="btn btn-outline w-full">Keluar</button>
			</form>
		@endauth
	</div>
@endsection

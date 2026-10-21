@extends('layouts.auth.master')
@section('title', 'Akses Ditolak')
@section('content')
	<span class="icon-[tabler--lock] mb-4 size-9 text-error" aria-hidden="true"></span>
	<h1 class="text-2xl font-semibold tracking-tight">Akses ditolak</h1>
	<p class="mt-2 text-sm text-base-content/70">
		{{ $exception->getMessage() ?: 'Akunmu belum punya akses ke halaman ini. Minta admin aplikasi untuk menambahkan role yang sesuai.' }}
	</p>

	<div class="mt-6 flex flex-col gap-2">
		<a href="{{ \App\Support\Sso::hubUrl('dashboard') }}" class="btn btn-primary btn-block">Buka halaman akun</a>
		@auth
			<form method="POST" action="{{ route('logout') }}">
				@csrf
				<button type="submit" class="btn btn-text btn-block">Keluar</button>
			</form>
		@endauth
	</div>
@endsection

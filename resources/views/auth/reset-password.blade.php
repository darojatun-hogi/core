@extends('layouts.auth.master')
@section('title', 'Reset Password')
@section('content')
	<div class="mb-6">
		<h1 class="text-2xl font-semibold tracking-tight">Buat password baru</h1>
		<p class="mt-1 text-sm text-base-content/70">Setelah disimpan, masuk kembali dengan password baru ini.</p>
	</div>

	<form action="{{ route('password.update') }}" method="POST" class="space-y-4">
		@csrf
		<input type="hidden" name="token" value="{{ $token }}">

		<x-field name="email" type="email" label="Email" :value="$email" required autocomplete="email" />
		<x-field name="password" type="password" label="Password baru" required autofocus autocomplete="new-password" />
		<x-field name="password_confirmation" type="password" label="Ulangi password baru" required
			autocomplete="new-password" />

		<button type="submit" class="btn btn-primary btn-block">Simpan password baru</button>
	</form>
@endsection

@extends('layouts.auth.master')
@section('title', 'Lupa Password')
@section('content')
	<div class="mb-6">
		<h1 class="text-2xl font-semibold tracking-tight">Lupa password</h1>
		<p class="mt-1 text-sm text-base-content/70">Masukkan email akunmu, kami kirim link untuk membuat password baru.</p>
	</div>

	@if (session('success'))
		<div class="alert alert-soft alert-success mb-4 flex items-start gap-3 text-sm" role="status">
			<span class="icon-[tabler--mail-check] size-5 shrink-0"></span>
			<span>{{ session('success') }}</span>
		</div>
	@endif

	<form action="{{ route('password.email') }}" method="POST" class="space-y-4">
		@csrf

		<x-field name="email" type="email" label="Email" required autofocus autocomplete="email"
			placeholder="email@example.com" />

		<button type="submit" class="btn btn-primary btn-block">Kirim link reset</button>
	</form>
@endsection

@section('footer')
	<a href="{{ route('login') }}" class="link link-primary font-medium">Kembali ke halaman masuk</a>
@endsection

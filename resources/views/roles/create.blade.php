@extends('layouts.dashboard.master')
@section('title', 'Tambah Role')
@section('content')
	<div class="mx-auto flex max-w-2xl flex-col gap-6">
		<x-page-header title="Tambah role" description="Pilih permission yang didapat setiap pengguna dengan role ini."
			:back="route('roles.index')" />

		<form action="{{ route('roles.store') }}" method="POST" class="rounded-box border border-base-content/10 bg-base-100">
			@csrf

			<div class="space-y-5 p-5 sm:p-6">
				@php($prefix = \App\Models\Role::appPrefix(\App\Support\ManagedApp::current()))
				<x-field name="name" label="Nama role" required autocomplete="off" :value="$prefix"
					:hint="'Wajib diawali ' . $prefix . ', contoh: ' . $prefix . 'editor'" />
				@include('roles._permissions-field', ['checked' => old('permissions', [])])
			</div>

			<div class="flex items-center justify-end gap-2 border-t border-base-content/10 px-5 py-4 sm:px-6">
				<a href="{{ route('roles.index') }}" class="btn btn-text">Batal</a>
				<button type="submit" class="btn btn-primary">Simpan role</button>
			</div>
		</form>
	</div>
@endsection

@extends('layouts.dashboard.master')
@section('title', 'Ubah Role')
@section('content')
	<div class="mx-auto flex max-w-2xl flex-col gap-6">
		<x-page-header :title="'Ubah ' . $role->name" description="Perubahan permission langsung berlaku untuk semua pengguna dengan role ini."
			:back="route('roles.show', $role)" />

		<form action="{{ route('roles.update', $role->id) }}" method="POST"
			class="rounded-box border border-base-content/10 bg-base-100">
			@csrf
			@method('PUT')

			<div class="space-y-5 p-5 sm:p-6">
				<x-field name="name" label="Nama role" :value="$role->name" required autocomplete="off"
					:readonly="$role->name === config('access.super_admin_role')"
					:hint="'Wajib diawali ' . \App\Models\Role::appPrefix(\App\Support\ManagedApp::current())" />
				@include('roles._permissions-field', ['checked' => old('permissions', $role->permissions->pluck('name')->all())])
			</div>

			<div class="flex items-center justify-end gap-2 border-t border-base-content/10 px-5 py-4 sm:px-6">
				<a href="{{ route('roles.show', $role) }}" class="btn btn-text">Batal</a>
				<button type="submit" class="btn btn-primary">Simpan perubahan</button>
			</div>
		</form>
	</div>
@endsection

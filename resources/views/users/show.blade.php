@extends('layouts.dashboard.master')
@section('title', $user->name)
@section('content')
	<div class="mx-auto flex max-w-2xl flex-col gap-6">
		<x-page-header :title="$user->name" :description="$user->email" :back="route('users.index')">
			@can('update', $user)
				<x-slot:actions>
					<a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
						<span class="icon-[tabler--pencil] size-5"></span>
						Ubah
					</a>
				</x-slot:actions>
			@endcan
		</x-page-header>

		<div class="rounded-box border border-base-content/10 bg-base-100">
			<dl class="divide-y divide-base-content/10">
				<div class="grid gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
					<dt class="text-sm text-base-content/70">Username</dt>
					<dd class="sm:col-span-2">{{ $user->username }}</dd>
				</div>
				<div class="grid gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
					<dt class="text-sm text-base-content/70">Email</dt>
					<dd class="break-all sm:col-span-2">{{ $user->email }}</dd>
				</div>
				<div class="grid gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
					<dt class="text-sm text-base-content/70">Role</dt>
					<dd class="flex flex-wrap gap-1 sm:col-span-2">
						@forelse ($user->roles as $role)
							<span class="badge badge-soft">{{ $role->name }}</span>
						@empty
							<span class="text-base-content/70">Belum punya role</span>
						@endforelse
					</dd>
				</div>
				<div class="grid gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
					<dt class="text-sm text-base-content/70">Dibuat</dt>
					<dd class="sm:col-span-2">{{ $user->created_at?->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
				</div>
				<div class="grid gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
					<dt class="text-sm text-base-content/70">Terakhir diubah</dt>
					<dd class="sm:col-span-2">{{ $user->updated_at?->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
				</div>
			</dl>
		</div>

		@can('delete', $user)
			<div class="flex flex-col gap-3 rounded-box border border-error/30 bg-base-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
				<div>
					<h2 class="font-semibold">Cabut akses ke aplikasi ini</h2>
					<p class="mt-1 text-sm text-base-content/70">Akun dan akses ke aplikasi lain tidak ikut terhapus.</p>
				</div>
				<button type="button" class="btn btn-error btn-outline shrink-0" aria-haspopup="dialog" aria-expanded="false"
					aria-controls="confirm-delete-modal" data-overlay="#confirm-delete-modal"
					data-delete-url="{{ route('users.destroy', $user) }}">
					Cabut akses
				</button>
			</div>

			<x-confirm-modal title="Cabut akses pengguna" confirm="Cabut akses">
				{{ $user->name }} tidak bisa lagi masuk ke aplikasi ini. Akunnya dan akses ke aplikasi lain tetap ada.
			</x-confirm-modal>
		@endcan
	</div>
@endsection

{{-- Daftar checkbox permission untuk form tambah/ubah role --}}
<fieldset class="w-full">
	<legend class="label-text mb-1.5 font-medium text-base-content">Permission</legend>
	@if ($permissions->isEmpty())
		<p class="rounded-box border border-dashed border-base-content/20 px-4 py-6 text-center text-sm text-base-content/70">
			Belum ada permission untuk aplikasi ini. Tambahkan lewat tombol Kelola permission di halaman Role & Permission.
		</p>
	@else
		<div class="grid gap-x-4 gap-y-1 rounded-box border border-base-content/10 p-2 sm:grid-cols-2">
			@foreach ($permissions as $permission)
				<label class="flex cursor-pointer items-center gap-2.5 rounded-field px-2 py-1.5 text-sm hover:bg-base-200">
					<input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
						class="checkbox checkbox-primary checkbox-sm" @checked(in_array($permission->name, $checked))>
					<span class="break-all">{{ $permission->name }}</span>
				</label>
			@endforeach
		</div>
	@endif
	@error('permissions')
		<p class="mt-1.5 text-sm text-error">{{ $message }}</p>
	@enderror
</fieldset>

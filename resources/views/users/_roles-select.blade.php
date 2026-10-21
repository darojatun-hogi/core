{{-- Pilihan role (multi) untuk form tambah/ubah pengguna --}}
<div class="w-full">
	<label for="roles" class="label-text mb-1.5 block font-medium text-base-content">Role</label>
	<select id="roles" name="roles[]" multiple required class="hidden"
		data-select='{
			"placeholder": "Pilih role",
			"toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
			"toggleClasses": "advance-select-toggle select-disabled:pointer-events-none select-disabled:opacity-40 @error('roles') is-invalid @enderror",
			"toggleCountText": "role dipilih",
			"toggleCountTextMinItems": 3,
			"dropdownClasses": "advance-select-menu",
			"optionClasses": "advance-select-option selected:select-active",
			"optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
			"extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content absolute top-1/2 end-3 -translate-y-1/2\"></span>"
		}'>
		@foreach ($roles as $role)
			<option value="{{ $role }}" @selected(in_array($role, $selected))>{{ $role }}</option>
		@endforeach
	</select>
	@error('roles')
		<p class="mt-1.5 text-sm text-error">{{ $message }}</p>
	@enderror
	@error('roles.*')
		<p class="mt-1.5 text-sm text-error">{{ $message }}</p>
	@enderror
	<p class="mt-1.5 text-sm text-base-content/70">Role menentukan menu dan aksi yang bisa dipakai pengguna ini.</p>
</div>

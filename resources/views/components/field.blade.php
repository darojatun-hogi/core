{{-- Input form dengan label, hint, dan pesan error validasi --}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'id' => null])

@php($id = $id ?? $name)

<div class="w-full">
	<label for="{{ $id }}" class="label-text mb-1.5 block font-medium text-base-content">{{ $label }}</label>
	<input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}"
		@if ($type !== 'password') value="{{ old($name, $value) }}" @endif
		{{ $attributes->class(['input w-full', 'is-invalid' => $errors->has($name)]) }}
		@error($name) aria-invalid="true" aria-describedby="{{ $id }}-error" @enderror />
	@error($name)
		<p id="{{ $id }}-error" class="mt-1.5 text-sm text-error">{{ $message }}</p>
	@else
		@if ($hint)
			<p class="mt-1.5 text-sm text-base-content/70">{{ $hint }}</p>
		@endif
	@enderror
</div>

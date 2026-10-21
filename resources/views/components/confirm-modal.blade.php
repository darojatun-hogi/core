{{-- Modal konfirmasi hapus. Tombol pemicu cukup memberi data-overlay="#{id}" dan data-delete-url, action form diisi oleh script di layout. --}}
@props(['id' => 'confirm-delete-modal', 'formId' => 'confirm-delete-form', 'title', 'confirm' => 'Hapus'])

<div id="{{ $id }}" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
	role="dialog" tabindex="-1" aria-labelledby="{{ $id }}-title">
	<div class="modal-dialog modal-dialog-sm">
		<div class="modal-content">
			<div class="modal-header">
				<h3 id="{{ $id }}-title" class="modal-title">{{ $title }}</h3>
				<button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Tutup"
					data-overlay="#{{ $id }}">
					<span class="icon-[tabler--x] size-4"></span>
				</button>
			</div>
			<div class="modal-body text-base-content/80">
				{{ $slot }}
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-text" data-overlay="#{{ $id }}">Batal</button>
				<form id="{{ $formId }}" method="POST">
					@csrf
					@method('DELETE')
					<button type="submit" class="btn btn-error">{{ $confirm }}</button>
				</form>
			</div>
		</div>
	</div>
</div>

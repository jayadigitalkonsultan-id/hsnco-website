<div class="client-logo-upload mb-4" data-logo-upload>
    <label class="form-label" for="clientLogoFile">Unggah logo baru</label>
    <div class="logo-dropzone" data-logo-dropzone>
        <x-admin.icon name="image" />
        <strong>Tarik dan lepaskan logo di sini</strong>
        <span>atau pilih gambar dari perangkat Anda</span>
        <input class="form-control mt-3" type="file" name="logo_file" id="clientLogoFile" accept="image/jpeg,image/png,image/webp" aria-describedby="clientLogoHelp clientLogoStatus">
    </div>
    <p class="field-help mt-2" id="clientLogoHelp">JPG, PNG, atau WebP. Maksimal 5 MB dan 8000 &times; 8000 piksel. Logo baru menggantikan pilihan Pustaka Media setelah klien disimpan.</p>
    <div class="logo-upload-preview" data-logo-preview hidden><img alt="Preview logo baru" width="240" height="140"><button class="btn btn-outline-secondary btn-sm" type="button" data-logo-remove>Batalkan logo baru</button></div>
    <p class="field-help" id="clientLogoStatus" role="status" aria-live="polite"></p>
    @error('logo_file')<div class="invalid-feedback d-block">{{ $message }} Pilih kembali file sebelum menyimpan.</div>@enderror
</div>
@once
@push('styles')
<style>
.logo-dropzone{display:flex;flex-direction:column;align-items:center;text-align:center;padding:24px 18px;border:2px dashed var(--admin-border);border-radius:12px;background:#faf8f2;gap:8px}.logo-dropzone>svg{width:28px;height:28px;color:var(--admin-gold-dark)}.logo-dropzone strong{font-size:14px}.logo-dropzone>span{font-size:12px;color:var(--admin-muted)}.logo-dropzone.is-dragging,.logo-dropzone:focus-within{border-color:var(--admin-gold);background:#f4edda}.logo-dropzone input{max-width:100%;font-size:13px}.logo-upload-preview{border:1px solid var(--admin-border);border-radius:10px;background:white;padding:12px;text-align:center;margin:12px 0}.logo-upload-preview img{display:block;max-width:100%;height:140px;object-fit:contain;margin:0 auto 12px}.logo-upload-preview[hidden]{display:none}
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-logo-upload]');
  if (!root) return;
  const input = root.querySelector('input[type=file]');
  const zone = root.querySelector('[data-logo-dropzone]');
  const preview = root.querySelector('[data-logo-preview]');
  const image = preview.querySelector('img');
  const status = root.querySelector('[role=status]');
  let objectUrl;
  let revision = 0;
  const clear = () => {
    revision++;
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = null; input.value = ''; input.setCustomValidity('');
    image.removeAttribute('src'); preview.hidden = true; status.textContent = '';
  };
  const show = () => {
    const file = input.files[0]; const current = ++revision;
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    preview.hidden = true; input.setCustomValidity(''); status.textContent = '';
    if (!file) return;
    const fail = message => { input.setCustomValidity(message); status.textContent = message; };
    if (!['image/jpeg','image/png','image/webp'].includes(file.type)) return fail('Pilih file JPG, PNG, atau WebP.');
    if (file.size > 5 * 1024 * 1024) return fail('Ukuran logo maksimal 5 MB.');
    objectUrl = URL.createObjectURL(file);
    const check = new Image();
    check.onload = () => {
      if (current !== revision) return;
      if (check.naturalWidth > 8000 || check.naturalHeight > 8000) return fail('Dimensi logo maksimal 8000 x 8000 piksel.');
      image.src = check.src; preview.hidden = false;
      status.textContent = file.name + ' siap diunggah. Klik Simpan klien untuk menyelesaikan.';
    };
    check.onerror = () => { if (current === revision) fail('File gambar tidak dapat dibaca.'); };
    check.src = objectUrl;
  };
  input.addEventListener('change', show);
  root.querySelector('[data-logo-remove]').addEventListener('click', clear);
  root.closest('form').querySelectorAll('input[name=image_id]').forEach(radio => radio.addEventListener('change', clear));
  let depth = 0;
  zone.addEventListener('dragenter', event => { event.preventDefault(); if (!input.disabled && !input.matches(':disabled')) { depth++; zone.classList.add('is-dragging'); } });
  zone.addEventListener('dragover', event => event.preventDefault());
  zone.addEventListener('dragleave', () => { if (--depth <= 0) { depth = 0; zone.classList.remove('is-dragging'); } });
  zone.addEventListener('drop', event => {
    event.preventDefault(); depth = 0; zone.classList.remove('is-dragging');
    if (input.matches(':disabled')) return;
    if (event.dataTransfer.files.length !== 1) { status.textContent = 'Pilih satu logo saja.'; return; }
    const transfer = new DataTransfer(); transfer.items.add(event.dataTransfer.files[0]); input.files = transfer.files; show();
  });
});
</script>
@endpush
@endonce

(() => {
  'use strict';
  const dialog = document.getElementById('deleteConfirmation');
  let pendingForm = null;
  let origin = null;
  document.querySelectorAll('[data-confirm-delete]').forEach(form => {
    form.addEventListener('submit', event => {
      if (form.dataset.confirmed === 'true' || !dialog || !window.bootstrap) return;
      event.preventDefault();
      pendingForm = form;
      origin = form.querySelector('button');
      document.getElementById('deleteConfirmationText').textContent = `Anda akan mengarsipkan “${form.dataset.confirmTitle}”.`;
      bootstrap.Modal.getOrCreateInstance(dialog).show();
    });
  });
  document.getElementById('confirmDeleteButton')?.addEventListener('click', () => {
    if (!pendingForm) return;
    pendingForm.dataset.confirmed = 'true';
    pendingForm.requestSubmit();
  });
  dialog?.addEventListener('hidden.bs.modal', () => {
    origin?.focus();
    pendingForm = null;
  });
})();

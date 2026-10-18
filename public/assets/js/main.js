(() => {
  'use strict';
  document.querySelectorAll('[data-enquiry-form]').forEach(form => {
    const fields = [...form.querySelectorAll('input[required], select[required], textarea[required]')];
    fields.forEach(field => {
      field.addEventListener('input', () => field.setCustomValidity(''));
      field.addEventListener('change', () => field.setCustomValidity(''));
      field.addEventListener('invalid', () => {
        if (field.validity.valueMissing) field.setCustomValidity('Kolom ini wajib diisi.');
        else if (field.type === 'email' && field.validity.typeMismatch) field.setCustomValidity('Masukkan alamat email yang valid.');
        else if (field.validity.tooLong) field.setCustomValidity('Teks melebihi batas panjang yang diizinkan.');
      });
    });
    form.addEventListener('submit', event => {
      fields.forEach(field => field.setCustomValidity(field.value && !field.value.trim() ? 'Masukkan teks selain spasi.' : ''));
      if (!form.reportValidity()) {
        event.preventDefault();
        return;
      }
      const button = form.querySelector('button[type="submit"]');
      if (button) button.disabled = true;
    });
  });
})();

(() => {
  'use strict';
  document.querySelectorAll('[data-demo-form]').forEach(form => {
    form.querySelector('button[type="submit"]').disabled = false;
    form.addEventListener('input', () => { form.querySelector('.form-feedback').textContent = ''; });
    const fields = [...form.querySelectorAll('input[required], textarea[required]')];
    fields.forEach(field => {
      field.addEventListener('input', () => field.setCustomValidity(''));
      field.addEventListener('invalid', () => {
        if (!field.value.trim()) field.setCustomValidity('Kolom ini wajib diisi.');
        else if (field.type === 'email' && field.validity.typeMismatch) field.setCustomValidity('Masukkan alamat email yang valid.');
        else if (field.validity.tooLong) field.setCustomValidity('Teks melebihi batas panjang yang diizinkan.');
      });
    });
    const validateWhitespace = () => fields.forEach(field => field.setCustomValidity(field.value && !field.value.trim() ? 'Masukkan teks selain spasi.' : ''));
    form.querySelector('button[type="submit"]').addEventListener('click', validateWhitespace);
    form.addEventListener('submit', event => {
      event.preventDefault(); validateWhitespace();
      if (!form.reportValidity()) return;
      const feedback = form.querySelector('.form-feedback');
      feedback.textContent = 'Permintaan contoh Anda valid. Ini hanya demonstrasi: tidak ada data yang dikirim atau disimpan.';
      feedback.setAttribute('tabindex', '-1'); feedback.focus();
    });
  });
})();

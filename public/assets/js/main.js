(() => {
  'use strict';
  document.querySelectorAll('[data-demo-form]').forEach(form => {
    form.querySelector('button[type="submit"]').disabled = false;
    form.addEventListener('input', () => { form.querySelector('.form-feedback').textContent = ''; });
    const fields = [...form.querySelectorAll('input[required], textarea[required]')];
    fields.forEach(field => field.addEventListener('input', () => field.setCustomValidity('')));
    const validateWhitespace = () => fields.forEach(field => field.setCustomValidity(field.value && !field.value.trim() ? 'Please enter a value other than spaces.' : ''));
    form.querySelector('button[type="submit"]').addEventListener('click', validateWhitespace);
    form.addEventListener('submit', event => {
      event.preventDefault(); validateWhitespace();
      if (!form.reportValidity()) return;
      const feedback = form.querySelector('.form-feedback');
      feedback.textContent = 'Your sample enquiry is valid. Demo only: nothing has been sent or stored.';
      feedback.setAttribute('tabindex', '-1'); feedback.focus();
    });
  });
})();

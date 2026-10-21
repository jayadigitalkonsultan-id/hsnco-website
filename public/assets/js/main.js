(() => {
  'use strict';
  const header = document.querySelector('.site-header');
  if (header) {
    const updateHeader = () => header.classList.toggle('is-scrolled', window.scrollY > 12);
    window.addEventListener('scroll', updateHeader, { passive: true });
    window.addEventListener('pageshow', updateHeader);
    updateHeader();
  }
  document.querySelectorAll('[data-enquiry-form]').forEach(form => {
    const fields = [...form.querySelectorAll('input[required], select[required], textarea[required]')];
    fields.forEach(field => {
      field.addEventListener('input', () => field.setCustomValidity(''));
      field.addEventListener('change', () => field.setCustomValidity(''));
      field.addEventListener('invalid', () => {
        if (field.validity.valueMissing) field.setCustomValidity('Please complete this field.');
        else if (field.type === 'email' && field.validity.typeMismatch) field.setCustomValidity('Please enter a valid email address.');
        else if (field.validity.tooLong) field.setCustomValidity('The text exceeds the permitted length.');
      });
    });
    form.addEventListener('submit', event => {
      fields.forEach(field => field.setCustomValidity(field.value && !field.value.trim() ? 'Please enter text other than spaces.' : ''));
      if (!form.reportValidity()) {
        event.preventDefault();
        return;
      }
      const button = form.querySelector('button[type="submit"]');
      if (button) button.disabled = true;
    });
  });
})();

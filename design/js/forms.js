// Validates the forms marked with data-validate and shows their success message.
// Nothing is sent: the website has to supply the processing.
(function () {
  const messages = {
    valueMissing: 'Please fill in this field.',
    typeMismatch: 'Please enter a valid email address.',
    tooShort: function (field) {
      return 'Please enter at least ' + field.minLength + ' characters.';
    },
    checkbox: 'Please confirm this to continue.',
    choice: 'Please choose an option.',
  };

  function messageFor(field) {
    const v = field.validity;
    if (field.type === 'checkbox' && v.valueMissing) {
      return messages.checkbox;
    }
    if (v.valueMissing && field.tagName === 'SELECT') {
      return messages.choice;
    }
    if (v.valueMissing) {
      return messages.valueMissing;
    }
    if (v.typeMismatch) {
      return messages.typeMismatch;
    }
    if (v.tooShort) {
      return messages.tooShort(field);
    }
    return '';
  }

  // A long label, like a consent text, names its field in the summary by data-label.
  function labelFor(field) {
    if (field.dataset.label) {
      return field.dataset.label;
    }
    const label = field.form.querySelector('label[for="' + field.id + '"]');
    return label ? label.textContent.replace('*', '').trim() : field.name;
  }

  function check(field) {
    const wrapper = field.closest('.form-field');
    const error = document.getElementById(field.id + '-error');
    const message = messageFor(field);
    if (wrapper) {
      wrapper.classList.toggle('has-error', message !== '');
    }
    if (error) {
      error.textContent = message;
    }
    field.setAttribute('aria-invalid', String(message !== ''));
    return message;
  }

  document.querySelectorAll('form[data-validate]').forEach(function (form) {
    const summary = form.querySelector('[data-error-summary]');
    const success = document.getElementById(form.dataset.success);
    const fields = Array.from(form.querySelectorAll('input, select, textarea')).filter(function (f) {
      return f.willValidate;
    });

    form.setAttribute('novalidate', '');

    fields.forEach(function (field) {
      field.addEventListener('blur', function () {
        if (field.value !== '' || field.getAttribute('aria-invalid') === 'true') {
          check(field);
        }
      });
    });

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      const failed = fields.filter(function (field) {
        return check(field) !== '';
      });

      if (summary) {
        summary.hidden = failed.length === 0;
        const list = summary.querySelector('ul');
        list.innerHTML = '';
        failed.forEach(function (field) {
          const item = document.createElement('li');
          const link = document.createElement('a');
          link.href = '#' + field.id;
          link.textContent = labelFor(field) + ': ' + messageFor(field);
          item.appendChild(link);
          list.appendChild(item);
        });
      }

      if (failed.length > 0) {
        (summary || failed[0]).focus();
        return;
      }

      if (success) {
        form.hidden = true;
        success.hidden = false;
        success.focus();
      }
    });
  });

  // Show and hide the password on the login page.
  document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
    const input = document.getElementById(button.getAttribute('aria-controls'));
    button.addEventListener('click', function () {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      button.textContent = show ? 'Hide' : 'Show';
      button.setAttribute('aria-pressed', String(show));
    });
  });
})();

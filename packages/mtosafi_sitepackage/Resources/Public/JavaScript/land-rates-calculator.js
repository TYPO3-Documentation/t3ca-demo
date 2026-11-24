// Land rates calculator (content element "Land rates calculator", design/js/calculator.js):
// the annual rate, the interest on late payment and the total, by the rule of
// design/docs/land-rates-calculation.md.
//
// The rates are the element's, not the script's: data-percent on each option of the
// land use, data-minimum and data-interest-per-month on the calculator. The texts it
// writes come translated from the template: data-label-no-value, and
// data-label-interest ("%1$s" the interest per month, "%2$s" the interest in money).
// Numbers and money are formatted in the language of the page.
(function () {
  const locale = document.documentElement.lang || 'en';

  document.querySelectorAll('[data-calculator]').forEach(function (calculator) {
    const form = calculator.querySelector('form');
    const use = form.elements.use;
    const settings = calculator.dataset;
    const minimum = Number(settings.minimum) || 0;
    const interestPerMonth = Number(settings.interestPerMonth) || 0;
    const maxMonths = Number(settings.maxMonths) || 36;
    const currency = new Intl.NumberFormat(locale, {
      style: 'currency',
      currency: settings.currency || 'KES',
      maximumFractionDigits: 0,
    });
    const percent = function (value) {
      return value.toLocaleString(locale) + ' %';
    };
    const out = function (name) {
      return calculator.querySelector('[data-output="' + name + '"]');
    };

    function calculate() {
      const data = new FormData(form);
      const option = use.options[use.selectedIndex];
      const percentOfValue = option ? Number(option.dataset.percent) || 0 : 0;
      const value = Math.max(0, Number(data.get('value')) || 0);
      const monthsLate = Math.max(0, Math.min(maxMonths, Math.floor(Number(data.get('months')) || 0)));

      // Whole shillings, as the rule says, so that the parts add up to the total shown.
      const rate = Math.round(Math.max(value * percentOfValue / 100, minimum));
      const interest = Math.round(rate * interestPerMonth / 100 * monthsLate);
      const total = rate + interest;

      out('percent').textContent = percent(percentOfValue);
      out('rate').textContent = currency.format(rate);
      out('interest').textContent = currency.format(interest);
      out('total').textContent = currency.format(total);

      out('warning').textContent =
        value === 0
          ? settings.labelNoValue
          : monthsLate > 0 && interest > 0
            ? settings.labelInterest
              .replace('%1$s', percent(interestPerMonth))
              .replace('%2$s', currency.format(interest))
            : '';
    }

    form.addEventListener('input', calculate);
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      calculate();
    });
    calculate();
  });
})();

// Land rates calculator: the annual rate, the interest on late payment and the total.
// The rates are the calculator's data attributes, so an editor can change them
// without touching this script.
(function () {
  document.querySelectorAll('[data-calculator]').forEach(function (calculator) {
    const form = calculator.querySelector('form');
    const rates = JSON.parse(calculator.dataset.rates);
    const currency = new Intl.NumberFormat('en-KE', {
      style: 'currency',
      currency: calculator.dataset.currency || 'KES',
      maximumFractionDigits: 0,
    });
    const out = function (name) {
      return calculator.querySelector('[data-output="' + name + '"]');
    };

    function calculate() {
      const data = new FormData(form);
      const use = data.get('use');
      const value = Math.max(0, Number(data.get('value')) || 0);
      const monthsLate = Math.max(0, Math.min(36, Math.floor(Number(data.get('months')) || 0)));

      const rate = Math.max(value * rates.percentOfValue[use] / 100, rates.minimum);
      const interest = rate * rates.interestPerMonth / 100 * monthsLate;
      const total = rate + interest;

      out('percent').textContent = rates.percentOfValue[use].toLocaleString('en') + ' %';
      out('rate').textContent = currency.format(rate);
      out('interest').textContent = currency.format(interest);
      out('total').textContent = currency.format(total);

      out('warning').textContent =
        value === 0
          ? 'Enter the rateable value from your valuation notice. Until then the minimum rate applies.'
          : monthsLate > 0
            ? 'Interest of ' + rates.interestPerMonth + ' % per month adds ' +
              currency.format(interest) + '. You can apply for a waiver of interest.'
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

// Search page: shows the term from ?q= and filters the results by type.
(function () {
  const results = document.querySelector('[data-search-results]');
  if (!results) {
    return;
  }

  const term = new URLSearchParams(window.location.search).get('q') || '';
  document.querySelectorAll('[data-search-term]').forEach(function (el) {
    el.textContent = term || 'land rates';
  });
  document.querySelectorAll('input[name="q"]').forEach(function (input) {
    input.value = term;
  });

  const filters = Array.from(document.querySelectorAll('[data-search-filter]'));
  const count = document.querySelector('[data-search-count]');
  const items = Array.from(results.children);

  function apply() {
    const active = filters.filter(function (f) {
      return f.checked;
    }).map(function (f) {
      return f.value;
    });
    let visible = 0;
    items.forEach(function (item) {
      const show = active.length === 0 || active.indexOf(item.dataset.type) !== -1;
      item.hidden = !show;
      visible += show ? 1 : 0;
    });
    count.textContent = visible + (visible === 1 ? ' result' : ' results');
  }

  filters.forEach(function (f) {
    f.addEventListener('change', apply);
  });
  apply();
})();

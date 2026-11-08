// Opens and closes the main navigation on small screens.
(function () {
  const toggle = document.querySelector('.nav-toggle');
  const nav = document.getElementById(toggle ? toggle.getAttribute('aria-controls') : '');
  if (!toggle || !nav) {
    return;
  }

  toggle.addEventListener('click', function () {
    const open = toggle.getAttribute('aria-expanded') !== 'true';
    toggle.setAttribute('aria-expanded', String(open));
    nav.classList.toggle('is-open', open);
  });
})();

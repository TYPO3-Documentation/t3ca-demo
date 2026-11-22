// Lightbox: a link with data-lightbox opens the image it links to in a modal dialog
// instead of on a page of its own. Links with the same data-lightbox value are one
// group, browsed with previous/next and the arrow keys. Escape, the close button and a
// click beside the image close it, and the focus goes back to the link.
//
// The link carries what the dialog shows and the labels it writes, translated by the
// template: data-lightbox-alt, data-lightbox-caption, data-label-close,
// data-label-previous, data-label-next and data-label-position ("%1$s" the image,
// "%2$s" the number of images).
(function () {
  let dialog = null;
  let group = [];
  let current = 0;
  let opener = null;

  function build(labels) {
    dialog = document.createElement('dialog');
    dialog.className = 'lightbox';
    dialog.innerHTML =
      '<figure class="lightbox__figure">' +
      '<img class="lightbox__image" alt="">' +
      '<figcaption class="lightbox__caption"></figcaption>' +
      '</figure>' +
      '<p class="lightbox__position" aria-live="polite"></p>' +
      '<button type="button" class="lightbox__button lightbox__button--close" data-lightbox-close>×</button>' +
      '<button type="button" class="lightbox__button lightbox__button--previous" data-lightbox-previous>‹</button>' +
      '<button type="button" class="lightbox__button lightbox__button--next" data-lightbox-next>›</button>';
    dialog.querySelector('[data-lightbox-close]').setAttribute('aria-label', labels.labelClose);
    dialog.querySelector('[data-lightbox-previous]').setAttribute('aria-label', labels.labelPrevious);
    dialog.querySelector('[data-lightbox-next]').setAttribute('aria-label', labels.labelNext);

    dialog.querySelector('[data-lightbox-close]').addEventListener('click', function () {
      dialog.close();
    });
    dialog.querySelector('[data-lightbox-previous]').addEventListener('click', function () {
      show(current - 1);
    });
    dialog.querySelector('[data-lightbox-next]').addEventListener('click', function () {
      show(current + 1);
    });
    // A click on the backdrop or beside the image reaches the dialog itself.
    dialog.addEventListener('click', function (event) {
      if (event.target === dialog || event.target.classList.contains('lightbox__figure')) {
        dialog.close();
      }
    });
    dialog.addEventListener('keydown', function (event) {
      if (group.length < 2) {
        return;
      }
      if (event.key === 'ArrowLeft') {
        show(current - 1);
      } else if (event.key === 'ArrowRight') {
        show(current + 1);
      }
    });
    dialog.addEventListener('close', function () {
      document.documentElement.classList.remove('has-lightbox');
      if (opener) {
        opener.focus();
      }
    });
    document.body.appendChild(dialog);
  }

  function show(index) {
    current = (index + group.length) % group.length;
    const link = group[current];
    const image = dialog.querySelector('.lightbox__image');
    const caption = dialog.querySelector('.lightbox__caption');
    image.src = link.href;
    image.alt = link.dataset.lightboxAlt || '';
    caption.textContent = link.dataset.lightboxCaption || '';
    caption.hidden = caption.textContent === '';
    const several = group.length > 1;
    dialog.querySelector('[data-lightbox-previous]').hidden = !several;
    dialog.querySelector('[data-lightbox-next]').hidden = !several;
    const position = dialog.querySelector('.lightbox__position');
    position.hidden = !several;
    position.textContent = several
      ? link.dataset.labelPosition.replace('%1$s', String(current + 1)).replace('%2$s', String(group.length))
      : '';
  }

  document.addEventListener('click', function (event) {
    const link = event.target.closest('a[data-lightbox]');
    if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
      return;
    }
    event.preventDefault();
    if (!dialog) {
      build(link.dataset);
    }
    opener = link;
    group = Array.from(document.querySelectorAll('a[data-lightbox]')).filter(function (candidate) {
      return candidate.dataset.lightbox === link.dataset.lightbox;
    });
    show(group.indexOf(link));
    document.documentElement.classList.add('has-lightbox');
    dialog.showModal();
    dialog.querySelector('[data-lightbox-close]').focus();
  });
})();

// Slider (content element "Slider", design/js/slider.js): previous/next, dots, arrow
// keys, autoplay with a pause button. Autoplay stays off where the visitor prefers
// reduced motion. The labels it writes come translated from the template:
// data-label-dot ("%1$s" the slide, "%2$s" the number of slides), data-label-pause and
// data-label-play.
(function () {
  document.querySelectorAll('[data-slider]').forEach(function (slider) {
    const track = slider.querySelector('.slider__track');
    const slides = Array.from(slider.querySelectorAll('.slider__slide'));
    const dotsContainer = slider.querySelector('.slider__dots');
    const pauseButton = slider.querySelector('[data-slider-pause]');
    const interval = Number(slider.dataset.interval || 6000);
    const labels = slider.dataset;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let current = 0;
    let timer = null;

    const dots = slides.map(function (slide, index) {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = 'slider__dot';
      dot.setAttribute('aria-label', labels.labelDot
        .replace('%1$s', String(index + 1))
        .replace('%2$s', String(slides.length)));
      dot.addEventListener('click', function () {
        show(index);
        stop();
      });
      dotsContainer.appendChild(dot);
      return dot;
    });

    function show(index) {
      current = (index + slides.length) % slides.length;
      track.style.transform = 'translateX(-' + current * 100 + '%)';
      slides.forEach(function (slide, i) {
        const active = i === current;
        slide.setAttribute('aria-hidden', String(!active));
        slide.querySelectorAll('a, button').forEach(function (el) {
          el.tabIndex = active ? 0 : -1;
        });
      });
      dots.forEach(function (dot, i) {
        dot.setAttribute('aria-current', String(i === current));
      });
    }

    function start() {
      if (reducedMotion || slides.length < 2) {
        return;
      }
      stop();
      timer = window.setInterval(function () {
        show(current + 1);
      }, interval);
      pauseButton.textContent = '❚❚';
      pauseButton.setAttribute('aria-label', labels.labelPause);
    }

    function stop() {
      window.clearInterval(timer);
      timer = null;
      pauseButton.textContent = '▶';
      pauseButton.setAttribute('aria-label', labels.labelPlay);
    }

    slider.querySelector('[data-slider-prev]').addEventListener('click', function () {
      show(current - 1);
      stop();
    });
    slider.querySelector('[data-slider-next]').addEventListener('click', function () {
      show(current + 1);
      stop();
    });
    pauseButton.addEventListener('click', function () {
      timer ? stop() : start();
    });
    slider.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowLeft') {
        show(current - 1);
        stop();
      } else if (event.key === 'ArrowRight') {
        show(current + 1);
        stop();
      }
    });
    // Moving the focus into the slider stops it, so nothing moves under the keyboard.
    slider.addEventListener('focusin', stop);

    show(0);
    reducedMotion ? stop() : start();
  });
})();

document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.getElementById('navToggle');
  var nav = document.getElementById('mainNav');
  if (!toggle || !nav) return;

  toggle.addEventListener('click', function () {
    var isOpen = nav.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });
});

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.slideshow').forEach(function (el) {
    var slides = el.querySelectorAll('.slideshow__slide');
    var dots = el.querySelectorAll('.slideshow__dot');
    var prevBtn = el.querySelector('.slideshow__arrow--prev');
    var nextBtn = el.querySelector('.slideshow__arrow--next');
    var caption = el.nextElementSibling && el.nextElementSibling.classList.contains('slideshow__caption')
      ? el.nextElementSibling
      : null;
    if (slides.length < 2) return;

    var current = 0;
    var timer = null;

    function show(index) {
      slides[current].classList.remove('is-active');
      dots[current] && dots[current].classList.remove('is-active');
      current = (index + slides.length) % slides.length;
      slides[current].classList.add('is-active');
      dots[current] && dots[current].classList.add('is-active');
      if (caption) caption.textContent = slides[current].dataset.caption || '';
    }

    function restartTimer() {
      if (timer) clearInterval(timer);
      timer = setInterval(function () { show(current + 1); }, 4500);
    }

    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () {
        show(i);
        restartTimer();
      });
    });

    if (prevBtn) prevBtn.addEventListener('click', function () { show(current - 1); restartTimer(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { show(current + 1); restartTimer(); });

    restartTimer();
  });
});

document.addEventListener('DOMContentLoaded', function () {
  var overlay = document.getElementById('lightboxOverlay');
  var overlayImg = document.getElementById('lightboxImage');
  var closeBtn = document.getElementById('lightboxClose');
  var prevBtn = document.getElementById('lightboxPrev');
  var nextBtn = document.getElementById('lightboxNext');
  if (!overlay || !overlayImg || !closeBtn) return;

  // Every clickable photo belongs to a "gallery" (the carousel/grid/photo
  // box it lives in), so the lightbox arrows can step through the same set
  // of photos the visitor was already browsing, not just close and reopen.
  var currentGroup = [];
  var currentIndex = 0;

  function render() {
    var img = currentGroup[currentIndex];
    overlayImg.src = img.src;
    overlayImg.alt = img.alt || '';
    var multi = currentGroup.length > 1;
    if (prevBtn) prevBtn.hidden = !multi;
    if (nextBtn) nextBtn.hidden = !multi;
  }

  function openLightbox(group, index) {
    currentGroup = group;
    currentIndex = index;
    render();
    overlay.classList.add('is-open');
  }

  function closeLightbox() {
    overlay.classList.remove('is-open');
    overlayImg.src = '';
  }

  function showNext() {
    if (currentGroup.length < 2) return;
    currentIndex = (currentIndex + 1) % currentGroup.length;
    render();
  }

  function showPrev() {
    if (currentGroup.length < 2) return;
    currentIndex = (currentIndex - 1 + currentGroup.length) % currentGroup.length;
    render();
  }

  var galleryContainers = ['.slideshow', '.showcase-grid', '.subsection-photo'];
  document.querySelectorAll('.slideshow__slide, .showcase-grid img, .subsection-photo img').forEach(function (img) {
    var container = img.closest(galleryContainers.join(', '));
    var group = container ? Array.prototype.slice.call(container.querySelectorAll('img')) : [img];
    img.style.cursor = 'pointer';
    img.addEventListener('click', function () {
      openLightbox(group, group.indexOf(img));
    });
  });

  closeBtn.addEventListener('click', closeLightbox);
  if (prevBtn) prevBtn.addEventListener('click', showPrev);
  if (nextBtn) nextBtn.addEventListener('click', showNext);
  overlay.addEventListener('click', function (e) {
    if (e.target === overlay) closeLightbox();
  });
  document.addEventListener('keydown', function (e) {
    if (!overlay.classList.contains('is-open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowRight') showNext();
    if (e.key === 'ArrowLeft') showPrev();
  });
});

document.addEventListener('DOMContentLoaded', function () {
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Infinite-feeling loop: the track holds the real slides followed by a
  // hidden clone of the same slides (see includes/vendor-carousel.php).
  // Scrolling forward past the real slides lands on the clone, which looks
  // identical, then we snap back to the real position with no transition
  // while nobody's looking. Same trick in reverse for the prev button.
  document.querySelectorAll('.vendor-carousel').forEach(function (el) {
    var viewport = el.querySelector('.vendor-carousel__viewport');
    var track = el.querySelector('.vendor-carousel__track');
    var slides = el.querySelectorAll('.vendor-carousel__slide');
    var prevBtn = el.querySelector('.vendor-carousel__arrow--prev');
    var nextBtn = el.querySelector('.vendor-carousel__arrow--next');
    var realCount = parseInt(track.dataset.realCount, 10) || 0;
    if (!realCount || slides.length < realCount * 2) return;

    var index = 0;
    var stepPx = 0;
    var canLoop = false;
    var timer = null;

    function apply() {
      track.style.transform = 'translateX(-' + (index * stepPx) + 'px)';
    }

    function jump(newIndex) {
      track.classList.add('is-jumping');
      index = newIndex;
      apply();
      void track.offsetHeight;
      track.classList.remove('is-jumping');
    }

    function measure() {
      stepPx = slides[1].offsetLeft - slides[0].offsetLeft;
      var visibleCount = stepPx > 0 ? Math.max(1, Math.round(viewport.clientWidth / stepPx)) : 1;
      canLoop = realCount > visibleCount;
      jump(canLoop ? index % realCount : 0);

      if (prevBtn) prevBtn.style.visibility = canLoop ? '' : 'hidden';
      if (nextBtn) nextBtn.style.visibility = canLoop ? '' : 'hidden';
    }

    function next() {
      if (!canLoop) return;
      index += 1;
      apply();
      // With reduced motion the track has no CSS transition, so
      // transitionend below never fires, reset synchronously instead.
      if (reduceMotion && index >= realCount) jump(index - realCount);
    }

    function prev() {
      if (!canLoop) return;
      if (index <= 0) jump(index + realCount);
      index -= 1;
      apply();
    }

    track.addEventListener('transitionend', function (e) {
      if (e.propertyName === 'transform' && index >= realCount) {
        jump(index - realCount);
      }
    });

    function restart() {
      if (reduceMotion || !canLoop) return;
      if (timer) clearInterval(timer);
      var interval = parseInt(el.dataset.interval, 10) || 5000;
      timer = setInterval(next, interval);
    }
    function stop() {
      if (timer) { clearInterval(timer); timer = null; }
    }

    if (nextBtn) nextBtn.addEventListener('click', function () { next(); restart(); });
    if (prevBtn) prevBtn.addEventListener('click', function () { prev(); restart(); });

    el.addEventListener('mouseenter', stop);
    el.addEventListener('mouseleave', restart);
    el.addEventListener('focusin', stop);
    el.addEventListener('focusout', restart);
    window.addEventListener('resize', measure);

    measure();
    restart();
  });
});

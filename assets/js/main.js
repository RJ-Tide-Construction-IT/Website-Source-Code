// Phone menu (the three-bar button in the header).
document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.getElementById('navToggle');
  var nav = document.getElementById('mainNav');
  var header = toggle && toggle.closest('.site-header');
  if (!header || !nav) return;

  // One place opens and closes the menu, so the class that shows it and
  // aria-expanded (read out by screen readers, and what turns the bars into
  // an X in style.css) always agree.
  function setOpen(open) {
    nav.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  toggle.addEventListener('click', function () {
    setOpen(!nav.classList.contains('is-open'));
  });

  // Escape closes it and puts keyboard focus back on the menu button, so
  // keyboard users aren't left on a link that just disappeared.
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && nav.classList.contains('is-open')) {
      setOpen(false);
      toggle.focus();
    }
  });

  // Tapping anywhere on the page outside the header closes it too.
  document.addEventListener('click', function (e) {
    if (nav.classList.contains('is-open') && !header.contains(e.target)) setOpen(false);
  });
});

// Light/dark switch (the sun/moon button in the header). The site follows the
// device's light/dark setting until the visitor presses the button. Their
// choice is then kept for a year in a "theme" cookie, which PHP also reads
// (site_theme() in includes/config.php) so every page arrives in the right
// colours. Picking the same as the device deletes the cookie instead, so the
// site goes back to following the device.
document.addEventListener('DOMContentLoaded', function () {
  var button = document.getElementById('themeToggle');
  if (!button) return;
  var root = document.documentElement;
  var deviceDark = window.matchMedia('(prefers-color-scheme: dark)');
  // When the white-lettered logo shows: always (picked dark), never (picked
  // light), or when the device is dark (no choice). Same as theme_logo() in header.php.
  var darkLogoMedia = { dark: 'all', light: 'not all', '': '(prefers-color-scheme: dark)' };

  // The saved choice: 'light', 'dark', or '' for "follow the device".
  function savedChoice() {
    var match = document.cookie.match(/(?:^|;\s*)theme=(light|dark)(?:;|$)/);
    return match ? match[1] : '';
  }

  // What the page actually shows for a choice.
  function shown(choice) {
    return choice || (deviceDark.matches ? 'dark' : 'light');
  }

  // Puts the colours, logo(s) and the button's spoken label in line with a choice.
  function apply(choice) {
    if (choice) root.setAttribute('data-theme', choice);
    else root.removeAttribute('data-theme');
    document.querySelectorAll('source[data-dark-logo]').forEach(function (source) {
      source.media = darkLogoMedia[choice];
    });
    button.setAttribute('aria-label', shown(choice) === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
  }

  button.addEventListener('click', function () {
    var next = shown(root.getAttribute('data-theme') || '') === 'dark' ? 'light' : 'dark';
    var choice = next === shown('') ? '' : next;
    document.cookie = 'theme=' + choice + '; max-age=' + (choice ? 31536000 : 0) + '; path=/; samesite=lax' +
      (location.protocol === 'https:' ? '; secure' : '');
    apply(choice);
  });

  // If the device switches light/dark while there's no choice, what's showing
  // changes, so the button's label follows.
  deviceDark.addEventListener('change', function () { apply(root.getAttribute('data-theme') || ''); });

  // Going Back can show a saved copy of this page from before the button was
  // pressed on another page, so check the cookie again.
  window.addEventListener('pageshow', function (e) { if (e.persisted) apply(savedChoice()); });

  apply(savedChoice());
});

document.addEventListener('DOMContentLoaded', function () {
  // Slideshows advance on their own, but not for visitors who've asked their
  // device for reduced motion, and they pause while the mouse is over one or
  // keyboard focus is inside it (same as the vendor carousel). Only the slide
  // that's showing can be reached with Tab.
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  document.querySelectorAll('.slideshow').forEach(function (el) {
    var slides = el.querySelectorAll('.slideshow__slide');
    var dots = el.querySelectorAll('.slideshow__dot');
    var prevBtn = el.querySelector('.slideshow__arrow--prev');
    var nextBtn = el.querySelector('.slideshow__arrow--next');
    if (slides.length < 2) return;

    var current = 0;
    var timer = null;
    var hovering = false;
    var focused = false;

    // Photos that open in the photo viewer can be reached with Tab, but only the one showing.
    function setTab(slide, on) {
      if (slide.hasAttribute('role')) slide.tabIndex = on ? 0 : -1;
    }

    function show(index) {
      slides[current].classList.remove('is-active');
      setTab(slides[current], false);
      dots[current] && dots[current].classList.remove('is-active');
      current = (index + slides.length) % slides.length;
      slides[current].classList.add('is-active');
      setTab(slides[current], true);
      dots[current] && dots[current].classList.add('is-active');
    }

    function restartTimer() {
      if (timer) clearInterval(timer);
      timer = null;
      if (reduceMotion || hovering || focused) return;
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

    el.addEventListener('mouseenter', function () { hovering = true; restartTimer(); });
    el.addEventListener('mouseleave', function () { hovering = false; restartTimer(); });
    el.addEventListener('focusin', function () { focused = true; restartTimer(); });
    el.addEventListener('focusout', function () { focused = false; restartTimer(); });

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

  var lastFocus = null;

  function render() {
    var img = currentGroup[currentIndex];
    // Small thumbnail copies carry the full-size photo's address in data-full.
    overlayImg.src = img.dataset.full || img.src;
    overlayImg.alt = img.alt || '';
    var multi = currentGroup.length > 1;
    if (prevBtn) prevBtn.hidden = !multi;
    if (nextBtn) nextBtn.hidden = !multi;
  }

  function openLightbox(group, index) {
    lastFocus = document.activeElement;
    currentGroup = group;
    currentIndex = index;
    render();
    overlay.classList.add('is-open');
    closeBtn.focus();
  }

  function closeLightbox() {
    overlay.classList.remove('is-open');
    overlayImg.src = '';
    if (lastFocus && lastFocus.focus) lastFocus.focus();
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
    // Keyboard users can Tab to each photo and open it with Enter or Space.
    // Slideshow photos that aren't showing are skipped (the slideshow code
    // above moves this along as slides change).
    img.setAttribute('role', 'button');
    img.tabIndex = img.classList.contains('slideshow__slide') && !img.classList.contains('is-active') ? -1 : 0;
    img.addEventListener('click', function () {
      openLightbox(group, group.indexOf(img));
    });
    img.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        openLightbox(group, group.indexOf(img));
      }
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
    // Keep Tab inside the open photo viewer.
    if (e.key === 'Tab') {
      var focusable = [closeBtn, prevBtn, nextBtn].filter(function (b) { return b && !b.hidden; });
      var i = focusable.indexOf(document.activeElement);
      e.preventDefault();
      focusable[(i + (e.shiftKey ? focusable.length - 1 : 1)) % focusable.length].focus();
    }
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

document.addEventListener('DOMContentLoaded', function () {
  var banner = document.getElementById('cookieBanner');
  var dismissBtn = document.getElementById('cookieBannerDismiss');
  if (!banner || !dismissBtn) return;

  var seen = false;
  try { seen = localStorage.getItem('cookieNoticeSeen') === '1'; } catch (e) {}
  if (!seen) banner.hidden = false;

  dismissBtn.addEventListener('click', function () {
    banner.hidden = true;
    try { localStorage.setItem('cookieNoticeSeen', '1'); } catch (e) {}
  });
});

// Contact form: needs at least one way to reach the visitor, email or phone.
// contact-handler.php checks the same thing server-side.
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('contact-form');
  var email = document.getElementById('email');
  var phone = document.getElementById('phone');
  if (!form || !email || !phone) return;

  function check() {
    var missing = email.value.trim() === '' && phone.value.trim() === '';
    email.setCustomValidity(missing ? 'Please enter an email address or phone number.' : '');
  }
  email.addEventListener('input', check);
  phone.addEventListener('input', check);
  check();
});

// Videos marked data-play-when-visible: nothing downloads until the visitor
// scrolls to one, then it plays muted (and pauses when scrolled away). Visitors
// who've asked their device for reduced motion just get the play button.
document.addEventListener('DOMContentLoaded', function () {
  var videos = document.querySelectorAll('video[data-play-when-visible]');
  if (!videos.length || !('IntersectionObserver' in window)) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.play().catch(function () {});
      } else {
        entry.target.pause();
      }
    });
  }, { threshold: 0.5 });
  videos.forEach(function (video) { observer.observe(video); });
});

// File uploads (job application resume, dashboard documents): catch a
// too-big or wrong-type file as soon as it's picked, using the input's
// data-max-bytes and data-extensions (e.g. "pdf,doc,docx"). The server rejects
// these too, but by then the person is sent back and loses what they typed.
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('input[type="file"][data-max-bytes]').forEach(function (input) {
    var extensions = (input.dataset.extensions || '').split(',');
    input.addEventListener('change', function () {
      var file = input.files[0];
      var message = '';
      if (file && extensions[0] && extensions.indexOf(file.name.split('.').pop().toLowerCase()) === -1) {
        message = 'That type of file can\'t be uploaded here. Allowed: ' + extensions.join(', ').toUpperCase() + '.';
      } else if (file && file.size > Number(input.dataset.maxBytes)) {
        message = 'That file is too large (' + (file.size / 1048576).toFixed(1) + 'MB). The limit is '
          + Math.round(input.dataset.maxBytes / 1048576) + 'MB.';
      }
      input.setCustomValidity(message);
      if (message) input.reportValidity();
    });
  });
});

// Cloudflare Turnstile (includes/turnstile.php): don't submit a form until its
// "Verify you are human" check is done, so a visitor isn't sent back to an
// empty form, painful on the long job application. Pressing Send too early
// shows a message just under the check (read out by screen readers); it's
// removed the next time they press Send.
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.cf-turnstile').forEach(function (widget) {
    var form = widget.closest('form');
    if (!form) return;
    var message = null;
    form.addEventListener('submit', function (e) {
      var token = form.querySelector('[name="cf-turnstile-response"]');
      if (message) { message.remove(); message = null; }
      if (token && token.value !== '') return;
      e.preventDefault();
      // A fresh message each time, so screen readers announce it again.
      message = document.createElement('p');
      message.className = 'form-note form-note--error';
      message.setAttribute('role', 'alert');
      message.textContent = 'Please complete the "Verify you are human" check before submitting.';
      // turnstile.php wraps the check in a .form-field; the message goes right after it, above the Send button.
      widget.parentNode.after(message);
    });
  });
});

// Contact form and job application: once a submission is really on its way,
// grey out the button and show "Sending..." so nobody taps twice and sends a
// duplicate. Only buttons marked data-sending do this, because some dashboard
// forms keep you on the same page (previews, downloads) and would be left
// with a dead button. Kept below the Turnstile block above so it can skip any
// submission that block stopped.
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('button[data-sending]').forEach(function (button) {
    var label = button.textContent;
    if (!button.form) return;

    button.form.addEventListener('submit', function (e) {
      if (e.defaultPrevented) return;
      button.disabled = true;
      button.textContent = 'Sending\u2026';
    });

    // Pressing Back to the form can bring the page back exactly as it was
    // left, greyed-out button included, so switch it back on whenever the
    // page is shown.
    window.addEventListener('pageshow', function () {
      button.disabled = false;
      button.textContent = label;
    });
  });
});

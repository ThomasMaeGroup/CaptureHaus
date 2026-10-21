// 360 capture microsite interactions
document.documentElement.classList.add('js');

(() => {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Header state + sticky mobile CTA ---------- */
  const header = document.querySelector('[data-header]');
  const stickyCta = document.querySelector('[data-sticky-cta]');
  const contact = document.getElementById('contact');
  let contactInView = false;

  const onScroll = () => {
    const y = window.scrollY;
    header.classList.toggle('is-scrolled', y > 40);
    stickyCta.classList.toggle('is-visible', y > window.innerHeight * 0.8 && !contactInView);
  };
  new IntersectionObserver(([e]) => { contactInView = e.isIntersecting; onScroll(); }, { threshold: 0.05 }).observe(contact);
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ---------- Mobile nav ---------- */
  const toggle = document.querySelector('[data-nav-toggle]');
  const nav = document.getElementById('site-nav');
  const setNav = (open) => {
    toggle.setAttribute('aria-expanded', String(open));
    nav.classList.toggle('is-open', open);
    document.body.classList.toggle('nav-open', open);
  };
  toggle.addEventListener('click', () => setNav(toggle.getAttribute('aria-expanded') !== 'true'));
  nav.addEventListener('click', (e) => { if (e.target.closest('a')) setNav(false); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setNav(false); });

  /* ---------- Lazy-load + autoplay videos only while on screen ---------- */
  const videos = document.querySelectorAll('[data-lazy-video]');
  const videoObserver = new IntersectionObserver((entries) => {
    entries.forEach(({ target: v, isIntersecting }) => {
      if (isIntersecting) {
        if (!v.dataset.loaded) {
          v.querySelectorAll('source[data-src]').forEach((s) => { s.src = s.dataset.src; });
          v.load();
          v.dataset.loaded = '1';
        }
        if (!reduceMotion) v.play().catch(() => {});
      } else if (!v.paused) {
        v.pause();
      }
    });
  }, { rootMargin: '200px 0px' });
  videos.forEach((v) => videoObserver.observe(v));

  const heroVideo = document.querySelector('.hero__media');
  if (reduceMotion && heroVideo) heroVideo.pause();

  /* ---------- Slideshows: cross-fade between views while on screen ---------- */
  document.querySelectorAll('[data-slideshow]').forEach((show) => {
    const slides = [...show.children];
    let i = 0;
    let inView = false;
    slides[0].classList.add('is-active');
    if (reduceMotion || slides.length < 2) return;
    new IntersectionObserver(([e]) => { inView = e.isIntersecting; }).observe(show);
    const timer = setInterval(() => {
      if (!show.isConnected) return clearInterval(timer);
      if (!inView || document.hidden) return;
      slides[i].classList.remove('is-active');
      i = (i + 1) % slides.length;
      slides[i].classList.add('is-active');
    }, 5500);
  });

  /* ---------- Live tour / 3D plan embeds: load only on click ---------- */
  document.querySelectorAll('[data-embed]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const media = btn.closest('.feature__media');
      const frame = document.createElement('iframe');
      frame.src = btn.dataset.embed;
      frame.title = btn.dataset.embedTitle;
      frame.allow = 'fullscreen; xr-spatial-tracking; gyroscope; accelerometer';
      frame.allowFullscreen = true;
      media.querySelector('video')?.pause();
      media.querySelector('[data-slideshow]')?.remove();
      media.appendChild(frame);
      btn.remove();
      frame.focus();
    });
  });

  /* ---------- Hero: live tour in a full-screen dialog ---------- */
  const tourModal = document.querySelector('[data-tour-modal]');
  const tourOpen = document.querySelector('[data-tour-open]');
  if (tourModal && tourOpen) {
    const tourFrame = tourModal.querySelector('iframe');
    tourOpen.addEventListener('click', () => {
      if (!tourFrame.src) tourFrame.src = tourFrame.dataset.src;
      tourModal.showModal();
    });
    tourModal.querySelector('[data-tour-close]').addEventListener('click', () => tourModal.close());
    tourModal.addEventListener('click', (e) => { if (e.target === tourModal) tourModal.close(); });
  }

  /* ---------- Reveal on scroll ---------- */
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting) { e.target.classList.add('is-in'); revealObserver.unobserve(e.target); }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal').forEach((el) => revealObserver.observe(el));

  /* ---------- Before / after compare ---------- */
  document.querySelectorAll('[data-compare]').forEach((fig) => {
    const range = fig.querySelector('input[type="range"]');
    const set = () => fig.style.setProperty('--pos', range.value + '%');
    range.addEventListener('input', set);
    set();
  });

  /* ---------- Gallery: duplicate images for a seamless loop ---------- */
  const track = document.querySelector('.gallery__track');
  if (track) {
    [...track.children].forEach((img) => {
      const clone = img.cloneNode(true);
      clone.alt = '';
      clone.setAttribute('aria-hidden', 'true');
      track.appendChild(clone);
    });
  }

  /* ---------- CTA buttons prefill the form ---------- */
  const form = document.querySelector('[data-form]');
  const sectorSelect = form.querySelector('[data-sector-select]');

  document.querySelectorAll('a[href="#contact"]').forEach((a) => {
    a.addEventListener('click', () => {
      const { sector, service } = a.dataset;
      if (sector) sectorSelect.value = sector;
      if (service) {
        const box = form.querySelector(`input[name="services[]"][value="${CSS.escape(service)}"]`);
        if (box) box.checked = true;
      }
      // Draw the eye to the form once the scroll lands
      setTimeout(() => {
        form.classList.remove('is-highlight');
        void form.offsetWidth;
        form.classList.add('is-highlight');
      }, reduceMotion ? 0 : 600);
    });
  });

  /* ---------- Form validation + submit ---------- */
  const status = form.querySelector('[data-form-status]');
  const setStatus = (msg, kind) => {
    status.textContent = msg;
    status.className = 'form__status' + (kind ? ' is-' + kind : '');
  };

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    let firstInvalid = null;
    form.querySelectorAll('[required]').forEach((input) => {
      const ok = input.checkValidity();
      input.closest('.field').classList.toggle('is-invalid', !ok);
      if (!ok && !firstInvalid) firstInvalid = input;
    });
    if (firstInvalid) {
      setStatus('Please fill in the highlighted fields.', 'error');
      firstInvalid.focus();
      return;
    }

    // Endpoint not configured yet: show the success state so the flow can be reviewed
    if (form.action.includes('YOUR_FORM_ID')) {
      setStatus('Thanks! We’ll be in touch shortly with your quote. (Demo mode: form endpoint not yet connected.)', 'ok');
      form.reset();
      return;
    }

    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    setStatus('Sending…');
    try {
      const res = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
      if (!res.ok) throw new Error(res.status);
      setStatus('Thanks! We’ll be in touch shortly with your quote.', 'ok');
      form.reset();
    } catch {
      setStatus('Sorry, something went wrong. Please try again or email us directly.', 'error');
    } finally {
      btn.disabled = false;
    }
  });

  form.addEventListener('input', (e) => {
    const field = e.target.closest('.field');
    if (field && field.classList.contains('is-invalid') && e.target.checkValidity()) field.classList.remove('is-invalid');
  });

  const year = document.querySelector('[data-year]');
  if (year) year.textContent = new Date().getFullYear();
})();

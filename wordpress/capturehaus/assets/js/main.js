// CaptureHaus theme interactions
document.documentElement.classList.add('js');

(() => {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Header state + sticky mobile CTA ---------- */
  const header = document.querySelector('[data-header]');
  const stickyCta = document.querySelector('[data-sticky-cta]');
  const contact = document.getElementById('contact');
  // Pages without a full-screen hero keep the solid (white) header from the start
  const solidHeader = !document.querySelector('.hero');
  let contactInView = false;

  const onScroll = () => {
    const y = window.scrollY;
    header.classList.toggle('is-scrolled', solidHeader || y > 40);
    stickyCta?.classList.toggle('is-visible', y > window.innerHeight * 0.8 && !contactInView);
  };
  if (contact) new IntersectionObserver(([e]) => { contactInView = e.isIntersecting; onScroll(); }, { threshold: 0.05 }).observe(contact);
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
  if (reduceMotion && heroVideo?.pause) heroVideo.pause();

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

  /* ---------- CTA buttons draw the eye to the (HubSpot) form ---------- */
  const form = document.querySelector('[data-form]');
  if (form) document.querySelectorAll('a[href$="#contact"]').forEach((a) => {
    a.addEventListener('click', () => {
      setTimeout(() => {
        form.classList.remove('is-highlight');
        void form.offsetWidth;
        form.classList.add('is-highlight');
      }, reduceMotion ? 0 : 600);
    });
  });

  const year = document.querySelector('[data-year]');
  if (year) year.textContent = new Date().getFullYear();
})();

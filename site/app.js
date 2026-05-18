// Общая логика сайта: мобильное меню, модалка контакта, отправка /api/lead, простые горизонтальные ленты.
(function () {
  const toggle = document.querySelector('[data-nav-toggle]');
  const menu = document.querySelector('[data-nav-menu]');
  if (toggle && menu) {
    toggle.addEventListener('click', () => {
      const open = menu.classList.toggle('open');
      document.body.classList.toggle('nav-open', open);
      toggle.setAttribute('aria-expanded', String(open));
    });
  }
})();

(function () {
  const modal = document.getElementById('leadModal');
  const form = document.getElementById('leadForm');
  const status = document.getElementById('leadStatus');
  const nameI = document.getElementById('lead-name');
  const phoneI = document.getElementById('lead-phone');
  if (!modal || !form || !status || !nameI || !phoneI) return;

  let lastFocus = null;
  function open(e) {
    if (e) e.preventDefault();
    lastFocus = document.activeElement;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    setTimeout(() => nameI.focus(), 50);
  }
  function close() {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
  }

  document.querySelectorAll('[data-lead-open]').forEach(el => el.addEventListener('click', open));
  document.querySelectorAll('[data-lead-close]').forEach(el => el.addEventListener('click', close));
  modal.addEventListener('click', e => { if (e.target === modal) close(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });

  phoneI.addEventListener('input', () => {
    let v = phoneI.value.replace(/[^\d+]/g, '');
    if (v.length > 16) v = v.slice(0, 16);
    phoneI.value = v;
  });

  function setStatus(msg, kind) {
    status.textContent = msg || '';
    status.className = 'modal-status' + (kind ? ' ' + kind : '');
  }

  let submitting = false;
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (submitting) return;
    setStatus('');

    const name = nameI.value.trim();
    const phone = phoneI.value.trim();
    const consent = form.elements.consent.checked;
    const hp = form.elements.company.value;

    if (name.length < 2) return setStatus('Укажите имя', 'err');
    if (phone.replace(/\D/g, '').length < 6) return setStatus('Укажите телефон', 'err');
    if (!consent) return setStatus('Нужно согласие на обработку данных', 'err');

    submitting = true;
    setStatus('Отправляем...', '');

    const payload = JSON.stringify({ name, phone, company: hp, page: location.pathname });
    const endpoints = ['/mail.php', '/api/lead']; // 1) PHP (Beget), 2) Node (Timeweb)

    let data = null;
    let lastErr = null;
    try {
      for (const url of endpoints) {
        try {
          const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: payload
          });
          // 404 / 405 → этот бэкенд не активен, пробуем следующий
          if (res.status === 404 || res.status === 405) continue;
          data = await res.json().catch(() => ({}));
          if (!res.ok || !data.ok) throw new Error(data.error || 'Ошибка отправки');
          lastErr = null;
          break;
        } catch (err) {
          lastErr = err;
          // сетевая ошибка — пробуем следующий endpoint
        }
      }
      if (!data || !data.ok) throw lastErr || new Error('Ошибка отправки');

      setStatus('Контакт отправлен. Мы свяжемся с вами.', 'ok');
      form.reset();
      setTimeout(close, 1800);
    } catch (err) {
      setStatus(err.message || 'Не удалось отправить', 'err');
    } finally {
      submitting = false;
    }
  });
})();

(function () {
  document.querySelectorAll('.press-strip').forEach((section) => {
    const rail = section.querySelector('[data-rail]');
    const prev = section.querySelector('[data-rail-prev]');
    const next = section.querySelector('[data-rail-next]');
    if (!rail || !prev || !next) return;
    const step = () => Math.max(280, Math.round(rail.clientWidth * 0.75));
    prev.addEventListener('click', () => rail.scrollBy({ left: -step(), behavior: 'smooth' }));
    next.addEventListener('click', () => rail.scrollBy({ left: step(), behavior: 'smooth' }));
  });
})();

(function () {
  document.querySelectorAll('.photo-section').forEach((section) => {
    const rail = section.querySelector('[data-gallery-rail]');
    const prev = section.querySelector('[data-gallery-prev]');
    const next = section.querySelector('[data-gallery-next]');
    if (!rail || !prev || !next) return;
    const step = () => Math.max(300, Math.round(rail.clientWidth * 0.8));
    prev.addEventListener('click', () => rail.scrollBy({ left: -step(), behavior: 'smooth' }));
    next.addEventListener('click', () => rail.scrollBy({ left: step(), behavior: 'smooth' }));
  });

  const triggers = Array.from(document.querySelectorAll('[data-lightbox-src]'));
  const lightbox = document.querySelector('[data-lightbox]');
  const image = document.querySelector('[data-lightbox-image]');
  const caption = document.querySelector('[data-lightbox-caption]');
  const closeBtn = document.querySelector('[data-lightbox-close]');
  const prevBtn = document.querySelector('[data-lightbox-prev]');
  const nextBtn = document.querySelector('[data-lightbox-next]');
  if (!triggers.length || !lightbox || !image || !caption) return;

  let current = 0;
  function show(index) {
    current = (index + triggers.length) % triggers.length;
    const trigger = triggers[current];
    image.src = trigger.dataset.lightboxSrc;
    image.alt = trigger.dataset.lightboxCaption || 'Фото Дальневосточной кинопремии';
    caption.textContent = trigger.dataset.lightboxCaption || '';
    lightbox.classList.add('open');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
  }
  function close() {
    lightbox.classList.remove('open');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    image.removeAttribute('src');
  }

  triggers.forEach((trigger, index) => trigger.addEventListener('click', () => show(index)));
  if (closeBtn) closeBtn.addEventListener('click', close);
  if (prevBtn) prevBtn.addEventListener('click', () => show(current - 1));
  if (nextBtn) nextBtn.addEventListener('click', () => show(current + 1));
  lightbox.addEventListener('click', (event) => {
    if (event.target === lightbox) close();
  });
  document.addEventListener('keydown', (event) => {
    if (!lightbox.classList.contains('open')) return;
    if (event.key === 'Escape') close();
    if (event.key === 'ArrowLeft') show(current - 1);
    if (event.key === 'ArrowRight') show(current + 1);
  });
})();

(function () {
  const targets = document.querySelectorAll('.section-title .hl');
  if (!targets.length || !('IntersectionObserver' in window)) {
    targets.forEach((el) => el.classList.add('lit'));
    return;
  }
  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('lit');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.6, rootMargin: '0px 0px -10% 0px' });
  targets.forEach((el) => io.observe(el));
})();

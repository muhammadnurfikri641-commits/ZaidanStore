(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const toast = document.getElementById('toast');
  let toastTimer;

  function notify(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('is-visible');
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => toast.classList.remove('is-visible'), 3200);
  }

  // The original ZaidanStore interactions: typewriter, reveal-on-scroll,
  // theme switch, quick scroll controls, and button ripples.
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const typed = document.getElementById('typed');
  if (typed && !reducedMotion) {
    const phrases = ['Murah & Terpercaya', 'Cepat & Aman', 'Praktis & Mudah'];
    let phraseIndex = 0;
    let deleting = false;
    const typeNext = () => {
      const phrase = phrases[phraseIndex];
      const current = typed.textContent || '';
      if (!deleting) {
        typed.textContent = phrase.slice(0, current.length + 1);
        if (typed.textContent === phrase) {
          deleting = true;
          window.setTimeout(typeNext, 1500);
          return;
        }
      } else {
        typed.textContent = current.slice(0, -1);
        if (!typed.textContent) {
          deleting = false;
          phraseIndex = (phraseIndex + 1) % phrases.length;
        }
      }
      window.setTimeout(typeNext, deleting ? 42 : 72);
    };
    window.setTimeout(typeNext, 1200);
  }

  const revealSelectors = [
    '.hero-copy > *', '.hero-aside', '.hero-bottom > *', '.trust-inner > *', '.popular-game',
    '.section-heading > *', '.catalog-toolbar', '.game-card', '.catalog-footnote',
    '.steps-intro > *', '.steps-list article', '.tracking-card', '.website-service > *',
    '.faq-heading > *', '.faq-list details', '.service-points > div', '.closing-inner > *', '.footer-main > *',
  ];
  const revealItems = document.querySelectorAll(revealSelectors.join(','));
  if (reducedMotion || !('IntersectionObserver' in window)) {
    revealItems.forEach((item) => item.classList.add('on'));
  } else {
    revealItems.forEach((item, index) => {
      item.classList.add('reveal');
      item.style.setProperty('--reveal-delay', `${Math.min(index % 4, 3) * 65}ms`);
    });
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('on');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -20px 0px' });
    revealItems.forEach((item) => revealObserver.observe(item));
  }

  const counters = document.querySelectorAll('[data-count]');
  const animateCounter = (element) => {
    const target = Number(element.dataset.count) || 0;
    if (reducedMotion) {
      element.textContent = new Intl.NumberFormat('id-ID').format(target);
      return;
    }
    const started = performance.now();
    const duration = 650;
    const update = (now) => {
      const progress = Math.min((now - started) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      element.textContent = new Intl.NumberFormat('id-ID').format(Math.round(target * eased));
      if (progress < 1) window.requestAnimationFrame(update);
    };
    window.requestAnimationFrame(update);
  };
  if ('IntersectionObserver' in window && !reducedMotion) {
    const countObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        animateCounter(entry.target);
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.6 });
    counters.forEach((counter) => countObserver.observe(counter));
  } else {
    counters.forEach(animateCounter);
  }

  const themeButton = document.querySelector('.theme-toggle');
  const setTheme = (isDark, announce = false) => {
    document.body.classList.toggle('dark-mode', isDark);
    themeButton?.setAttribute('aria-pressed', String(isDark));
    themeButton?.setAttribute('aria-label', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode malam');
    try { localStorage.setItem('zs-darkmode', isDark ? '1' : '0'); } catch (_) { /* Storage can be disabled. */ }
    if (announce) notify(isDark ? 'Mode malam aktif' : 'Mode terang aktif');
  };
  let savedTheme = '0';
  try { savedTheme = localStorage.getItem('zs-darkmode') || '0'; } catch (_) { /* Storage can be disabled. */ }
  setTheme(savedTheme === '1');
  themeButton?.addEventListener('click', () => setTheme(!document.body.classList.contains('dark-mode'), true));

  const siteHeader = document.querySelector('.site-header');
  const quickScroll = document.querySelector('.quick-scroll');
  const scrollTop = document.getElementById('scroll-top');
  const scrollProgress = scrollTop?.querySelector('.scroll-progress circle');
  let scrollFrame = 0;
  const updateScrollButtons = () => {
    scrollFrame = 0;
    const y = window.scrollY || window.pageYOffset;
    const scrollRange = document.documentElement.scrollHeight - window.innerHeight;
    const scrollPercent = scrollRange > 0 ? Math.min(y / scrollRange, 1) : 0;
    quickScroll?.classList.toggle('is-visible', y > 280);
    scrollProgress?.setAttribute('stroke-dashoffset', String(113.1 * (1 - scrollPercent)));
    siteHeader?.classList.toggle('scrolled', y > 10);
  };
  window.addEventListener('scroll', () => {
    if (scrollFrame) return;
    scrollFrame = window.requestAnimationFrame(updateScrollButtons);
  }, { passive: true });
  window.addEventListener('resize', updateScrollButtons, { passive: true });
  updateScrollButtons();
  scrollTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: reducedMotion ? 'instant' : 'smooth' }));

  document.querySelectorAll('.button,.order-button,.nav-contact,.filter-chip,.theme-toggle,.track-form button,.quick-scroll-button').forEach((button) => {
    button.addEventListener('click', (event) => {
      if (reducedMotion || button.disabled) return;
      const darkModeLabel = document.body.classList.contains('dark-mode') && button.matches('.button-primary,.order-button,.nav-contact,.track-form button');
      if (darkModeLabel) {
        button.classList.add('is-pressed');
        window.setTimeout(() => button.classList.remove('is-pressed'), 600);
      }
      const bounds = button.getBoundingClientRect();
      const size = Math.max(bounds.width, bounds.height);
      const ripple = document.createElement('span');
      ripple.className = 'ripple';
      ripple.style.width = `${size}px`;
      ripple.style.height = `${size}px`;
      ripple.style.left = `${event.clientX - bounds.left - size / 2}px`;
      ripple.style.top = `${event.clientY - bounds.top - size / 2}px`;
      if (darkModeLabel) ripple.style.color = '#fff';
      button.appendChild(ripple);
      window.setTimeout(() => ripple.remove(), 600);
    });
  });

  document.querySelectorAll('[data-product-select]').forEach((select) => {
    select.addEventListener('change', () => {
      const card = select.closest('.game-card');
      const option = select.selectedOptions[0];
      const price = option?.dataset.price;
      const label = card.querySelector('[data-price-label]');
      label.textContent = !select.value ? 'Pilih item dulu' : price ? `Rp ${new Intl.NumberFormat('id-ID').format(Number(price))}` : 'Tanya admin';
    });
  });

  document.querySelectorAll('[data-order-game]').forEach((button) => {
    button.addEventListener('click', async () => {
      const card = button.closest('.game-card');
      const productSelect = card.querySelector('[data-product-select]');
      const gameId = card.querySelector('.game-id').value.trim();
      const server = card.querySelector('.game-server')?.value.trim() || '';
      if (!productSelect.value) return notify('Pilih nominal atau item terlebih dahulu.');
      if (!/^[a-zA-Z0-9_-]{3,32}$/.test(gameId)) return notify('Masukkan ID game yang valid (3–32 karakter).');

      const pendingTab = window.open('about:blank', '_blank');
      button.disabled = true;
      button.classList.add('is-loading');
      const originalText = button.innerHTML;
      button.textContent = 'Menyimpan order…';
      try {
        const response = await fetch('api/orders.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
          body: JSON.stringify({ productId: Number(productSelect.value), gameId, server }),
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'Pesanan belum bisa dibuat.');

        const history = JSON.parse(localStorage.getItem('zs-orders') || '[]');
        history.unshift({ code: data.code, game: data.game, item: data.item, time: Date.now() });
        localStorage.setItem('zs-orders', JSON.stringify(history.slice(0, 8)));
        const message = [
          'Halo ZaidanStore! Saya ingin konfirmasi pesanan:',
          `Game: ${data.game}`,
          `Item: ${data.item}`,
          `ID akun: ${gameId}`,
          ...(server ? [`Server / Zone: ${server}`] : []),
          ...(data.price === null ? [] : [`Harga katalog: Rp ${new Intl.NumberFormat('id-ID').format(Number(data.price))}`]),
          `Kode order: ${data.code}`,
          'Mohon konfirmasi total dan prosesnya. Terima kasih.',
        ].join('\n');
        const whatsappUrl = `https://wa.me/${data.whatsappNumber}?text=${encodeURIComponent(message)}`;
        if (pendingTab) pendingTab.location.href = whatsappUrl;
        else window.location.href = whatsappUrl;

        const trackInput = document.getElementById('track-code');
        trackInput.value = data.code;
        document.getElementById('lacak-order').scrollIntoView({ behavior: 'smooth', block: 'center' });
        notify(`Order tersimpan: ${data.code}. Kode ini bisa dipakai untuk melacak pesanan.`);
      } catch (error) {
        if (pendingTab) pendingTab.close();
        notify(error.message || 'Terjadi kendala. Coba lagi.');
      } finally {
        button.disabled = false;
        button.classList.remove('is-loading');
        button.innerHTML = originalText;
      }
    });
  });

  document.querySelectorAll('.filter-chip').forEach((chip) => {
    chip.addEventListener('click', () => {
      document.querySelector('.filter-chip.is-active')?.classList.remove('is-active');
      chip.classList.add('is-active');
      const filter = chip.dataset.filter;
      document.querySelectorAll('.game-card').forEach((card) => {
        card.classList.toggle('is-hidden', filter !== 'all' && card.dataset.category !== filter);
      });
    });
  });

  const menuButton = document.querySelector('.menu-toggle');
  const menu = document.querySelector('.nav-links');
  menuButton?.addEventListener('click', () => {
    const isOpen = menu.classList.toggle('is-open');
    menuButton.setAttribute('aria-expanded', String(isOpen));
  });
  menu?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
    menu.classList.remove('is-open');
    menuButton?.setAttribute('aria-expanded', 'false');
  }));

  const statusNames = {
    pending: 'Menunggu konfirmasi', paid: 'Pembayaran diterima',
    processing: 'Sedang diproses', completed: 'Selesai', cancelled: 'Dibatalkan',
  };
  const trackForm = document.getElementById('track-form');
  const trackResult = document.getElementById('track-result');
  trackForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const code = document.getElementById('track-code').value.trim().toUpperCase();
    trackResult.className = 'track-result';
    trackResult.textContent = 'Memeriksa pesanan…';
    try {
      const response = await fetch(`api/track-order.php?code=${encodeURIComponent(code)}`);
      const data = await response.json();
      if (!response.ok) throw new Error(data.error || 'Order tidak ditemukan.');
      const when = new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(data.created_at.replace(' ', 'T')));
      trackResult.replaceChildren();
      const info = document.createElement('span');
      info.textContent = `${data.game} · ${data.item_name} · ${when} `;
      const status = document.createElement('strong');
      status.className = 'status-pill';
      status.textContent = statusNames[data.status] || data.status;
      trackResult.append(info, status);
    } catch (error) {
      trackResult.classList.add('error');
      trackResult.textContent = error.message || 'Gagal memeriksa pesanan. Coba lagi.';
    }
  });

  try {
    const lastOrder = JSON.parse(localStorage.getItem('zs-orders') || '[]')[0];
    if (lastOrder) document.getElementById('track-code').value = lastOrder.code;
  } catch (_) { /* Browser storage may be disabled. */ }
})();

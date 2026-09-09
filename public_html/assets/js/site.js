(() => {
  'use strict';

  const doc = document;
  doc.documentElement.classList.add('js');

  const body = doc.body;
  const scrim = doc.querySelector('[data-page-scrim]');
  const megaToggle = doc.querySelector('[data-mega-toggle]');
  const megaMenu = doc.querySelector('[data-mega-menu]');
  const searchToggle = doc.querySelector('[data-search-toggle]');
  const searchPanel = doc.querySelector('[data-site-search]');
  const menuToggle = doc.querySelector('[data-menu-toggle]');
  const mobileMenu = doc.querySelector('[data-mobile-menu]');

  const iconMarkup = (name, className = '') => `<svg class="welga-icon ${className}" aria-hidden="true"><use href="/assets/icons/welga-icons.svg#icon-${name}"></use></svg>`;
  const featureIcon = (label = '') => {
    const value = String(label).toLocaleLowerCase();
    const map = [
      ['sleep', ['сън', 'sleep', 'schlaf']],
      ['modular', ['модул', 'modular', 'modul']],
      ['relax', ['релакс', 'relax']],
      ['cushion', ['възглав', 'cushion', 'kissen']],
      ['layers', ['материал', 'material', 'пяна', 'foam', 'polster']],
      ['wood', ['дърв', 'wood', 'holz']],
      ['metal', ['метал', 'metal', 'хром', 'inox', 'stahl']],
      ['leaf', ['стил', 'design', 'дизайн', 'style']],
      ['mechanism', ['механиз', 'mechanism', 'mechanik']],
      ['storage', ['ракла', 'storage', 'stauraum']],
      ['upholstery', ['тапиц', 'upholstery', 'дамас', 'stoff', 'leder']],
    ];
    for (const [icon, words] of map) {
      if (words.some((word) => value.includes(word))) return icon;
    }
    return 'spark';
  };

  const setScrim = (visible) => {
    if (!scrim) return;
    scrim.hidden = !visible;
  };

  const closeMega = () => {
    if (!megaMenu || !megaToggle) return;
    megaMenu.hidden = true;
    megaToggle.setAttribute('aria-expanded', 'false');
  };

  const closeSearch = () => {
    if (!searchPanel || !searchToggle) return;
    searchPanel.hidden = true;
    searchToggle.setAttribute('aria-expanded', 'false');
  };

  const closeMobileMenu = () => {
    if (!mobileMenu || !menuToggle) return;
    mobileMenu.classList.remove('is-open');
    mobileMenu.setAttribute('aria-hidden', 'true');
    menuToggle.setAttribute('aria-expanded', 'false');
    body.classList.remove('is-locked');
  };

  const closeHeaderLayers = () => {
    closeMega();
    closeSearch();
    closeMobileMenu();
    setScrim(false);
  };

  megaToggle?.addEventListener('click', () => {
    const open = megaMenu?.hidden ?? true;
    closeSearch();
    closeMobileMenu();
    if (megaMenu) megaMenu.hidden = !open;
    megaToggle.setAttribute('aria-expanded', String(open));
    setScrim(open);
  });

  searchToggle?.addEventListener('click', () => {
    const open = searchPanel?.hidden ?? true;
    closeMega();
    closeMobileMenu();
    if (searchPanel) searchPanel.hidden = !open;
    searchToggle.setAttribute('aria-expanded', String(open));
    setScrim(open);
    if (open) window.setTimeout(() => searchPanel?.querySelector('input')?.focus(), 80);
  });

  menuToggle?.addEventListener('click', () => {
    if (!mobileMenu) return;
    closeMega();
    closeSearch();
    const open = !mobileMenu.classList.contains('is-open');
    mobileMenu.classList.toggle('is-open', open);
    mobileMenu.setAttribute('aria-hidden', String(!open));
    menuToggle.setAttribute('aria-expanded', String(open));
    body.classList.toggle('is-locked', open);
    setScrim(open);
  });

  doc.querySelectorAll('[data-menu-close]').forEach((button) => button.addEventListener('click', closeHeaderLayers));
  scrim?.addEventListener('click', closeHeaderLayers);
  doc.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeHeaderLayers(); });

  // Scroll reveal: progressive enhancement, content remains visible without JS.
  const reveals = [...doc.querySelectorAll('.reveal')];
  if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    }, { rootMargin: '0px 0px -7% 0px', threshold: 0.08 });
    reveals.forEach((element) => observer.observe(element));
  } else {
    reveals.forEach((element) => element.classList.add('is-visible'));
  }

  // Native dialog helpers.
  const openDialog = (dialog) => {
    if (!dialog || typeof dialog.showModal !== 'function') return;
    if (!dialog.open) dialog.showModal();
  };

  doc.querySelectorAll('[data-dialog-open]').forEach((button) => {
    button.addEventListener('click', () => openDialog(doc.getElementById(button.dataset.dialogOpen || '')));
  });

  doc.querySelectorAll('[data-dialog-close]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
  });

  doc.querySelectorAll('dialog').forEach((dialog) => {
    dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
  });

  // PDF modal.
  const pdfDialog = doc.querySelector('[data-pdf-dialog]');
  const pdfFrame = pdfDialog?.querySelector('[data-pdf-frame]');
  const pdfTitle = pdfDialog?.querySelector('[data-pdf-dialog-title]');
  doc.querySelectorAll('[data-pdf-open]').forEach((button) => {
    if (!button.querySelector('.welga-icon')) button.insertAdjacentHTML('afterbegin', iconMarkup('pdf'));
    button.addEventListener('click', () => {
      if (!pdfDialog || !pdfFrame) return;
      pdfFrame.setAttribute('src', button.dataset.pdfOpen || '');
      if (pdfTitle) pdfTitle.textContent = button.dataset.pdfTitle || 'PDF';
      openDialog(pdfDialog);
    });
  });
  pdfDialog?.addEventListener('close', () => pdfFrame?.removeAttribute('src'));

  // Product image gallery: auto/manual main image + thumbnails + lightbox.
  const galleryDialog = doc.querySelector('[data-gallery-dialog]');
  const galleryImage = galleryDialog?.querySelector('[data-gallery-image]');
  const galleryDataNode = galleryDialog?.querySelector('[data-gallery-data]');
  const introGallery = doc.querySelector('.product-intro__gallery');
  const introMainButton = introGallery?.querySelector('.product-main-image[data-gallery-open]');
  const introMainImage = introMainButton?.querySelector('img');
  const introThumbs = [...(introGallery?.querySelectorAll('.product-intro__thumbs [data-gallery-open]') || [])];
  let galleryData = [];
  let galleryIndex = 0;
  let introIndex = 0;
  let introTimer = null;

  if (galleryDataNode) {
    try { galleryData = JSON.parse(galleryDataNode.textContent || '[]'); } catch (_) { galleryData = []; }
  }

  const renderGallery = () => {
    if (!galleryImage || galleryData.length === 0) return;
    galleryIndex = (galleryIndex + galleryData.length) % galleryData.length;
    const item = galleryData[galleryIndex];
    galleryImage.src = '/' + String(item.path || '').replace(/^\/+/, '');
    galleryImage.alt = item.alt_text || '';
  };

  const renderIntroGallery = (nextIndex) => {
    if (!introMainImage || galleryData.length === 0) return;
    introIndex = (nextIndex + galleryData.length) % galleryData.length;
    const item = galleryData[introIndex];
    introMainImage.classList.add('is-changing');
    window.setTimeout(() => {
      introMainImage.src = '/' + String(item.path || '').replace(/^\/+/, '');
      introMainImage.alt = item.alt_text || '';
      introMainButton.dataset.galleryOpen = String(introIndex);
      introMainImage.classList.remove('is-changing');
    }, 120);
    introThumbs.forEach((thumb, index) => thumb.classList.toggle('is-active', Number(thumb.dataset.galleryOpen) === introIndex));
    introGallery?.querySelectorAll('[data-intro-dot]').forEach((dot, index) => dot.classList.toggle('is-active', index === introIndex));
  };

  const resetIntroTimer = () => {
    if (introTimer) window.clearInterval(introTimer);
    if (galleryData.length < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    introTimer = window.setInterval(() => renderIntroGallery(introIndex + 1), 6500);
  };

  if (introGallery && galleryData.length > 1 && introMainButton) {
    const nav = doc.createElement('div');
    nav.className = 'product-intro__gallery-nav';
    nav.innerHTML = `<button type="button" data-intro-prev aria-label="Previous">${iconMarkup('chevron-right', 'is-back')}</button><div class="product-intro__dots">${galleryData.map((_, index) => `<button type="button" data-intro-dot="${index}" class="${index === 0 ? 'is-active' : ''}" aria-label="${index + 1}"></button>`).join('')}</div><button type="button" data-intro-next aria-label="Next">${iconMarkup('chevron-right')}</button>`;
    introGallery.appendChild(nav);
    nav.querySelector('[data-intro-prev]')?.addEventListener('click', () => { renderIntroGallery(introIndex - 1); resetIntroTimer(); });
    nav.querySelector('[data-intro-next]')?.addEventListener('click', () => { renderIntroGallery(introIndex + 1); resetIntroTimer(); });
    nav.querySelectorAll('[data-intro-dot]').forEach((dot) => dot.addEventListener('click', () => { renderIntroGallery(Number(dot.dataset.introDot || 0)); resetIntroTimer(); }));
    introThumbs.forEach((thumb) => thumb.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopImmediatePropagation();
      renderIntroGallery(Number(thumb.dataset.galleryOpen || 0));
      resetIntroTimer();
    }, true));
    introGallery.addEventListener('pointerenter', () => { if (introTimer) window.clearInterval(introTimer); });
    introGallery.addEventListener('pointerleave', resetIntroTimer);
    resetIntroTimer();
  }

  doc.querySelectorAll('[data-gallery-open]').forEach((button) => {
    if (button.closest('.product-intro__thumbs')) return;
    button.addEventListener('click', () => {
      galleryIndex = button === introMainButton ? introIndex : Number(button.dataset.galleryOpen || 0);
      renderGallery();
      openDialog(galleryDialog);
    });
  });
  galleryDialog?.querySelector('[data-gallery-prev]')?.addEventListener('click', () => { galleryIndex -= 1; renderGallery(); });
  galleryDialog?.querySelector('[data-gallery-next]')?.addEventListener('click', () => { galleryIndex += 1; renderGallery(); });
  galleryDialog?.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') { galleryIndex -= 1; renderGallery(); }
    if (event.key === 'ArrowRight') { galleryIndex += 1; renderGallery(); }
  });

  // Structured icon decoration on product feature surfaces.
  doc.querySelectorAll('.product-feature-pills li').forEach((item) => {
    if (!item.querySelector('.welga-icon')) item.insertAdjacentHTML('afterbegin', iconMarkup(featureIcon(item.textContent || '')));
  });
  doc.querySelectorAll('.feature-list__row').forEach((row) => {
    const text = row.querySelector('p')?.textContent || '';
    const number = row.querySelector('span');
    if (number) number.innerHTML = iconMarkup(featureIcon(text));
  });

  // Option choice is UI state only; the eventual inquiry payload reuses selected values.
  doc.querySelectorAll('.option-values').forEach((group) => {
    group.querySelectorAll('[data-option-value]').forEach((button) => {
      button.addEventListener('click', () => {
        group.querySelectorAll('[data-option-value]').forEach((item) => item.classList.remove('is-selected'));
        button.classList.add('is-selected');
      });
    });
  });

  // Product inquiry / quote CTAs and async protected delivery.
  const inquiryDialog = doc.getElementById('inquiry-dialog');
  const inquiryForm = inquiryDialog?.querySelector('[data-inquiry-form]');
  const mainInquiryButton = doc.querySelector('.product-actions > [data-dialog-open="inquiry-dialog"]');
  if (mainInquiryButton) {
    mainInquiryButton.insertAdjacentHTML('afterbegin', iconMarkup('message'));
    const quote = doc.createElement('button');
    quote.type = 'button';
    quote.className = 'button button--outline button--wide quote-button';
    quote.innerHTML = `${iconMarkup('offer')}<span>${doc.documentElement.lang === 'bg' ? 'Искам оферта' : (doc.documentElement.lang === 'de' ? 'Angebot anfordern' : 'Request a quote')}</span>${iconMarkup('arrow-right')}`;
    mainInquiryButton.insertAdjacentElement('afterend', quote);
    quote.addEventListener('click', () => {
      if (inquiryForm) inquiryForm.dataset.requestType = 'quote';
      openDialog(inquiryDialog);
    });
    mainInquiryButton.addEventListener('click', () => { if (inquiryForm) inquiryForm.dataset.requestType = 'inquiry'; });
  }

  if (inquiryForm) {
    const honeypot = doc.createElement('input');
    honeypot.type = 'text';
    honeypot.name = 'website';
    honeypot.tabIndex = -1;
    honeypot.autocomplete = 'off';
    honeypot.className = 'form-honeypot';
    inquiryForm.appendChild(honeypot);

    const status = doc.createElement('p');
    status.className = 'form-status';
    status.setAttribute('aria-live', 'polite');
    inquiryForm.appendChild(status);

    inquiryForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const submit = inquiryForm.querySelector('button[value="submit"]');
      const original = submit?.innerHTML || '';
      if (submit) { submit.disabled = true; submit.textContent = '…'; }
      status.textContent = '';

      try {
        const csrfResponse = await fetch('/api/csrf.php', { credentials: 'same-origin', headers: { Accept: 'application/json' } });
        const csrfPayload = await csrfResponse.json();
        if (!csrfPayload?.token) throw new Error('csrf');

        const data = new FormData(inquiryForm);
        data.set('language', doc.documentElement.lang || 'bg');
        data.set('request_type', inquiryForm.dataset.requestType || 'inquiry');
        data.set('selected_options', [...doc.querySelectorAll('.option-value.is-selected')].map((item) => item.textContent.trim()).join('; '));
        data.set('selected_upholstery', [...doc.querySelectorAll('.upholstery-card.is-selected')].map((item) => item.textContent.trim()).join('; '));

        const response = await fetch('/api/product-inquiry.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'X-CSRF-Token': csrfPayload.token, Accept: 'application/json' },
          body: data,
        });
        const payload = await response.json();
        if (!response.ok || !payload?.ok) throw new Error(payload?.code || 'delivery');

        status.textContent = doc.documentElement.lang === 'bg' ? 'Запитването е изпратено успешно.' : (doc.documentElement.lang === 'de' ? 'Ihre Anfrage wurde erfolgreich gesendet.' : 'Your inquiry was sent successfully.');
        inquiryForm.reset();
      } catch (_) {
        status.textContent = doc.documentElement.lang === 'bg' ? 'В момента не успяхме да изпратим формата. Опитайте отново.' : (doc.documentElement.lang === 'de' ? 'Die Anfrage konnte momentan nicht gesendet werden. Bitte versuchen Sie es erneut.' : 'We could not send the form right now. Please try again.');
      } finally {
        if (submit) { submit.disabled = false; submit.innerHTML = original; }
      }
    });
  }

  doc.querySelectorAll('[data-upholstery-collection]').forEach((button) => {
    button.addEventListener('click', () => button.classList.toggle('is-selected'));
  });

  // Category filters: OR inside a group, AND between groups.
  const catalogue = doc.querySelector('[data-category-catalog]');
  if (catalogue) {
    const panel = catalogue.querySelector('[data-filter-panel]');
    const filterInputs = [...catalogue.querySelectorAll('[data-filter-input]')];
    const productWraps = [...catalogue.querySelectorAll('[data-product-wrap]')];
    const sortSelects = [...doc.querySelectorAll('[data-sort-select]')];
    const sortChoices = [...doc.querySelectorAll('[data-sort-choice]')];
    const layoutChoices = [...doc.querySelectorAll('[data-layout-choice]')];
    const resultCounts = [...doc.querySelectorAll('[data-visible-count], [data-filter-result-count]')];
    const activeCounts = [...doc.querySelectorAll('[data-active-filter-count]')];
    const emptyState = catalogue.querySelector('[data-category-empty]');
    const productGrid = catalogue.querySelector('[data-category-products]');
    const filterScrim = doc.querySelector('.filter-scrim');

    const selectedByGroup = () => {
      const groups = new Map();
      filterInputs.filter((input) => input.checked).forEach((input) => {
        const group = input.dataset.filterGroup || '0';
        if (!groups.has(group)) groups.set(group, []);
        groups.get(group).push(String(input.value));
      });
      return groups;
    };

    const productMatches = (wrap, groups) => {
      const card = wrap.querySelector('[data-product-card]');
      const filterIds = card?.dataset.filterIds || ',,';
      for (const selected of groups.values()) {
        if (!selected.some((id) => filterIds.includes(',' + id + ','))) return false;
      }
      return true;
    };

    const currentSort = () => sortChoices.find((choice) => choice.checked)?.value || sortSelects[0]?.value || 'latest';
    const sortProducts = () => {
      if (!productGrid) return;
      const mode = currentSort();
      const sorted = [...productWraps].sort((a, b) => {
        const cardA = a.querySelector('[data-product-card]');
        const cardB = b.querySelector('[data-product-card]');
        const nameA = cardA?.dataset.name || '';
        const nameB = cardB?.dataset.name || '';
        if (mode === 'name') return nameA.localeCompare(nameB, undefined, { sensitivity: 'base' });
        if (mode === 'name-desc') return nameB.localeCompare(nameA, undefined, { sensitivity: 'base' });
        return (cardB?.dataset.published || '').localeCompare(cardA?.dataset.published || '');
      });
      sorted.forEach((item) => productGrid.appendChild(item));
    };

    const applyFilters = () => {
      const groups = selectedByGroup();
      let visible = 0;
      productWraps.forEach((wrap) => {
        const matches = productMatches(wrap, groups);
        wrap.hidden = !matches;
        if (matches) visible += 1;
      });
      const active = filterInputs.filter((input) => input.checked).length;
      resultCounts.forEach((node) => { node.textContent = String(visible); });
      activeCounts.forEach((node) => { node.textContent = String(active); });
      if (emptyState) emptyState.hidden = visible !== 0;
      sortProducts();
    };

    filterInputs.forEach((input) => input.addEventListener('change', applyFilters));
    sortSelects.forEach((select) => {
      select.addEventListener('change', () => {
        sortSelects.forEach((other) => { if (other !== select) other.value = select.value; });
        sortChoices.forEach((choice) => { choice.checked = choice.value === select.value; });
        sortProducts();
      });
    });
    sortChoices.forEach((choice) => choice.addEventListener('change', () => {
      if (!choice.checked) return;
      sortSelects.forEach((select) => { select.value = choice.value; });
      sortProducts();
    }));
    layoutChoices.forEach((choice) => choice.addEventListener('change', () => {
      if (!choice.checked || !productGrid) return;
      productGrid.classList.toggle('is-one-column', choice.value === 'one');
      productGrid.classList.toggle('is-two-column', choice.value !== 'one');
    }));

    doc.querySelectorAll('[data-filter-clear]').forEach((button) => button.addEventListener('click', () => {
      filterInputs.forEach((input) => { input.checked = false; });
      applyFilters();
    }));

    const openFilters = () => {
      panel?.classList.add('is-open');
      if (filterScrim) filterScrim.hidden = false;
      body.classList.add('is-locked');
    };
    const closeFilters = () => {
      panel?.classList.remove('is-open');
      if (filterScrim) filterScrim.hidden = true;
      body.classList.remove('is-locked');
    };
    doc.querySelectorAll('[data-filter-toggle]').forEach((button) => button.addEventListener('click', openFilters));
    doc.querySelectorAll('[data-filter-close], [data-filter-apply]').forEach((button) => button.addEventListener('click', closeFilters));

    applyFilters();
  }

  // Mobile homepage gets a second lightweight inquiry action, matching the reference without duplicating desktop UI.
  const heroIntro = doc.querySelector('.page-home .home-hero__intro');
  if (heroIntro && !heroIntro.querySelector('.home-hero__secondary')) {
    const lang = doc.documentElement.lang || 'bg';
    const href = lang === 'bg' ? '/kontakti/' : `/${lang}/kontakti/`;
    const secondary = doc.createElement('a');
    secondary.className = 'home-hero__secondary';
    secondary.href = href;
    secondary.innerHTML = `${iconMarkup('message')}<span>${lang === 'bg' ? 'Изпрати запитване' : (lang === 'de' ? 'Anfrage senden' : 'Send inquiry')}</span>`;
    heroIntro.appendChild(secondary);
  }
})();

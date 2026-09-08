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
    if (open) {
      window.setTimeout(() => searchPanel?.querySelector('input')?.focus(), 80);
    }
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

  doc.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeHeaderLayers();
  });

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
    dialog.addEventListener('click', (event) => {
      if (event.target === dialog) dialog.close();
    });
  });

  // PDF modal.
  const pdfDialog = doc.querySelector('[data-pdf-dialog]');
  const pdfFrame = pdfDialog?.querySelector('[data-pdf-frame]');
  const pdfTitle = pdfDialog?.querySelector('[data-pdf-dialog-title]');
  doc.querySelectorAll('[data-pdf-open]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!pdfDialog || !pdfFrame) return;
      pdfFrame.setAttribute('src', button.dataset.pdfOpen || '');
      if (pdfTitle) pdfTitle.textContent = button.dataset.pdfTitle || 'PDF';
      openDialog(pdfDialog);
    });
  });
  pdfDialog?.addEventListener('close', () => pdfFrame?.removeAttribute('src'));

  // Product image lightbox.
  const galleryDialog = doc.querySelector('[data-gallery-dialog]');
  const galleryImage = galleryDialog?.querySelector('[data-gallery-image]');
  const galleryDataNode = galleryDialog?.querySelector('[data-gallery-data]');
  let galleryData = [];
  let galleryIndex = 0;

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

  doc.querySelectorAll('[data-gallery-open]').forEach((button) => {
    button.addEventListener('click', () => {
      galleryIndex = Number(button.dataset.galleryOpen || 0);
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

  // Option choice is UI state only; the eventual inquiry payload will reuse these selected values.
  doc.querySelectorAll('.option-values').forEach((group) => {
    group.querySelectorAll('[data-option-value]').forEach((button) => {
      button.addEventListener('click', () => {
        group.querySelectorAll('[data-option-value]').forEach((item) => item.classList.remove('is-selected'));
        button.classList.add('is-selected');
      });
    });
  });

  // Category filters: OR inside a group, AND between groups.
  const catalogue = doc.querySelector('[data-category-catalog]');
  if (catalogue) {
    const panel = catalogue.querySelector('[data-filter-panel]');
    const filterInputs = [...catalogue.querySelectorAll('[data-filter-input]')];
    const productWraps = [...catalogue.querySelectorAll('[data-product-wrap]')];
    const sortSelects = [...doc.querySelectorAll('[data-sort-select]')];
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

    const sortProducts = () => {
      if (!productGrid) return;
      const mode = sortSelects[0]?.value || 'latest';
      const sorted = [...productWraps].sort((a, b) => {
        const cardA = a.querySelector('[data-product-card]');
        const cardB = b.querySelector('[data-product-card]');
        if (mode === 'name') return (cardA?.dataset.name || '').localeCompare(cardB?.dataset.name || '', undefined, { sensitivity: 'base' });
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
        sortProducts();
      });
    });

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
})();

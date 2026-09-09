(() => {
  'use strict';

  const section = document.querySelector('.page-product #configurations');
  const productIdField = document.querySelector('.page-product input[name="product_id"]');
  if (!section || !productIdField) return;

  const productId = Number(productIdField.value || 0);
  if (!productId) return;

  const lang = document.documentElement.lang || 'bg';
  const icon = (name) => `<svg class="welga-icon" aria-hidden="true"><use href="/assets/icons/welga-icons.svg#icon-${name}"></use></svg>`;
  const labels = {
    bg: { technical: 'Технически скици и размери', choose: 'Конфигурации', noImage: 'Схемата се подготвя' },
    en: { technical: 'Technical drawings and dimensions', choose: 'Configurations', noImage: 'Drawing in preparation' },
    de: { technical: 'Technische Zeichnungen und Maße', choose: 'Konfigurationen', noImage: 'Zeichnung wird vorbereitet' },
  };
  const text = labels[lang] || labels.en;

  const escapeHtml = (value) => String(value ?? '').replace(/[&<>"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[char]));

  fetch(`/api/product-configurations.php?product_id=${encodeURIComponent(productId)}&lang=${encodeURIComponent(lang)}`, {
    credentials: 'same-origin',
    headers: { Accept: 'application/json' },
  })
    .then((response) => response.ok ? response.json() : null)
    .then((payload) => {
      const configurations = payload?.configurations || [];
      if (!Array.isArray(configurations) || configurations.length === 0) return;

      const body = section.querySelector('.product-section__body') || section;
      const block = document.createElement('div');
      block.className = 'technical-configurations reveal is-visible';
      block.innerHTML = `
        <div class="technical-configurations__eyebrow">${icon('dimensions')}<span>${escapeHtml(text.technical)}</span></div>
        <div class="technical-configurations__stage" data-technical-stage></div>
        <div class="technical-configurations__rail" data-technical-rail aria-label="${escapeHtml(text.choose)}"></div>
      `;
      body.prepend(block);

      const stage = block.querySelector('[data-technical-stage]');
      const rail = block.querySelector('[data-technical-rail]');

      const render = (index) => {
        const configuration = configurations[index];
        if (!configuration || !stage) return;
        const media = Array.isArray(configuration.media) ? configuration.media : [];
        const diagram = media.find((item) => ['overview', 'diagram', 'dimension'].includes(item.role)) || media[0] || null;
        const attributes = Array.isArray(configuration.attributes) ? configuration.attributes : [];
        stage.innerHTML = `
          <div class="technical-configurations__visual">
            ${diagram?.path
              ? `<img src="/${escapeHtml(String(diagram.path).replace(/^\/+/, ''))}" alt="${escapeHtml(diagram.alt_text || configuration.name || '')}" loading="lazy">`
              : `<div class="technical-configurations__placeholder">${icon('dimensions')}<span>${escapeHtml(text.noImage)}</span></div>`}
          </div>
          <div class="technical-configurations__info">
            <p class="kicker">${escapeHtml(configuration.code || '')}</p>
            <h3>${escapeHtml(configuration.name || configuration.code || '')}</h3>
            ${configuration.description ? `<p class="technical-configurations__description">${escapeHtml(configuration.description)}</p>` : ''}
            <div class="technical-configurations__attributes">
              ${attributes.map((attribute) => `<div><span>${escapeHtml(attribute.name)}</span><strong>${escapeHtml(attribute.value)}</strong></div>`).join('')}
            </div>
          </div>
        `;
        rail?.querySelectorAll('button').forEach((button, buttonIndex) => button.classList.toggle('is-active', buttonIndex === index));
      };

      if (rail) {
        configurations.forEach((configuration, index) => {
          const diagram = Array.isArray(configuration.media)
            ? (configuration.media.find((item) => ['overview', 'diagram', 'dimension'].includes(item.role)) || configuration.media[0])
            : null;
          const button = document.createElement('button');
          button.type = 'button';
          button.className = index === 0 ? 'is-active' : '';
          button.innerHTML = `${diagram?.path ? `<img src="/${escapeHtml(String(diagram.path).replace(/^\/+/, ''))}" alt="" loading="lazy">` : icon('dimensions')}<span>${escapeHtml(configuration.name || configuration.code || '')}</span>`;
          button.addEventListener('click', () => render(index));
          rail.appendChild(button);
        });
      }

      render(0);
    })
    .catch(() => {
      // Technical configurations are an enhancement; old dev databases remain fully usable.
    });
})();

(() => {
  'use strict';

  const doc = document;
  const lang = doc.documentElement.lang || 'bg';
  const page = doc.querySelector('.page-product');

  doc.querySelectorAll('.brand img').forEach((image) => {
    image.src = '/assets/img/logo-welga-line-grey.svg';
  });

  if (!page) return;

  const section = doc.querySelector('.page-product #configurations');
  const productIdField = doc.querySelector('.page-product input[name="product_id"]');
  const modelField = doc.querySelector('.page-product input[name="model"]');
  const productHeading = doc.querySelector('.page-product .product-intro__info h1');
  if (!section || !productIdField) return;

  const model = String(modelField?.value || '').trim();
  const productId = Number(productIdField.value || 0);
  const escapeHtml = (value) => String(value ?? '').replace(/[&<>\"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '\"': '&quot;' }[char]));
  const cleanPath = (value) => String(value || '').split('/').filter(Boolean).join('/');
  const icon = (name) => `<svg class="welga-icon" aria-hidden="true"><use href="/assets/icons/welga-icons.svg#icon-${name}"></use></svg>`;

  const labels = {
    bg: {
      technical: 'Технически скици и размери', choose: 'Конфигурации', noImage: 'Схемата се подготвя', full: 'Пълно описание', benefits: 'Качество във всеки елемент', showroom: 'В нашия шоурум', showroomText: 'Този модел може да бъде видян и изпробван в нашия шоурум в гр. Ловеч. Свържете се с нас предварително за наличност и посещение.', upholsteryAll: 'Виж всички дамаски', docs: 'Документи и техническа информация', techDoc: 'Технически данни PDF', priceDoc: 'Ценова листа PDF', quoteTitle: 'Искане на оферта', company: 'Фирма', companyId: 'ЕИК / VAT №', quantity: 'Брой', country: 'Държава', city: 'Град', delivery: 'Искате ли доставка?', yes: 'Да', no: 'Не', name: 'Имена', email: 'Имейл', phone: 'Телефон', message: 'Допълнителна информация', privacy: 'Съгласен съм с Политиката за поверителност', sendQuote: 'Изпрати искане за оферта', sent: 'Искането за оферта е изпратено успешно.', failed: 'В момента не успяхме да изпратим формата. Опитайте отново.', produced: 'Произведено в България', fabrics: 'Богат избор на дамаски', width: 'Обща ширина', depth: 'Обща дълбочина', height: 'Обща височина', armHeight: 'Височина на подлакътника', seatHeight: 'Височина на седалката', seatDepth: 'Дълбочина на седалката', seatWidth: 'Седална ширина*', footnote: '* Седална ширина без декоративни възглавници.'
    },
    en: {
      technical: 'Technical drawings and dimensions', choose: 'Configurations', noImage: 'Drawing in preparation', full: 'Full description', benefits: 'Quality in every element', showroom: 'In our showroom', showroomText: 'This model can be viewed and tested in our showroom in Lovech. Please contact us in advance for availability and a visit.', upholsteryAll: 'View all upholstery', docs: 'Documents and technical information', techDoc: 'Technical data PDF', priceDoc: 'Price list PDF', quoteTitle: 'Request a quote', company: 'Company', companyId: 'Company / VAT No.', quantity: 'Quantity', country: 'Country', city: 'City', delivery: 'Delivery required?', yes: 'Yes', no: 'No', name: 'Name', email: 'Email', phone: 'Phone', message: 'Additional information', privacy: 'I agree to the Privacy Policy', sendQuote: 'Send quote request', sent: 'Your quote request was sent successfully.', failed: 'We could not send the form right now. Please try again.', produced: 'Made in Bulgaria', fabrics: 'Wide upholstery selection', width: 'Overall width', depth: 'Overall depth', height: 'Overall height', armHeight: 'Arm height', seatHeight: 'Seat height', seatDepth: 'Seat depth', seatWidth: 'Seat width*', footnote: '* Seat width without decorative cushions.'
    },
    de: {
      technical: 'Technische Zeichnungen und Maße', choose: 'Konfigurationen', noImage: 'Zeichnung wird vorbereitet', full: 'Vollständige Beschreibung', benefits: 'Qualität in jedem Element', showroom: 'In unserem Showroom', showroomText: 'Dieses Modell kann in unserem Showroom in Lovech besichtigt und getestet werden. Bitte kontaktieren Sie uns vorab bezüglich Verfügbarkeit und Besuch.', upholsteryAll: 'Alle Bezugsstoffe', docs: 'Dokumente und technische Informationen', techDoc: 'Technische Daten PDF', priceDoc: 'Preisliste PDF', quoteTitle: 'Angebot anfordern', company: 'Firma', companyId: 'Firmen- / USt.-Nr.', quantity: 'Menge', country: 'Land', city: 'Stadt', delivery: 'Lieferung gewünscht?', yes: 'Ja', no: 'Nein', name: 'Name', email: 'E-Mail', phone: 'Telefon', message: 'Zusätzliche Informationen', privacy: 'Ich stimme der Datenschutzerklärung zu', sendQuote: 'Angebotsanfrage senden', sent: 'Ihre Angebotsanfrage wurde erfolgreich gesendet.', failed: 'Die Anfrage konnte momentan nicht gesendet werden. Bitte versuchen Sie es erneut.', produced: 'Hergestellt in Bulgarien', fabrics: 'Große Auswahl an Bezugsstoffen', width: 'Gesamtbreite', depth: 'Gesamttiefe', height: 'Gesamthöhe', armHeight: 'Armlehnenhöhe', seatHeight: 'Sitzhöhe', seatDepth: 'Sitztiefe', seatWidth: 'Sitzbreite*', footnote: '* Sitzbreite ohne Dekokissen.'
    }
  };
  const text = labels[lang] || labels.en;

  if (!doc.getElementById('welga-product-refinement-styles')) {
    const style = doc.createElement('style');
    style.id = 'welga-product-refinement-styles';
    style.textContent = `
      :root{--gold:#b39a69;--gold-dark:#957b4c;--gold-soft:#f4eee3}.page-product .button{border-radius:10px}.page-product .button--dark{background:#9f8658;border-color:#9f8658}.page-product .button--dark:hover{background:#8d7449;border-color:#8d7449}.page-product .quote-button{border-radius:10px}.page-product .product-document-actions .text-link{border:1px solid #d9d1c4;border-radius:9px;padding:12px 14px;flex:1;justify-content:center;text-decoration:none}.page-product .product-feature-pills{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px 18px;margin:24px 0!important;padding:0!important;list-style:none!important}.page-product .product-feature-pills li{display:flex!important;align-items:center;gap:10px;border:0!important;background:transparent!important;padding:0!important;color:#5f5a52!important;font-size:13px!important;line-height:1.3!important}.page-product .product-feature-pills .welga-icon{width:23px;height:23px;flex:0 0 23px;color:#9f8658}.showroom-marker{display:grid;grid-template-columns:34px 1fr;gap:12px;margin-top:18px;padding:16px;border:1px solid #e5dfd5;border-radius:12px;background:#fbfaf8}.showroom-marker .welga-icon{width:25px;height:25px;color:#9f8658}.showroom-marker strong{display:block;margin-bottom:3px;font-size:13px}.showroom-marker p{margin:0;color:#6f695f;font-size:12px;line-height:1.55}.technical-fallback{margin-bottom:34px}.technical-fallback__stage{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(280px,.75fr);gap:36px;align-items:center;padding:28px;background:#f7f5f1;border-radius:18px}.technical-fallback__visual{background:#fff;border-radius:14px;overflow:hidden}.technical-fallback__visual img{width:100%;height:auto}.technical-fallback__info h3{margin:0 0 18px;font-family:var(--font-display);font-size:30px;font-weight:400}.technical-dimensions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0;border-top:1px solid #ddd6cb}.technical-dimensions div{display:flex;justify-content:space-between;gap:12px;padding:11px 0;border-bottom:1px solid #e8e2d9}.technical-dimensions div:nth-child(odd){padding-right:18px}.technical-dimensions div:nth-child(even){padding-left:18px;border-left:1px solid #e8e2d9}.technical-dimensions span{color:#716a60;font-size:12px}.technical-dimensions strong{font-size:13px}.technical-footnote{margin:12px 0 0;color:#8d857a;font-size:11px}.product-documents-refined{padding-top:72px;padding-bottom:72px}.product-documents-refined__grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.product-document-card{display:flex;align-items:center;justify-content:space-between;gap:20px;width:100%;min-height:86px;padding:18px 20px;border:1px solid #ddd5c8;border-radius:12px;background:#fff;text-align:left;cursor:pointer}.product-document-card .welga-icon{width:28px;height:28px;color:#9f8658}.product-document-card span{flex:1}.product-document-card strong{display:block;font-size:14px}.product-document-card small{color:#857d71}.product-document-card:hover{border-color:#b39a69;background:#fbfaf7}.upholstery-type{overflow:visible}.upholstery-collections{display:flex!important;grid-template-columns:none!important;gap:14px!important;overflow-x:auto!important;scroll-snap-type:x mandatory;padding:2px max(24px,calc((100vw - var(--shell))/2)) 18px 0;margin-right:calc((100vw - var(--shell))/-2);scrollbar-width:none}.upholstery-collections::-webkit-scrollbar{display:none}.upholstery-card{flex:0 0 clamp(170px,18vw,250px)!important;scroll-snap-align:start}.upholstery-view-all{display:inline-flex;align-items:center;gap:9px;margin-left:auto;color:#8d7449;text-decoration:none;font-size:12px;border-bottom:1px solid currentColor;padding-bottom:3px}.upholstery-type__head{display:flex;align-items:end;gap:16px}.upholstery-type__head>span{margin-left:0!important}.upholstery-type__head .upholstery-view-all{margin-left:auto}.feature-list::before{content:'${text.benefits.replace(/'/g,"\\'")}';display:block;grid-column:1/-1;margin-bottom:12px;font-family:var(--font-display);font-size:28px}.feature-list__row{border-radius:10px!important}.feature-list__row>span{display:grid!important;place-items:center}.feature-list__row .welga-icon{width:25px;height:25px;color:#9f8658}.quote-dialog{width:min(780px,calc(100vw - 28px));max-height:90vh;border:0;border-radius:18px;padding:0;box-shadow:0 30px 90px rgba(35,30,22,.22)}.quote-dialog::backdrop{background:rgba(22,19,15,.45);backdrop-filter:blur(3px)}.quote-form{padding:26px}.quote-form .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.quote-form label{display:grid;gap:6px}.quote-form label span{font-size:11px;color:#686158}.quote-form input,.quote-form select,.quote-form textarea{width:100%;border:1px solid #dcd5ca;border-radius:9px;padding:12px;background:#fff}.quote-form .form-wide{grid-column:1/-1}.quote-form__privacy{display:flex!important;grid-template-columns:none!important;align-items:flex-start;gap:9px!important;margin:14px 0}.quote-form__privacy input{width:auto;margin-top:3px}.quote-form__privacy span{font-size:12px!important}.quote-form .form-status{min-height:20px;font-size:12px;margin:10px 0}@media(max-width:760px){.page-product .product-feature-pills{grid-template-columns:repeat(2,minmax(0,1fr));gap:13px 10px}.page-product .product-feature-pills li{font-size:11px!important}.technical-fallback__stage{grid-template-columns:1fr;gap:18px;padding:16px;border-radius:14px}.technical-fallback__info h3{font-size:24px}.technical-dimensions{grid-template-columns:1fr}.technical-dimensions div:nth-child(odd),.technical-dimensions div:nth-child(even){padding:10px 0;border-left:0}.product-documents-refined{padding-top:46px;padding-bottom:46px}.product-documents-refined__grid{grid-template-columns:1fr}.upholstery-collections{margin-right:-18px;padding-right:18px}.upholstery-card{flex-basis:42vw!important}.quote-form{padding:20px}.quote-form .form-grid{grid-template-columns:1fr}.quote-form .form-wide{grid-column:auto}}
    `;
    doc.head.appendChild(style);
  }

  if (productHeading && model && !productHeading.textContent.toLocaleLowerCase().includes(model.toLocaleLowerCase())) {
    const modelSpan = doc.createElement('span');
    modelSpan.className = 'product-title__model';
    modelSpan.textContent = ` ${model}`;
    productHeading.appendChild(modelSpan);
  }

  const introInfo = doc.querySelector('.product-info-sticky');
  const detailsCopy = doc.querySelector('#details .rich-text--product');
  if (introInfo && !introInfo.querySelector('.product-lead') && detailsCopy) {
    const raw = String(detailsCopy.textContent || '').replace(/\s+/g, ' ').trim();
    if (raw) {
      const lead = doc.createElement('p');
      lead.className = 'product-lead';
      lead.textContent = raw.length > 230 ? `${raw.slice(0, 227).trim()}…` : raw;
      productHeading?.insertAdjacentElement('afterend', lead);
    }
  }

  const detailsHeading = doc.querySelector('#details .product-section__heading h2');
  if (detailsHeading) detailsHeading.textContent = text.full;

  const featureList = doc.querySelector('.product-feature-pills');
  if (featureList) {
    const current = [...featureList.querySelectorAll('li')].map((item) => item.textContent.trim().toLocaleLowerCase());
    const addFeature = (label, iconName) => {
      if (current.some((item) => item.includes(label.toLocaleLowerCase()))) return;
      const li = doc.createElement('li');
      li.innerHTML = `${icon(iconName)}<span>${escapeHtml(label)}</span>`;
      featureList.appendChild(li);
    };
    if (doc.querySelector('#upholstery .upholstery-card')) addFeature(text.fabrics, 'layers');
    addFeature(text.produced, 'factory');
    while (featureList.children.length > 6) featureList.lastElementChild?.remove();
  }

  const createW334Fallback = () => {
    if (model.toUpperCase() !== 'W-334' || section.querySelector('.technical-fallback')) return null;
    const body = section.querySelector('.product-section__body') || section;
    const block = doc.createElement('div');
    block.className = 'technical-fallback reveal is-visible';
    const dimensions = [[text.width,'105 cm'],[text.depth,'94 cm'],[text.height,'82 cm'],[text.armHeight,'71 cm'],[text.seatHeight,'44 cm'],[text.seatDepth,'55 cm'],[text.seatWidth,'52–80 cm']];
    block.innerHTML = `<div class="technical-fallback__stage"><div class="technical-fallback__visual"><img src="/assets/img/technical/w-334-sketch.jpg" alt="W-334 technical drawing" loading="lazy"></div><div class="technical-fallback__info"><p class="kicker">W-334 / ${escapeHtml(text.technical)}</p><h3>${escapeHtml(text.technical)}</h3><div class="technical-dimensions">${dimensions.map(([label,value]) => `<div><span>${escapeHtml(label)}</span><strong>${value}</strong></div>`).join('')}</div><p class="technical-footnote">${escapeHtml(text.footnote)}</p></div></div>`;
    body.prepend(block);
    return block;
  };
  const fallbackBlock = createW334Fallback();

  if (!doc.querySelector('link[data-technical-configurations-css]')) {
    const stylesheet = doc.createElement('link');
    stylesheet.rel = 'stylesheet';
    stylesheet.href = '/assets/css/technical-configurations.css?v=0.2.0';
    stylesheet.dataset.technicalConfigurationsCss = '';
    doc.head.appendChild(stylesheet);
  }

  const documentButtons = [...doc.querySelectorAll('.product-document-actions [data-pdf-open]')];
  const technicalSource = documentButtons[0] || null;
  const priceSource = documentButtons[1] || null;
  if (technicalSource) technicalSource.addEventListener('click', (event) => { event.preventDefault(); event.stopImmediatePropagation(); section.scrollIntoView({behavior:'smooth',block:'start'}); }, true);

  const detailsSection = doc.querySelector('#details');
  if (detailsSection && (technicalSource || priceSource) && !doc.getElementById('documents')) {
    const docsSection = doc.createElement('section');
    docsSection.className = 'product-documents-refined shell-wide';
    docsSection.id = 'documents';
    docsSection.innerHTML = `<div class="product-section__heading reveal is-visible"><p class="section-number">PDF</p><h2>${escapeHtml(text.docs)}</h2></div><div class="product-documents-refined__grid"></div>`;
    const grid = docsSection.querySelector('.product-documents-refined__grid');
    const addDoc = (source, label) => {
      if (!source || !grid) return;
      const button = doc.createElement('button');
      button.type = 'button'; button.className = 'product-document-card';
      button.innerHTML = `${icon('pdf')}<span><strong>${escapeHtml(label)}</strong><small>${escapeHtml(String(source.dataset.pdfTitle || 'PDF'))}</small></span>${icon('arrow-right')}`;
      button.addEventListener('click', () => {
        const pdf = doc.querySelector('[data-pdf-dialog]');
        const frame = pdf?.querySelector('[data-pdf-frame]');
        const title = pdf?.querySelector('[data-pdf-dialog-title]');
        if (frame) frame.src = source.dataset.pdfOpen || '';
        if (title) title.textContent = source.dataset.pdfTitle || 'PDF';
        if (pdf && typeof pdf.showModal === 'function' && !pdf.open) pdf.showModal();
      });
      grid.appendChild(button);
    };
    addDoc(technicalSource, text.techDoc); addDoc(priceSource, text.priceDoc);
    detailsSection.insertAdjacentElement('afterend', docsSection);
    if (priceSource) priceSource.addEventListener('click', (event) => { event.preventDefault(); event.stopImmediatePropagation(); docsSection.scrollIntoView({behavior:'smooth',block:'start'}); }, true);
  }

  doc.querySelectorAll('.upholstery-type').forEach((type) => {
    const head = type.querySelector('.upholstery-type__head');
    if (!head || head.querySelector('.upholstery-view-all')) return;
    const link = doc.createElement('a');
    link.className = 'upholstery-view-all';
    link.href = lang === 'bg' ? '/tapicerii/' : `/${lang}/upholstery/`;
    link.innerHTML = `<span>${escapeHtml(text.upholsteryAll)}</span>${icon('arrow-right')}`;
    head.appendChild(link);
  });

  const actions = doc.querySelector('.product-actions');
  if (actions && model.toUpperCase() === 'W-334' && !doc.querySelector('.showroom-marker')) {
    const marker = doc.createElement('div');
    marker.className = 'showroom-marker';
    marker.innerHTML = `${icon('map-pin')}<div><strong>${escapeHtml(text.showroom)}</strong><p>${escapeHtml(text.showroomText)}</p></div>`;
    actions.appendChild(marker);
  }

  const inquiryForm = doc.querySelector('#inquiry-dialog [data-inquiry-form]');
  if (inquiryForm && !inquiryForm.querySelector('[name="privacy"]')) {
    const submit = inquiryForm.querySelector('button[value="submit"]');
    const privacy = doc.createElement('label');
    privacy.className = 'quote-form__privacy';
    privacy.innerHTML = `<input type="checkbox" name="privacy" value="1" required><span>${escapeHtml(text.privacy)}</span>`;
    submit?.insertAdjacentElement('beforebegin', privacy);
  }

  const quoteButton = doc.querySelector('.quote-button');
  if (quoteButton && !doc.getElementById('quote-dialog')) {
    const dialog = doc.createElement('dialog');
    dialog.className = 'quote-dialog'; dialog.id = 'quote-dialog';
    dialog.innerHTML = `<form class="quote-form" data-quote-form><div class="dialog-top"><div><p class="kicker">${escapeHtml(model)}</p><h2>${escapeHtml(text.quoteTitle)}</h2></div><button type="button" data-quote-close aria-label="Close">×</button></div><input type="hidden" name="product_id" value="${productId}"><input type="hidden" name="request_type" value="quote"><input type="text" name="website" tabindex="-1" autocomplete="off" class="form-honeypot"><div class="form-grid"><label><span>${escapeHtml(text.company)} *</span><input name="company" required></label><label><span>${escapeHtml(text.companyId)} *</span><input name="company_id" required></label><label><span>${escapeHtml(text.name)} *</span><input name="name" required></label><label><span>${escapeHtml(text.phone)} *</span><input name="phone" type="tel" required></label><label><span>${escapeHtml(text.email)} *</span><input name="email" type="email" required></label><label><span>${escapeHtml(text.quantity)} *</span><input name="quantity" type="number" min="1" value="1" required></label><label><span>${escapeHtml(text.country)} *</span><input name="country" required></label><label><span>${escapeHtml(text.city)} *</span><input name="city" required></label><label class="form-wide"><span>${escapeHtml(text.delivery)} *</span><select name="delivery" required><option value="">—</option><option value="yes">${escapeHtml(text.yes)}</option><option value="no">${escapeHtml(text.no)}</option></select></label><label class="form-wide"><span>${escapeHtml(text.message)}</span><textarea name="message" rows="4"></textarea></label></div><label class="quote-form__privacy"><input type="checkbox" name="privacy" value="1" required><span>${escapeHtml(text.privacy)}</span></label><p class="form-status" aria-live="polite"></p><button class="button button--dark button--wide" type="submit">${escapeHtml(text.sendQuote)} ${icon('arrow-right')}</button></form>`;
    doc.body.appendChild(dialog);
    quoteButton.addEventListener('click', (event) => { event.preventDefault(); event.stopImmediatePropagation(); if (typeof dialog.showModal === 'function' && !dialog.open) dialog.showModal(); }, true);
    dialog.querySelector('[data-quote-close]')?.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
    const form = dialog.querySelector('[data-quote-form]');
    form?.addEventListener('submit', async (event) => {
      event.preventDefault();
      const submit = form.querySelector('button[type="submit"]'); const status = form.querySelector('.form-status'); const original = submit?.innerHTML || '';
      if (submit) { submit.disabled = true; submit.textContent = '…'; } if (status) status.textContent = '';
      try {
        const csrfResponse = await fetch('/api/csrf.php', {credentials:'same-origin',headers:{Accept:'application/json'}}); const csrfPayload = await csrfResponse.json(); if (!csrfPayload?.token) throw new Error('csrf');
        const data = new FormData(form); data.set('language', lang); data.set('selected_options', [...doc.querySelectorAll('.option-value.is-selected')].map((item)=>item.textContent.trim()).join('; ')); data.set('selected_upholstery', [...doc.querySelectorAll('.upholstery-card.is-selected')].map((item)=>item.textContent.trim()).join('; '));
        const response = await fetch('/api/product-inquiry.php',{method:'POST',credentials:'same-origin',headers:{'X-CSRF-Token':csrfPayload.token,Accept:'application/json'},body:data}); const payload = await response.json(); if (!response.ok || !payload?.ok) throw new Error(payload?.code || 'delivery');
        if (status) status.textContent = text.sent; form.reset();
      } catch (_) { if (status) status.textContent = text.failed; }
      finally { if (submit) { submit.disabled = false; submit.innerHTML = original; } }
    });
  }

  if (!productId) return;
  fetch(`/api/product-configurations.php?product_id=${encodeURIComponent(productId)}&lang=${encodeURIComponent(lang)}`, {credentials:'same-origin',headers:{Accept:'application/json'}})
    .then((response) => response.ok ? response.json() : null)
    .then((payload) => {
      const configurations = payload?.configurations || []; if (!Array.isArray(configurations) || configurations.length === 0) return;
      fallbackBlock?.remove();
      const body = section.querySelector('.product-section__body') || section; const block = doc.createElement('div'); block.className = 'technical-configurations reveal is-visible'; block.innerHTML = `<div class="technical-configurations__eyebrow">${icon('dimensions')}<span>${escapeHtml(text.technical)}</span></div><div class="technical-configurations__stage" data-technical-stage></div><div class="technical-configurations__rail" data-technical-rail aria-label="${escapeHtml(text.choose)}"></div>`; body.prepend(block);
      const stage = block.querySelector('[data-technical-stage]'); const rail = block.querySelector('[data-technical-rail]');
      const render = (index) => {
        const configuration = configurations[index]; if (!configuration || !stage) return; const media = Array.isArray(configuration.media) ? configuration.media : []; const diagram = media.find((item)=>['overview','diagram','dimension'].includes(item.role)) || media[0] || null; const attributes = Array.isArray(configuration.attributes) ? configuration.attributes : [];
        const diagramPath = diagram?.path ? '/' + cleanPath(diagram.path) : '';
        stage.innerHTML = `<div class="technical-configurations__visual">${diagramPath ? `<img src="${escapeHtml(diagramPath)}" alt="${escapeHtml(diagram.alt_text || configuration.name || '')}" loading="lazy">` : `<div class="technical-configurations__placeholder">${icon('dimensions')}<span>${escapeHtml(text.noImage)}</span></div>`}</div><div class="technical-configurations__info"><p class="kicker">${escapeHtml(configuration.code || '')}</p><h3>${escapeHtml(configuration.name || configuration.code || '')}</h3>${configuration.description ? `<p class="technical-configurations__description">${escapeHtml(configuration.description)}</p>` : ''}<div class="technical-configurations__attributes">${attributes.map((attribute)=>`<div><span>${escapeHtml(attribute.name)}</span><strong>${escapeHtml(attribute.value)}</strong></div>`).join('')}</div></div>`;
        rail?.querySelectorAll('button').forEach((button,buttonIndex)=>button.classList.toggle('is-active',buttonIndex===index));
      };
      configurations.forEach((configuration,index)=>{ const media = Array.isArray(configuration.media) ? configuration.media : []; const diagram = media.find((item)=>['overview','diagram','dimension'].includes(item.role)) || media[0] || null; const button = doc.createElement('button'); button.type='button'; button.className=index===0?'is-active':''; const diagramPath = diagram?.path ? '/' + cleanPath(diagram.path) : ''; button.innerHTML = `${diagramPath ? `<img src="${escapeHtml(diagramPath)}" alt="" loading="lazy">` : icon('dimensions')}<span>${escapeHtml(configuration.name || configuration.code || '')}</span>`; button.addEventListener('click',()=>render(index)); rail?.appendChild(button); });
      render(0);
    })
    .catch(()=>{});
})();

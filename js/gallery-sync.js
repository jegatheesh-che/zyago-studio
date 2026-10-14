/**
 * ============================================================
 * Zyago Studio — Public Gallery Live Sync (gallery-sync.js)
 * Non-destructive progressive enhancement:
 * Hydrates gallery grids with newly uploaded photographs from
 * the PHP admin backend while preserving 100% of existing UI/UX,
 * masonry styling, category filters, and lightbox interactions.
 * ============================================================
 */

(function () {
  'use strict';

  // Determine current page context
  const isMasterGalleries = document.getElementById('mainGalleryGrid') !== null;
  const isStudioPage = window.location.pathname.includes('studio-portrait') || document.querySelector('.masonry-gallery-grid[data-category="studio"]') !== null;
  const isEventsPage = window.location.pathname.includes('life-events') || document.querySelector('.masonry-gallery-grid[data-category="events"]') !== null;

  const targetContainer = document.getElementById('mainGalleryGrid') || document.querySelector('.masonry-gallery-grid');
  if (!targetContainer) return;

  // Determine appropriate API endpoint relative to current page
  const apiUrl = window.location.pathname.includes('/pages/') ? '../api/gallery.php' : 'api/gallery.php';

  let pageFilter = 'galleries';
  if (isStudioPage) pageFilter = 'studio-portrait';
  if (isEventsPage) pageFilter = 'life-events';

  // Fetch gallery catalogue filtered by active page
  const query = `?page=${encodeURIComponent(pageFilter)}`;
  fetch(apiUrl + query, { cache: 'no-cache' })
    .then(res => {
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    })
    .then(data => {
      if (!data || !data.ok || !Array.isArray(data.items)) return;

      const items = data.items;
      if (!items.length) return;

      const basePath = window.location.pathname.includes('/pages/') ? '../' : '';
      const currentImgs = Array.from(targetContainer.querySelectorAll('.curated-photo')).map(img => img.getAttribute('src'));
      const apiImgs = items.map(item => {
        return item.src.startsWith('http') || item.src.startsWith('/') 
          ? item.src 
          : basePath + item.src.replace(/^\.\.\//, '');
      });

      // If current DOM already matches the API items exactly, preserve static markup
      const isIdentical = currentImgs.length === apiImgs.length && currentImgs.every((src, idx) => src === apiImgs[idx]);
      if (isIdentical) {
        return;
      }

      // Re-hydrate grid with synchronized items while preserving identical markup structure
      const fragment = document.createDocumentFragment();

      items.forEach(item => {
        const article = document.createElement('article');
        article.className = `gallery-item ${item.grid_class || 'item-portrait'}`;
        article.dataset.category = item.category;

        // Secure source path
        const imgSrc = item.src.startsWith('http') || item.src.startsWith('/') 
          ? item.src 
          : basePath + item.src.replace(/^\.\.\//, '');

        article.innerHTML = `
          <img src="${escapeAttr(imgSrc)}" alt="${escapeAttr(item.alt || item.title)}" class="curated-photo" loading="lazy" decoding="async">
          <div class="gallery-item-overlay">
            <span class="gallery-item-category">${escapeHtml(item.category_label || (item.category === 'studio' ? 'Studio Portrait' : 'Life Events'))}</span>
            <h3 class="gallery-item-title">${escapeHtml(item.title)}</h3>
          </div>
        `;

        fragment.appendChild(article);
      });

      targetContainer.innerHTML = '';
      targetContainer.appendChild(fragment);

      // Re-initialize lightbox & filter listeners
      if (typeof window.initGalleryLightbox === 'function') {
        window.initGalleryLightbox();
      } else {
        bindLightboxFallback(targetContainer);
      }
    })
    .catch(() => {
      // Graceful fallback: If API is unavailable (static server or offline), static HTML is completely preserved
    });

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function escapeAttr(str) {
    if (!str) return '';
    return String(str).replace(/"/g, '&quot;');
  }

  function bindLightboxFallback(container) {
    const lightbox = document.getElementById('globalLightbox');
    if (!lightbox) return;

    const lbImg = document.getElementById('lightboxImg');
    const lbTitle = document.getElementById('lightboxTitle');
    const lbCat = document.getElementById('lightboxCat');
    const items = container.querySelectorAll('.gallery-item');

    items.forEach(item => {
      item.addEventListener('click', () => {
        const img = item.querySelector('img');
        const title = item.querySelector('.gallery-item-title');
        const cat = item.querySelector('.gallery-item-category');

        if (lbImg && img) lbImg.src = img.src;
        if (lbTitle && title) lbTitle.textContent = title.textContent;
        if (lbCat && cat) lbCat.textContent = cat.textContent;

        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
      });
    });
  }

})();

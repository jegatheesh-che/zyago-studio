/**
 * ============================================================
 * Zyago Studio — Admin Panel Interactions (admin.js)
 * Features:
 * - Multi-file and single photo upload queue with thumbnails
 * - Multi-photo selection and batch deletion
 * - Dynamic category filtering and search
 * - Drag and drop with progress bar
 * - Real-time counter updates
 * ============================================================
 */

(function () {
  'use strict';

  // ── Elements & State ─────────────────────────────────────────
  const csrfToken = document.getElementById('adminCsrfToken')?.value || '';
  let pendingDeleteId = null;
  let pendingDeleteCard = null;
  let isBatchDeleteMode = false;
  const selectedCardIds = new Set();
  const selectedCardsMap = new Map(); // id -> cardEl

  // ── Navigation & Sections ────────────────────────────────────
  const sidebar = document.getElementById('adminSidebar');
  const sidebarToggle = document.getElementById('sidebarOpenBtn') || document.getElementById('sidebarToggle');
  const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
  const sidebarBackdrop = document.getElementById('sidebarBackdrop');
  const sidebarLinks = document.querySelectorAll('.sidebar-link');
  const sections = document.querySelectorAll('.admin-section');
  const gotoButtons = document.querySelectorAll('[data-goto]');

  const topbarPageName = document.getElementById('topbarPageName');
  const sectionTitles = {
    dashSection: 'Dashboard',
    uploadSection: 'Upload Photos',
    gallerySection: 'Manage Gallery',
    aboutSection: 'Edit About Page'
  };

  function openSidebar() {
    if (sidebar) {
      sidebar.classList.add('open');
      sidebar.classList.add('is-open');
    }
    if (sidebarBackdrop) {
      sidebarBackdrop.classList.add('is-visible');
    }
    document.body.classList.add('sidebar-locked');
  }

  function closeSidebar() {
    if (sidebar) {
      sidebar.classList.remove('open');
      sidebar.classList.remove('is-open');
    }
    if (sidebarBackdrop) {
      sidebarBackdrop.classList.remove('is-visible');
    }
    document.body.classList.remove('sidebar-locked');
  }

  function showSection(sectionId) {
    sections.forEach(sec => {
      sec.classList.toggle('active', sec.id === sectionId);
    });
    sidebarLinks.forEach(link => {
      link.classList.toggle('active', link.dataset.section === sectionId);
    });
    if (topbarPageName && sectionTitles[sectionId]) {
      topbarPageName.textContent = sectionTitles[sectionId];
    }
    // Close mobile sidebar if open
    closeSidebar();
    // Scroll to top of section for mobile usability
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  sidebarLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const secId = link.dataset.section;
      if (secId) showSection(secId);
    });
  });

  gotoButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const secId = btn.dataset.goto;
      if (secId) showSection(secId);
    });
  });

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      if (sidebar && (sidebar.classList.contains('open') || sidebar.classList.contains('is-open'))) {
        closeSidebar();
      } else {
        openSidebar();
      }
    });
  }

  if (sidebarCloseBtn) {
    sidebarCloseBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      closeSidebar();
    });
  }

  if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', closeSidebar);
  }

  // ── Black / White Theme Switcher ─────────────────────────────
  const themeToggleBtn = document.getElementById('themeToggleBtn');
  const themeToggleLabel = document.getElementById('themeToggleLabel');

  function updateThemeUI(t) {
    document.documentElement.setAttribute('data-theme', t);
    if (themeToggleLabel) {
      themeToggleLabel.textContent = t === 'light' ? 'Alabaster' : 'Noir';
    }
  }

  // Ensure label matches current active theme attribute
  const activeTheme = document.documentElement.getAttribute('data-theme') || 'dark';
  updateThemeUI(activeTheme);

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
      const current = document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
      const next = current === 'light' ? 'dark' : 'light';
      updateThemeUI(next);
      try {
        localStorage.setItem('zyago_admin_theme', next);
      } catch (e) {}
      showToast(next === 'light' ? 'Switched to Alabaster (White) Theme' : 'Switched to Noir (Black) Theme', 'info');
    });
  }

  // ── Category & Grid Visual Radio Selectors ───────────────────
  const categoryOptions = document.querySelectorAll('.category-option');
  categoryOptions.forEach(opt => {
    const radio = opt.querySelector('input[type="radio"]');
    opt.addEventListener('click', () => {
      categoryOptions.forEach(o => o.classList.remove('active'));
      opt.classList.add('active');
      if (radio) radio.checked = true;
    });
  });

  const gridOptions = document.querySelectorAll('.grid-option');
  gridOptions.forEach(opt => {
    const radio = opt.querySelector('input[type="radio"]');
    opt.addEventListener('click', () => {
      gridOptions.forEach(o => o.classList.remove('active'));
      opt.classList.add('active');
      if (radio) radio.checked = true;
    });
  });

  // ── Multi-File Photo Upload Queue ────────────────────────────
  let uploadFilesQueue = []; // Array of File objects

  const fileDropZone = document.getElementById('fileDropZone');
  const photoFileInput = document.getElementById('photoFile');
  const browseTrigger = document.getElementById('browseTrigger');
  const dropZoneContent = document.getElementById('dropZoneContent');
  const dropZonePreview = document.getElementById('dropZonePreview');
  const multiPreviewGrid = document.getElementById('multiPreviewGrid');
  const previewCountEl = document.getElementById('previewCount');
  const previewTotalSizeEl = document.getElementById('previewTotalSize');
  const addMoreFilesBtn = document.getElementById('addMoreFilesBtn');
  const removeFileBtn = document.getElementById('removeFileBtn');

  const photoTitleInput = document.getElementById('photoTitle');
  const uploadForm = document.getElementById('uploadForm');
  const uploadSubmitBtn = document.getElementById('uploadSubmitBtn');
  const uploadBtnText = document.getElementById('uploadBtnText');
  const uploadProgressWrap = document.getElementById('uploadProgressWrap');
  const uploadProgressBar = document.getElementById('uploadProgressBar');
  const uploadProgressLabel = document.getElementById('uploadProgressLabel');
  const uploadAlert = document.getElementById('uploadAlert');

  function formatBytes(bytes, decimals = 1) {
    if (!+bytes) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
  }

  function addFilesToQueue(fileList) {
    if (!fileList || !fileList.length) return;

    const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    let rejectedCount = 0;

    Array.from(fileList).forEach(file => {
      // Validate type
      if (!validTypes.includes(file.type)) {
        rejectedCount++;
        return;
      }
      // Validate size (max 25MB)
      if (file.size > 25 * 1024 * 1024) {
        rejectedCount++;
        return;
      }
      // Check duplicate in current queue
      const isDup = uploadFilesQueue.some(f => f.name === file.name && f.size === file.size);
      if (!isDup) {
        uploadFilesQueue.push(file);
      }
    });

    if (rejectedCount > 0) {
      showAlert(uploadAlert, 'error', `${rejectedCount} file(s) were skipped (must be JPEG, PNG, or WebP under 25 MB).`);
    } else if (uploadAlert) {
      uploadAlert.style.display = 'none';
    }

    renderMultiPreview();
  }

  function removeFileFromQueue(index) {
    uploadFilesQueue.splice(index, 1);
    renderMultiPreview();
  }

  function clearFilesQueue() {
    uploadFilesQueue = [];
    if (photoFileInput) photoFileInput.value = '';
    renderMultiPreview();
  }

  function renderMultiPreview() {
    if (!multiPreviewGrid) return;
    multiPreviewGrid.innerHTML = '';

    const count = uploadFilesQueue.length;

    if (count === 0) {
      if (dropZonePreview) dropZonePreview.style.display = 'none';
      if (dropZoneContent) dropZoneContent.style.display = 'block';
      if (uploadSubmitBtn) uploadSubmitBtn.disabled = true;
      if (uploadBtnText) uploadBtnText.textContent = 'Upload to Gallery';
      return;
    }

    if (dropZoneContent) dropZoneContent.style.display = 'none';
    if (dropZonePreview) dropZonePreview.style.display = 'flex';

    let totalBytes = 0;

    uploadFilesQueue.forEach((file, idx) => {
      totalBytes += file.size;

      const card = document.createElement('div');
      card.className = 'multi-preview-card';

      const thumbUrl = URL.createObjectURL(file);

      card.innerHTML = `
        <div class="mpc-thumb-wrap">
          <img src="${thumbUrl}" alt="Preview" class="mpc-thumb-img">
          <button type="button" class="mpc-remove-btn" data-index="${idx}" title="Remove this photo">✕</button>
        </div>
        <div class="mpc-meta">
          <span class="mpc-name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</span>
          <span class="mpc-size">${formatBytes(file.size)}</span>
        </div>
      `;

      multiPreviewGrid.appendChild(card);
    });

    if (previewCountEl) previewCountEl.textContent = `${count} photo${count > 1 ? 's' : ''} selected`;
    if (previewTotalSizeEl) previewTotalSizeEl.textContent = formatBytes(totalBytes);

    if (uploadSubmitBtn) uploadSubmitBtn.disabled = false;
    if (uploadBtnText) {
      uploadBtnText.textContent = (count === 1) ? 'Upload Photo to Gallery' : `Upload ${count} Photos to Gallery`;
    }
  }

  // Delegated remove click inside multi-preview grid
  if (multiPreviewGrid) {
    multiPreviewGrid.addEventListener('click', (e) => {
      const btn = e.target.closest('.mpc-remove-btn');
      if (btn) {
        e.stopPropagation();
        const idx = parseInt(btn.dataset.index, 10);
        if (!isNaN(idx)) removeFileFromQueue(idx);
      }
    });
  }

  if (browseTrigger && photoFileInput) {
    browseTrigger.addEventListener('click', (e) => {
      e.stopPropagation();
      photoFileInput.click();
    });
  }

  if (addMoreFilesBtn && photoFileInput) {
    addMoreFilesBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      photoFileInput.click();
    });
  }

  if (removeFileBtn) {
    removeFileBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      clearFilesQueue();
    });
  }

  if (fileDropZone && photoFileInput) {
    fileDropZone.addEventListener('click', (e) => {
      if (e.target.closest('.mpc-remove-btn') || e.target.closest('.btn-preview-clear') || e.target.closest('.btn-preview-add')) {
        return;
      }
      if (uploadFilesQueue.length === 0) {
        photoFileInput.click();
      }
    });

    fileDropZone.addEventListener('dragover', (e) => {
      e.preventDefault();
      fileDropZone.classList.add('dragover');
    });

    fileDropZone.addEventListener('dragleave', () => {
      fileDropZone.classList.remove('dragover');
    });

    fileDropZone.addEventListener('drop', (e) => {
      e.preventDefault();
      fileDropZone.classList.remove('dragover');
      if (e.dataTransfer && e.dataTransfer.files) {
        addFilesToQueue(e.dataTransfer.files);
      }
    });
  }

  if (photoFileInput) {
    photoFileInput.addEventListener('change', () => {
      if (photoFileInput.files) {
        addFilesToQueue(photoFileInput.files);
      }
    });
  }

  // ── Upload Form Submit ───────────────────────────────────────
  if (uploadForm) {
    uploadForm.addEventListener('submit', (e) => {
      e.preventDefault();

      if (!uploadFilesQueue.length) {
        showAlert(uploadAlert, 'error', 'Please select at least one photo to upload.');
        return;
      }

      const formData = new FormData(uploadForm);
      if (csrfToken) formData.set('csrf_token', csrfToken);

      // Remove default file input value and append all queued files
      formData.delete('photo');
      formData.delete('photos[]');
      uploadFilesQueue.forEach(file => {
        formData.append('photos[]', file);
      });

      // UI uploading state
      uploadSubmitBtn.disabled = true;
      if (uploadProgressWrap) {
        uploadProgressWrap.style.display = 'block';
        if (uploadProgressBar) uploadProgressBar.style.width = '10%';
        if (uploadProgressLabel) uploadProgressLabel.textContent = `Preparing upload (${uploadFilesQueue.length} files)…`;
      }
      if (uploadAlert) uploadAlert.style.display = 'none';

      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'api/upload.php', true);

      xhr.upload.onprogress = (evt) => {
        if (evt.lengthComputable) {
          const percent = Math.round((evt.loaded / evt.total) * 92);
          if (uploadProgressBar) uploadProgressBar.style.width = percent + '%';
          if (uploadProgressLabel) uploadProgressLabel.textContent = `Uploading… ${percent}%`;
        }
      };

      xhr.onload = () => {
        if (uploadProgressBar) uploadProgressBar.style.width = '100%';
        if (uploadProgressLabel) uploadProgressLabel.textContent = 'Finalizing catalog updates…';

        let res;
        try {
          res = JSON.parse(xhr.responseText);
        } catch (err) {
          res = { ok: false, error: 'Server returned an invalid response.' };
        }

        setTimeout(() => {
          if (uploadProgressWrap) uploadProgressWrap.style.display = 'none';

          if (xhr.status === 200 && res.ok) {
            const count = res.count || (res.items ? res.items.length : 1);
            const msg = res.message || `Successfully uploaded ${count} photo${count > 1 ? 's' : ''}!`;

            showAlert(uploadAlert, 'success', msg);
            showToast(msg, 'success');

            // Reset form and queue
            uploadForm.reset();
            clearFilesQueue();

            // Append newly uploaded items into gallery grid
            const items = res.items || (res.item ? [res.item] : []);
            items.forEach(item => {
              appendGalleryItem(item);
              updateStatsAfterUpload(item.category);
            });
          } else {
            uploadSubmitBtn.disabled = false;
            showAlert(uploadAlert, 'error', res.error || 'Upload failed. Please check files and try again.');
          }
        }, 350);
      };

      xhr.onerror = () => {
        if (uploadProgressWrap) uploadProgressWrap.style.display = 'none';
        uploadSubmitBtn.disabled = false;
        showAlert(uploadAlert, 'error', 'Network error. Please check your connection and try again.');
      };

      xhr.send(formData);
    });
  }

  // ── Gallery Management Filters & Search ──────────────────────
  const filterPills = document.querySelectorAll('.gallery-filter-pill');
  const galleryGrid = document.getElementById('adminGalleryGrid');
  let currentFilter = 'all';

  function applyAdminFilter(filter) {
    currentFilter = filter;
    filterPills.forEach(p => p.classList.toggle('active', p.dataset.filter === filter));
    const cards = galleryGrid ? galleryGrid.querySelectorAll('.admin-gallery-card') : [];
    cards.forEach(card => {
      const cat = card.dataset.category;
      const isDefault = card.dataset.default === '1';
      const pages = (card.dataset.pages || '').split(',').map(s => s.trim());

      let match = false;
      if (filter === 'all') match = true;
      else if (filter === 'studio') match = (cat === 'studio');
      else if (filter === 'events') match = (cat === 'events');
      else if (filter === 'page-studio') match = pages.includes('studio-portrait');
      else if (filter === 'page-events') match = pages.includes('life-events');
      else if (filter === 'page-galleries') match = pages.includes('galleries');
      else if (filter === 'new') match = !isDefault;

      card.style.display = match ? 'block' : 'none';
    });

    syncSelectAllCheckbox();
  }

  filterPills.forEach(pill => {
    pill.addEventListener('click', () => {
      applyAdminFilter(pill.dataset.filter);
    });
  });

  // Quick stat click filter shortcuts
  const statFilterCards = document.querySelectorAll('.stat-card[data-filter-cat]');
  statFilterCards.forEach(card => {
    card.addEventListener('click', () => {
      const cat = card.dataset.filterCat;
      showSection('gallerySection');
      applyAdminFilter(cat);
    });
  });

  // ── Multiple Selection & Batch Deletion System ───────────────
  const selectAllCheckbox = document.getElementById('selectAllCheckbox');
  const batchSelectedCount = document.getElementById('batchSelectedCount');
  const batchDeleteBtn = document.getElementById('batchDeleteBtn');
  const batchDeleteNum = document.getElementById('batchDeleteNum');
  const batchCancelBtn = document.getElementById('batchCancelBtn');

  function updateBatchToolbar() {
    const count = selectedCardIds.size;

    if (batchSelectedCount) {
      batchSelectedCount.textContent = `${count} selected`;
      batchSelectedCount.style.display = count > 0 ? 'inline-block' : 'none';
    }

    if (batchDeleteNum) batchDeleteNum.textContent = count;

    if (batchDeleteBtn) {
      batchDeleteBtn.disabled = (count === 0);
    }

    if (batchCancelBtn) {
      batchCancelBtn.style.display = count > 0 ? 'inline-block' : 'none';
    }

    syncSelectAllCheckbox();
  }

  function syncSelectAllCheckbox() {
    if (!selectAllCheckbox || !galleryGrid) return;

    // Get all visible deletable cards
    const visibleDeletableCards = Array.from(galleryGrid.querySelectorAll('.admin-gallery-card.is-new'))
      .filter(card => card.style.display !== 'none');

    if (visibleDeletableCards.length === 0) {
      selectAllCheckbox.checked = false;
      selectAllCheckbox.disabled = true;
      return;
    }

    selectAllCheckbox.disabled = false;
    const allSelected = visibleDeletableCards.every(card => selectedCardIds.has(card.dataset.id));
    selectAllCheckbox.checked = allSelected && visibleDeletableCards.length > 0;
  }

  // Handle individual card checkbox change
  if (galleryGrid) {
    galleryGrid.addEventListener('change', (e) => {
      if (e.target.classList.contains('agc-card-cb')) {
        const cb = e.target;
        const id = cb.dataset.id;
        const card = cb.closest('.admin-gallery-card');

        if (cb.checked) {
          selectedCardIds.add(id);
          selectedCardsMap.set(id, card);
          if (card) card.classList.add('is-selected');
        } else {
          selectedCardIds.delete(id);
          selectedCardsMap.delete(id);
          if (card) card.classList.remove('is-selected');
        }

        updateBatchToolbar();
      }
    });
  }

  // Handle "Select All" toggle
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', () => {
      if (!galleryGrid) return;

      const visibleDeletableCards = Array.from(galleryGrid.querySelectorAll('.admin-gallery-card.is-new'))
        .filter(card => card.style.display !== 'none');

      const shouldSelect = selectAllCheckbox.checked;

      visibleDeletableCards.forEach(card => {
        const id = card.dataset.id;
        const cb = card.querySelector('.agc-card-cb');

        if (shouldSelect) {
          selectedCardIds.add(id);
          selectedCardsMap.set(id, card);
          card.classList.add('is-selected');
          if (cb) cb.checked = true;
        } else {
          selectedCardIds.delete(id);
          selectedCardsMap.delete(id);
          card.classList.remove('is-selected');
          if (cb) cb.checked = false;
        }
      });

      updateBatchToolbar();
    });
  }

  // Handle "Deselect" button
  if (batchCancelBtn) {
    batchCancelBtn.addEventListener('click', () => {
      deselectAllPhotos();
    });
  }

  function deselectAllPhotos() {
    selectedCardIds.clear();
    selectedCardsMap.clear();

    if (galleryGrid) {
      galleryGrid.querySelectorAll('.agc-card-cb').forEach(cb => { cb.checked = false; });
      galleryGrid.querySelectorAll('.admin-gallery-card.is-selected').forEach(c => { c.classList.remove('is-selected'); });
    }

    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    updateBatchToolbar();
  }

  // ── Delete Confirmation Modal (Supports Single & Batch) ─────
  const deleteModalOverlay = document.getElementById('deleteModalOverlay');
  const deleteModalTitle = document.getElementById('deleteModalTitle');
  const deleteConfirmPrompt = document.getElementById('deleteConfirmPrompt');
  const deleteConfirmTitle = document.getElementById('deleteConfirmTitle');
  const modalDeleteBtnText = document.getElementById('modalDeleteBtnText');
  const modalCancelBtn = document.getElementById('modalCancelBtn');
  const modalCancelBtn2 = document.getElementById('modalCancelBtn2');
  const modalDeleteConfirmBtn = document.getElementById('modalDeleteConfirmBtn');
  const deleteAlert = document.getElementById('deleteAlert');

  function openSingleDeleteModal(photoId, photoTitle, cardEl) {
    isBatchDeleteMode = false;
    pendingDeleteId = photoId;
    pendingDeleteCard = cardEl;

    if (deleteModalTitle) deleteModalTitle.textContent = 'Delete Photo';
    if (deleteConfirmPrompt) deleteConfirmPrompt.textContent = 'Are you sure you want to permanently delete:';
    if (deleteConfirmTitle) deleteConfirmTitle.textContent = photoTitle;
    if (modalDeleteBtnText) modalDeleteBtnText.textContent = 'Yes, Delete Permanently';
    if (deleteModalOverlay) deleteModalOverlay.style.display = 'flex';
  }

  function openBatchDeleteModal() {
    const count = selectedCardIds.size;
    if (count === 0) return;

    isBatchDeleteMode = true;
    pendingDeleteId = null;
    pendingDeleteCard = null;

    if (deleteModalTitle) deleteModalTitle.textContent = `Delete ${count} Photographs`;
    if (deleteConfirmPrompt) deleteConfirmPrompt.textContent = `Are you sure you want to permanently delete these:`;
    if (deleteConfirmTitle) deleteConfirmTitle.textContent = `${count} selected photographs`;
    if (modalDeleteBtnText) modalDeleteBtnText.textContent = `Yes, Delete All ${count} Photos`;
    if (deleteModalOverlay) deleteModalOverlay.style.display = 'flex';
  }

  function closeDeleteModal() {
    pendingDeleteId = null;
    pendingDeleteCard = null;
    isBatchDeleteMode = false;
    if (deleteModalOverlay) deleteModalOverlay.style.display = 'none';
  }

  if (modalCancelBtn) modalCancelBtn.addEventListener('click', closeDeleteModal);
  if (modalCancelBtn2) modalCancelBtn2.addEventListener('click', closeDeleteModal);

  if (deleteModalOverlay) {
    deleteModalOverlay.addEventListener('click', (e) => {
      if (e.target === deleteModalOverlay) closeDeleteModal();
    });
  }

  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && deleteModalOverlay && deleteModalOverlay.style.display === 'flex') {
      closeDeleteModal();
    }
  });

  // Batch delete button click opens modal
  if (batchDeleteBtn) {
    batchDeleteBtn.addEventListener('click', () => {
      if (selectedCardIds.size > 0) {
        openBatchDeleteModal();
      }
    });
  }

  // Event delegation for single delete buttons in gallery grid
  if (galleryGrid) {
    galleryGrid.addEventListener('click', (e) => {
      const delBtn = e.target.closest('.agc-delete-btn');
      if (delBtn) {
        const id = delBtn.dataset.id;
        const title = delBtn.dataset.title;
        const card = delBtn.closest('.admin-gallery-card');
        if (id) {
          openSingleDeleteModal(id, title || 'Selected Photo', card);
        }
      }
    });
  }

  // Confirm Delete Action (Handles both Single & Batch)
  if (modalDeleteConfirmBtn) {
    modalDeleteConfirmBtn.addEventListener('click', () => {
      modalDeleteConfirmBtn.disabled = true;
      modalDeleteConfirmBtn.innerHTML = '<span class="spinner-sm"></span> Deleting…';

      if (isBatchDeleteMode) {
        // Batch deletion
        const idsList = Array.from(selectedCardIds);

        fetch('api/delete.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            ids: idsList,
            csrf_token: csrfToken
          })
        })
        .then(res => res.json())
        .then(data => {
          modalDeleteConfirmBtn.disabled = false;
          modalDeleteConfirmBtn.innerHTML = `
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
            <span id="modalDeleteBtnText">Yes, Delete Permanently</span>
          `;

          if (data.ok) {
            const deletedIds = data.deleted_ids || (Array.isArray(data.deleted) ? data.deleted : [data.deleted]);

            deletedIds.forEach(id => {
              const card = selectedCardsMap.get(id) || galleryGrid.querySelector(`.admin-gallery-card[data-id="${id}"]`);
              if (card) {
                const cat = card.dataset.category;
                card.style.transition = 'opacity 0.3s, transform 0.3s';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.85)';
                setTimeout(() => {
                  card.remove();
                  updateStatsAfterDelete(cat);
                }, 300);
              }
            });

            closeDeleteModal();
            deselectAllPhotos();
            showToast(data.message || `Deleted ${deletedIds.length} photos successfully.`, 'success');
          } else {
            closeDeleteModal();
            showAlert(deleteAlert, 'error', data.error || 'Failed to delete selected photos.');
          }
        })
        .catch(() => {
          modalDeleteConfirmBtn.disabled = false;
          modalDeleteConfirmBtn.innerHTML = 'Yes, Delete Permanently';
          closeDeleteModal();
          showAlert(deleteAlert, 'error', 'Network error during batch deletion.');
        });

      } else {
        // Single deletion
        if (!pendingDeleteId) return;

        fetch('api/delete.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            id: pendingDeleteId,
            csrf_token: csrfToken
          })
        })
        .then(res => res.json())
        .then(data => {
          modalDeleteConfirmBtn.disabled = false;
          modalDeleteConfirmBtn.innerHTML = `
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
            <span id="modalDeleteBtnText">Yes, Delete Permanently</span>
          `;

          if (data.ok) {
            const deletedCard = pendingDeleteCard;
            const cat = deletedCard ? deletedCard.dataset.category : null;
            closeDeleteModal();

            if (deletedCard) {
              deletedCard.style.transition = 'opacity 0.3s, transform 0.3s';
              deletedCard.style.opacity = '0';
              deletedCard.style.transform = 'scale(0.85)';
              setTimeout(() => {
                deletedCard.remove();
                updateStatsAfterDelete(cat);
              }, 300);
            }

            showToast('Photo removed successfully.', 'success');
          } else {
            closeDeleteModal();
            showAlert(deleteAlert, 'error', data.error || 'Failed to delete photo.');
          }
        })
        .catch(() => {
          modalDeleteConfirmBtn.disabled = false;
          modalDeleteConfirmBtn.innerHTML = 'Yes, Delete Permanently';
          closeDeleteModal();
          showAlert(deleteAlert, 'error', 'Network error while attempting to delete.');
        });
      }
    });
  }

  // ── Category Radio & Page Checkboxes Auto-Sync ──────────────
  const catRadios = document.querySelectorAll('input[name="category"]');
  const pageCheckStudio = document.getElementById('pageCheckStudio');
  const pageCheckEvents = document.getElementById('pageCheckEvents');
  const pageCheckGalleries = document.getElementById('pageCheckGalleries');

  catRadios.forEach(radio => {
    radio.addEventListener('change', () => {
      if (radio.value === 'studio') {
        if (pageCheckStudio) pageCheckStudio.checked = true;
        if (pageCheckEvents) pageCheckEvents.checked = false;
      } else if (radio.value === 'events') {
        if (pageCheckStudio) pageCheckStudio.checked = false;
        if (pageCheckEvents) pageCheckEvents.checked = true;
      }
      if (pageCheckGalleries) pageCheckGalleries.checked = true;
    });
  });

  // ── Edit Photo Placement & Details Modal ─────────────────────
  const editModalOverlay  = document.getElementById('editModalOverlay');
  const editModalCancelBtn = document.getElementById('editModalCancelBtn');
  const editModalCancelX  = document.getElementById('editModalCancelX');
  const editPhotoForm     = document.getElementById('editPhotoForm');
  const editPhotoId       = document.getElementById('editPhotoId');
  const editModalThumb    = document.getElementById('editModalThumb');
  const editPhotoTitle    = document.getElementById('editPhotoTitle');
  const editPhotoAlt      = document.getElementById('editPhotoAlt');
  const editPageGalleries = document.getElementById('editPageGalleries');
  const editPageStudio    = document.getElementById('editPageStudio');
  const editPageEvents    = document.getElementById('editPageEvents');
  const editModalAlert    = document.getElementById('editModalAlert');
  const editModalSaveBtn  = document.getElementById('editModalSaveBtn');
  let currentEditingCard  = null;

  function openEditModal(card) {
    if (!card) return;
    currentEditingCard = card;
    const id    = card.dataset.id;
    const title = card.dataset.title || '';
    const alt   = card.dataset.alt || '';
    const pages = (card.dataset.pages || '').split(',').map(s => s.trim());
    const grid  = card.dataset.grid || 'item-portrait';
    const src   = card.dataset.src || card.querySelector('.agc-img')?.getAttribute('src') || '';

    if (editPhotoId) editPhotoId.value = id;
    if (editModalThumb) editModalThumb.src = src;
    if (editPhotoTitle) editPhotoTitle.value = title;
    if (editPhotoAlt) editPhotoAlt.value = alt;

    if (editPageGalleries) editPageGalleries.checked = pages.includes('galleries');
    if (editPageStudio) editPageStudio.checked = pages.includes('studio-portrait');
    if (editPageEvents) editPageEvents.checked = pages.includes('life-events');

    const gridRadio = document.querySelector(`input[name="edit_grid_class"][value="${grid}"]`);
    if (gridRadio) gridRadio.checked = true;

    if (editModalAlert) editModalAlert.style.display = 'none';
    if (editModalOverlay) editModalOverlay.style.display = 'flex';
  }

  function closeEditModal() {
    if (editModalOverlay) editModalOverlay.style.display = 'none';
    currentEditingCard = null;
  }

  if (editModalCancelBtn) editModalCancelBtn.addEventListener('click', closeEditModal);
  if (editModalCancelX) editModalCancelX.addEventListener('click', closeEditModal);

  // Delegated click for .agc-edit-btn on cards
  if (galleryGrid) {
    galleryGrid.addEventListener('click', (e) => {
      const editBtn = e.target.closest('.agc-edit-btn');
      if (editBtn) {
        e.stopPropagation();
        const card = editBtn.closest('.admin-gallery-card');
        if (card) openEditModal(card);
      }
    });
  }

  if (editPhotoForm) {
    editPhotoForm.addEventListener('submit', (e) => {
      e.preventDefault();
      if (!currentEditingCard) return;

      const id    = editPhotoId.value;
      const title = editPhotoTitle.value.trim();
      const alt   = editPhotoAlt.value.trim();
      const grid  = document.querySelector('input[name="edit_grid_class"]:checked')?.value || 'item-portrait';

      const selectedPages = [];
      if (editPageGalleries?.checked) selectedPages.push('galleries');
      if (editPageStudio?.checked) selectedPages.push('studio-portrait');
      if (editPageEvents?.checked) selectedPages.push('life-events');

      if (!selectedPages.length) {
        showAlert(editModalAlert, 'error', 'Please select at least one page for this photo to appear on.');
        return;
      }

      if (editModalSaveBtn) {
        editModalSaveBtn.disabled = true;
        editModalSaveBtn.textContent = 'Saving…';
      }

      fetch('api/update_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: id,
          title: title,
          alt: alt,
          grid_class: grid,
          pages: selectedPages,
          csrf_token: csrfToken
        })
      })
      .then(res => res.json())
      .then(data => {
        if (editModalSaveBtn) {
          editModalSaveBtn.disabled = false;
          editModalSaveBtn.innerHTML = `
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <span>Save Changes</span>
          `;
        }

        if (data.ok) {
          const card = currentEditingCard;
          card.dataset.pages = selectedPages.join(',');
          card.dataset.title = title;
          card.dataset.alt   = alt;
          card.dataset.grid  = grid;

          const titleEl = card.querySelector('.agc-title');
          if (titleEl) titleEl.textContent = title;

          const tagsWrap = card.querySelector('.agc-page-tags');
          if (tagsWrap) {
            tagsWrap.innerHTML = `
              ${selectedPages.includes('studio-portrait') ? '<span class="page-tag page-tag-studio" title="Active on Studio Portrait Page">Studio</span>' : ''}
              ${selectedPages.includes('life-events') ? '<span class="page-tag page-tag-events" title="Active on Life Events Page">Events</span>' : ''}
              ${selectedPages.includes('galleries') ? '<span class="page-tag page-tag-galleries" title="Active on Main Galleries">Galleries</span>' : ''}
            `;
          }

          recalculatePageBadgeCounts();
          closeEditModal();
          showToast('Photo page placement updated.', 'success');

          // Re-apply active filter so view updates accurately
          applyAdminFilter(currentFilter);
        } else {
          showAlert(editModalAlert, 'error', data.error || 'Failed to update photo placement.');
        }
      })
      .catch(() => {
        if (editModalSaveBtn) {
          editModalSaveBtn.disabled = false;
          editModalSaveBtn.innerHTML = `<span>Save Changes</span>`;
        }
        showAlert(editModalAlert, 'error', 'Network error while updating photo.');
      });
    });
  }

  function recalculatePageBadgeCounts() {
    if (!galleryGrid) return;
    const cards = Array.from(galleryGrid.querySelectorAll('.admin-gallery-card'));
    let studioPCount = 0;
    let eventsPCount = 0;
    let galleriesPCount = 0;

    cards.forEach(card => {
      const p = (card.dataset.pages || '').split(',').map(s => s.trim());
      if (p.includes('studio-portrait')) studioPCount++;
      if (p.includes('life-events')) eventsPCount++;
      if (p.includes('galleries')) galleriesPCount++;
    });

    const bStudio = document.querySelector('.badge-page-studio-count');
    if (bStudio) bStudio.textContent = studioPCount;

    const bEvents = document.querySelector('.badge-page-events-count');
    if (bEvents) bEvents.textContent = eventsPCount;

    const bGalleries = document.querySelector('.badge-page-galleries-count');
    if (bGalleries) bGalleries.textContent = galleriesPCount;
  }

  // ── Helper: Append Newly Uploaded Item to DOM ────────────────
  function appendGalleryItem(item) {
    if (!galleryGrid) return;

    const card = document.createElement('div');
    card.className = 'admin-gallery-card is-new';
    card.dataset.id = item.id;
    card.dataset.category = item.category;
    card.dataset.default = '0';
    const itemPages = item.pages || [];
    card.dataset.pages = itemPages.join(',');
    card.dataset.title = item.title;
    card.dataset.alt = item.alt || '';
    card.dataset.grid = item.grid_class || 'item-portrait';
    card.dataset.src = `../${item.src}`;

    const dateStr = item.uploaded_at ? new Date(item.uploaded_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'Just now';

    card.innerHTML = `
      <div class="agc-img-wrap">
        <label class="agc-select-wrap" title="Select photo for batch deletion">
          <input type="checkbox" class="agc-card-cb" data-id="${escapeHtml(item.id)}" data-title="${escapeHtml(item.title)}">
          <span class="agc-cb-custom"></span>
        </label>
        <img src="../${escapeHtml(item.src)}" alt="${escapeHtml(item.alt || '')}" class="agc-img" loading="lazy">
        <div class="agc-overlay">
          <button type="button"
                  class="agc-edit-btn"
                  data-id="${escapeHtml(item.id)}"
                  data-title="${escapeHtml(item.title)}"
                  data-alt="${escapeHtml(item.alt || '')}"
                  data-grid="${escapeHtml(item.grid_class || 'item-portrait')}"
                  data-pages="${escapeHtml(itemPages.join(','))}"
                  data-src="../${escapeHtml(item.src)}"
                  aria-label="Edit photo placement and details">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>
          <button type="button"
                  class="agc-delete-btn"
                  data-id="${escapeHtml(item.id)}"
                  data-title="${escapeHtml(item.title)}"
                  aria-label="Delete photo">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            Delete
          </button>
        </div>
      </div>
      <div class="agc-meta">
        <div class="agc-meta-top">
          <span class="agc-cat-badge agc-cat-${escapeHtml(item.category)}">${escapeHtml(item.category_label || item.category)}</span>
          <div class="agc-page-tags">
            ${itemPages.includes('studio-portrait') ? '<span class="page-tag page-tag-studio" title="Active on Studio Portrait Page">Studio</span>' : ''}
            ${itemPages.includes('life-events') ? '<span class="page-tag page-tag-events" title="Active on Life Events Page">Events</span>' : ''}
            ${itemPages.includes('galleries') ? '<span class="page-tag page-tag-galleries" title="Active on Main Galleries">Galleries</span>' : ''}
          </div>
        </div>
        <span class="agc-title">${escapeHtml(item.title)}</span>
        <span class="agc-date">${dateStr}</span>
      </div>
    `;

    galleryGrid.insertBefore(card, galleryGrid.firstChild);
    recalculatePageBadgeCounts();
    syncSelectAllCheckbox();
  }

  // ── Helper: Dynamic Counter Updates ──────────────────────────
  function updateStatsAfterUpload(category) {
    updateBadgeCount('.badge-total-count', 1);
    updateBadgeCount('.badge-new-count', 1);
    if (category === 'studio') {
      updateBadgeCount('.badge-studio-count', 1);
    } else if (category === 'events') {
      updateBadgeCount('.badge-events-count', 1);
    }
    recalculatePageBadgeCounts();
  }

  function updateStatsAfterDelete(category) {
    updateBadgeCount('.badge-total-count', -1);
    updateBadgeCount('.badge-new-count', -1);
    if (category === 'studio') {
      updateBadgeCount('.badge-studio-count', -1);
    } else if (category === 'events') {
      updateBadgeCount('.badge-events-count', -1);
    }
    recalculatePageBadgeCounts();
  }

  function updateBadgeCount(selector, delta) {
    document.querySelectorAll(selector).forEach(el => {
      const current = parseInt(el.textContent.replace(/\D/g, ''), 10) || 0;
      const next = Math.max(0, current + delta);
      if (el.textContent.includes('(')) {
        el.textContent = el.textContent.replace(/\d+/, next);
      } else {
        el.textContent = next;
      }
    });
  }

  // ── Notification Helpers ─────────────────────────────────────
  function showAlert(alertEl, type, msg) {
    if (!alertEl) return;
    alertEl.className = `admin-alert admin-alert-${type}`;
    alertEl.textContent = msg;
    alertEl.style.display = 'block';
  }

  function showToast(msg, type = 'info') {
    const toast = document.getElementById('adminToast');
    if (!toast) return;
    toast.textContent = msg;
    toast.className = `admin-toast is-visible toast-${type}`;
    setTimeout(() => {
      toast.classList.remove('is-visible');
    }, 3500);
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // ── About Page Content Editor ─────────────────────────────────
  const aboutForm = document.getElementById('aboutForm');
  const aboutAlert = document.getElementById('aboutAlert');
  const aboutSubmitBtn = document.getElementById('aboutSubmitBtn');
  const aboutHeaderSaveBtn = document.getElementById('aboutHeaderSaveBtn');
  const aboutResetBtn = document.getElementById('aboutResetBtn');
  const aboutSaveStatus = document.getElementById('aboutSaveStatus');
  const aboutStatusDot = document.querySelector('.save-status-dot');

  function collectAboutFormData() {
    const f = document.getElementById('aboutForm');
    if (!f) return null;

    const points = [];
    for (let i = 0; i < 5; i++) {
      const tEl = f.querySelector(`[name="mission_point_title_${i}"]`);
      const dEl = f.querySelector(`[name="mission_point_desc_${i}"]`);
      if (tEl || dEl) {
        points.push({
          title: tEl ? tEl.value.trim() : '',
          desc:  dEl ? dEl.value.trim() : ''
        });
      }
    }

    const bioRaw = f.querySelector('[name="founder_bio"]')?.value || '';
    const bioParagraphs = bioRaw
      .split(/\r\n\r\n|\n\n|\r\r/)
      .map(p => p.trim())
      .filter(Boolean);

    return {
      hero: {
        tagline:      f.querySelector('[name="hero_tagline"]')?.value.trim() || '',
        title_main:   f.querySelector('[name="hero_title_main"]')?.value.trim() || 'About',
        title_italic: f.querySelector('[name="hero_title_italic"]')?.value.trim() || 'ZYAGO',
        title_sub:    f.querySelector('[name="hero_title_sub"]')?.value.trim() || 'STUDIO'
      },
      founder: {
        name:           f.querySelector('[name="founder_name"]')?.value.trim() || '',
        role:           f.querySelector('[name="founder_role"]')?.value.trim() || '',
        subhead:        f.querySelector('[name="founder_subhead"]')?.value.trim() || '',
        bio_paragraphs: bioParagraphs,
        quote:          f.querySelector('[name="founder_quote"]')?.value.trim() || ''
      },
      story: {
        title:            f.querySelector('[name="story_title"]')?.value.trim() || 'OUR STORY',
        block1_heading:   f.querySelector('[name="story_b1_heading"]')?.value.trim() || '',
        block1_text:      f.querySelector('[name="story_b1_text"]')?.value.trim() || '',
        block2_heading:   f.querySelector('[name="story_b2_heading"]')?.value.trim() || '',
        block2_text:      f.querySelector('[name="story_b2_text"]')?.value.trim() || '',
        block2_highlight: f.querySelector('[name="story_b2_highlight"]')?.value.trim() || ''
      },
      vision: {
        title:     f.querySelector('[name="vision_title"]')?.value.trim() || 'OUR VISION',
        subhead:   f.querySelector('[name="vision_subhead"]')?.value.trim() || '',
        text:      f.querySelector('[name="vision_text"]')?.value.trim() || '',
        highlight: f.querySelector('[name="vision_highlight"]')?.value.trim() || ''
      },
      mission: {
        title:     f.querySelector('[name="mission_title"]')?.value.trim() || 'OUR MISSION',
        subhead:   f.querySelector('[name="mission_subhead"]')?.value.trim() || '',
        intro:     f.querySelector('[name="mission_intro"]')?.value.trim() || '',
        points:    points,
        outro:     f.querySelector('[name="mission_outro"]')?.value.trim() || '',
        highlight: f.querySelector('[name="mission_highlight"]')?.value.trim() || ''
      },
      inspiration: {
        eyebrow: f.querySelector('[name="inspiration_eyebrow"]')?.value.trim() || '',
        title:   f.querySelector('[name="inspiration_title"]')?.value.trim() || 'INSPIRATION',
        text:    f.querySelector('[name="inspiration_text"]')?.value.trim() || ''
      }
    };
  }

  function populateAboutForm(d) {
    if (!d || !aboutForm) return;
    const setVal = (name, val) => {
      const el = aboutForm.querySelector(`[name="${name}"]`);
      if (el) el.value = val !== undefined && val !== null ? val : '';
    };

    if (d.hero) {
      setVal('hero_tagline', d.hero.tagline || '');
      setVal('hero_title_main', d.hero.title_main || '');
      setVal('hero_title_italic', d.hero.title_italic || '');
      setVal('hero_title_sub', d.hero.title_sub || '');
    }
    if (d.founder) {
      setVal('founder_name', d.founder.name || '');
      setVal('founder_role', d.founder.role || '');
      setVal('founder_subhead', d.founder.subhead || '');
      setVal('founder_bio', (d.founder.bio_paragraphs || []).join('\n\n'));
      setVal('founder_quote', d.founder.quote || '');
    }
    if (d.story) {
      setVal('story_title', d.story.title || '');
      setVal('story_b1_heading', d.story.block1_heading || '');
      setVal('story_b1_text', d.story.block1_text || '');
      setVal('story_b2_heading', d.story.block2_heading || '');
      setVal('story_b2_text', d.story.block2_text || '');
      setVal('story_b2_highlight', d.story.block2_highlight || '');
    }
    if (d.vision) {
      setVal('vision_title', d.vision.title || '');
      setVal('vision_subhead', d.vision.subhead || '');
      setVal('vision_text', d.vision.text || '');
      setVal('vision_highlight', d.vision.highlight || '');
    }
    if (d.mission) {
      setVal('mission_title', d.mission.title || '');
      setVal('mission_subhead', d.mission.subhead || '');
      setVal('mission_intro', d.mission.intro || '');
      setVal('mission_outro', d.mission.outro || '');
      setVal('mission_highlight', d.mission.highlight || '');
      const pts = d.mission.points || [];
      for (let i = 0; i < 5; i++) {
        setVal(`mission_point_title_${i}`, pts[i]?.title || '');
        setVal(`mission_point_desc_${i}`, pts[i]?.desc || '');
      }
    }
    if (d.inspiration) {
      setVal('inspiration_eyebrow', d.inspiration.eyebrow || '');
      setVal('inspiration_title', d.inspiration.title || '');
      setVal('inspiration_text', d.inspiration.text || '');
    }
  }

  async function handleAboutSubmit(e) {
    if (e) e.preventDefault();
    const payload = collectAboutFormData();
    if (!payload) return;

    if (aboutSubmitBtn) aboutSubmitBtn.disabled = true;
    if (aboutHeaderSaveBtn) aboutHeaderSaveBtn.disabled = true;
    if (aboutStatusDot) aboutStatusDot.classList.add('saving');
    if (aboutSaveStatus) aboutSaveStatus.textContent = 'Saving changes to disk...';
    if (aboutAlert) aboutAlert.style.display = 'none';

    try {
      const res = await fetch('api/about.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          csrf_token: csrfToken,
          data: payload
        })
      });

      const json = await res.json();
      if (res.ok && json.ok) {
        showToast('About page published successfully!', 'success');
        if (aboutSaveStatus) {
          const now = new Date();
          aboutSaveStatus.textContent = `Published · Today at ${now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
        }
      } else {
        const msg = json.error || 'Failed to save About page.';
        showToast(msg, 'error');
        showAlert(aboutAlert, 'error', msg);
        if (aboutSaveStatus) aboutSaveStatus.textContent = 'Save failed.';
      }
    } catch (err) {
      console.error('Error saving about page:', err);
      showToast('Network error while saving.', 'error');
      showAlert(aboutAlert, 'error', 'Network error. Please verify server connection.');
      if (aboutSaveStatus) aboutSaveStatus.textContent = 'Connection error.';
    } finally {
      if (aboutSubmitBtn) aboutSubmitBtn.disabled = false;
      if (aboutHeaderSaveBtn) aboutHeaderSaveBtn.disabled = false;
      if (aboutStatusDot) aboutStatusDot.classList.remove('saving');
    }
  }

  if (aboutForm) {
    aboutForm.addEventListener('submit', handleAboutSubmit);
  }

  if (aboutResetBtn) {
    aboutResetBtn.addEventListener('click', async () => {
      if (!confirm('Discard unsaved edits and reload current saved About page?')) return;
      try {
        const res = await fetch('api/about.php');
        const json = await res.json();
        if (json.ok && json.data) {
          populateAboutForm(json.data);
          showToast('Reloaded saved About content.', 'info');
          if (aboutAlert) aboutAlert.style.display = 'none';
        }
      } catch (err) {
        showToast('Failed to reload content.', 'error');
      }
    });
  }

  // Quick navigation pills inside About editor
  const aboutPills = document.querySelectorAll('.about-pill');
  aboutPills.forEach(pill => {
    pill.addEventListener('click', (e) => {
      e.preventDefault();
      aboutPills.forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      const targetId = pill.getAttribute('href');
      const targetEl = document.querySelector(targetId);
      if (targetEl) {
        targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

})();


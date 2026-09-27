/**
 * ZYAGO STUDIO — CORE INTERACTION ENGINE
 * Restrained, Cinematic, & Resilient Vanilla JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
  initPreloader();
  initStickyHeader();
  initHeroSlideshow();
  initMenuOverlay();
  initCustomCursor();
  initScrollReveals();
  initExperienceTabs();
  initTestimonialRotation();
  initVideoModals();
  initGalleryLightbox();
  initWhatsAppEnquiryForm();
  initMagneticButtons();
  initConnectWidget();
  initCardEnquiry();
});

/* --------------------------------------------------------------------------
   1. PRELOADER ENGINE
   -------------------------------------------------------------------------- */
function initPreloader() {
  const preloader = document.querySelector('.site-preloader');
  if (!preloader) return;

  const hidePreloader = () => {
    preloader.classList.add('loaded');
    setTimeout(() => {
      preloader.remove();
    }, 700);
  };

  // Dismiss quickly on load, maximum 900ms
  if (document.readyState === 'complete') {
    setTimeout(hidePreloader, 600);
  } else {
    window.addEventListener('load', () => {
      setTimeout(hidePreloader, 600);
    });
    // Fallback safety timeout
    setTimeout(hidePreloader, 1200);
  }
}

/* --------------------------------------------------------------------------
   2. STICKY HEADER & ACTIVE UNDERLINE
   -------------------------------------------------------------------------- */
function initStickyHeader() {
  const header = document.querySelector('.site-header');
  if (!header) return;

  const handleScroll = () => {
    if (window.scrollY > 40) {
      header.classList.add('is-scrolled');
      if (header.classList.contains('hero-transparent-nav')) {
        const logo = header.querySelector('#headerLogo');
        if (logo && logo.dataset.darkSrc) {
          logo.src = logo.dataset.darkSrc;
        }
      }
    } else {
      header.classList.remove('is-scrolled');
      if (header.classList.contains('hero-transparent-nav')) {
        const logo = header.querySelector('#headerLogo');
        if (logo && logo.dataset.lightSrc) {
          logo.src = logo.dataset.lightSrc;
        }
      }
    }
  };

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();
}

/* --------------------------------------------------------------------------
   3. RESPONSIVE MENU OVERLAY (Strictly Mobile/Tablet only)
   -------------------------------------------------------------------------- */
function initMenuOverlay() {
  const hamburger = document.getElementById('hamburgerBtn');
  const overlay = document.getElementById('menuOverlay');
  const closeBtn = document.getElementById('menuCloseBtn');

  if (!hamburger || !overlay) return;

  const openMenu = () => {
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  };

  const closeMenu = () => {
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  };

  hamburger.addEventListener('click', openMenu);
  if (closeBtn) closeBtn.addEventListener('click', closeMenu);

  overlay.querySelectorAll('.minimal-menu-link').forEach(link => {
    link.addEventListener('click', closeMenu);
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024 && overlay.classList.contains('is-open')) {
      closeMenu();
    }
  });
}

/* --------------------------------------------------------------------------
   4. CUSTOM DESKTOP CURSOR (VIEW / DRAG STATES)
   -------------------------------------------------------------------------- */
function initCustomCursor() {
  if (window.innerWidth < 1024 || window.matchMedia('(pointer: coarse)').matches) return;

  const dot = document.createElement('div');
  dot.className = 'custom-cursor-dot';
  const circle = document.createElement('div');
  circle.className = 'custom-cursor-circle';

  document.body.appendChild(dot);
  document.body.appendChild(circle);

  let mouseX = -100, mouseY = -100;
  let circleX = -100, circleY = -100;
  let hasMoved = false;

  const handleMouseMove = (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
    if (!hasMoved) {
      hasMoved = true;
      circleX = mouseX;
      circleY = mouseY;
      document.body.classList.add('cursor-visible');
    }
    dot.style.left = `${mouseX}px`;
    dot.style.top = `${mouseY}px`;
  };

  window.addEventListener('mousemove', handleMouseMove, { passive: true });
  document.addEventListener('mouseleave', () => {
    document.body.classList.remove('cursor-visible');
    hasMoved = false;
  });
  document.addEventListener('mouseenter', () => {
    if (hasMoved) document.body.classList.add('cursor-visible');
  });

  const renderCursor = () => {
    if (hasMoved) {
      circleX += (mouseX - circleX) * 0.18;
      circleY += (mouseY - circleY) * 0.18;
      circle.style.left = `${circleX}px`;
      circle.style.top = `${circleY}px`;
    }
    requestAnimationFrame(renderCursor);
  };
  requestAnimationFrame(renderCursor);

  // Gallery items hover trigger "VIEW"
  document.querySelectorAll('.gallery-item, .editorial-card, .media-block-wrap').forEach(el => {
    el.addEventListener('mouseenter', () => {
      circle.classList.add('cursor-hover');
      circle.innerText = 'View';
    });
    el.addEventListener('mouseleave', () => {
      circle.classList.remove('cursor-hover');
      circle.innerText = '';
    });
  });
}

/* --------------------------------------------------------------------------
   5. LUXURY SCROLL REVEALS & IMAGE REVEAL ANIMATION ENGINE
   -------------------------------------------------------------------------- */
function initScrollReveals() {
  const revealSelectors = [
    '.reveal-on-scroll',
    '.curtain-reveal-wrap',
    '.editorial-card',
    '.experience-process-card',
    '.experience-card-item',
    '.about-collage-item',
    '.media-block-wrap',
    '.gallery-item',
    '.footer-insta-item',
    '.footer-insta-strip',
    '.img-reveal-shutter'
  ];

  const elements = document.querySelectorAll(revealSelectors.join(', '));
  if (!elements.length) return;

  // Stagger calculation helper for siblings in grids
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const el = entry.target;
        
        // Auto-calculate stagger delay for grid items
        const parent = el.parentElement;
        if (parent && (parent.classList.contains('gallery-container') ||
                       parent.classList.contains('footer-insta-strip') ||
                       parent.classList.contains('editorial-grid-3') ||
                       parent.classList.contains('about-collage-grid') ||
                       parent.classList.contains('experience-card-grid') ||
                       parent.classList.contains('experience-cards-track'))) {
          const siblings = Array.from(parent.children);
          const idx = siblings.indexOf(el);
          if (idx >= 0 && !el.style.transitionDelay) {
            el.style.transitionDelay = `${(idx % 6) * 0.08}s`;
          }
        }

        el.classList.add('is-revealed', 'revealed');
        observer.unobserve(el);
      }
    });
  }, {
    threshold: 0.1,
    rootMargin: '0px 0px -40px 0px'
  });

  elements.forEach(el => {
    // Immediate reveal for elements already in initial viewport
    const rect = el.getBoundingClientRect();
    if (rect.top < window.innerHeight && rect.bottom > 0) {
      el.classList.add('is-revealed', 'revealed');
    }
    observer.observe(el);
  });
}

/* --------------------------------------------------------------------------
   6. THE BESPOKE EXPERIENCE (Interactive Cards & Mobile Swipe Sync)
   -------------------------------------------------------------------------- */
function initExperienceTabs() {
  const track = document.getElementById('experienceCardsTrack');
  const pills = document.querySelectorAll('.exp-pill-btn');
  const cards = document.querySelectorAll('.experience-card-item');
  if (!track || !cards.length) return;

  let isProgrammaticScroll = false;
  let scrollTimeout = null;

  // Function to set active stage
  const setActiveStage = (index) => {
    pills.forEach((p, i) => {
      p.classList.toggle('active', i === index);
      p.setAttribute('aria-selected', i === index ? 'true' : 'false');
    });

    cards.forEach((c, i) => {
      c.classList.toggle('active', i === index);
    });
  };

  // Pill Button Clicks
  pills.forEach(pill => {
    pill.addEventListener('click', (e) => {
      e.preventDefault();
      const targetIdx = parseInt(pill.dataset.index, 10);
      if (isNaN(targetIdx)) return;

      setActiveStage(targetIdx);

      const targetCard = track.querySelector(`.experience-card-item[data-index="${targetIdx}"]`);
      if (targetCard) {
        isProgrammaticScroll = true;
        
        const cardCenter = targetCard.offsetLeft + targetCard.offsetWidth / 2;
        const targetScrollLeft = cardCenter - track.offsetWidth / 2;

        track.scrollTo({
          left: Math.max(0, targetScrollLeft),
          behavior: 'smooth'
        });

        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
          isProgrammaticScroll = false;
          // Re-verify correct active stage after animation settles
          setActiveStage(targetIdx);
        }, 650);
      }
    });
  });

  // Tap on card to center and activate (Mobile)
  cards.forEach((card, idx) => {
    card.addEventListener('click', () => {
      if (window.innerWidth <= 960) {
        const pill = document.querySelector(`.exp-pill-btn[data-index="${idx}"]`);
        if (pill) pill.click();
      }
    });
  });

  // Mobile Real-Time Swipe Tracker (Only when user manually swipes)
  track.addEventListener('scroll', () => {
    if (isProgrammaticScroll) return;

    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(() => {
      if (isProgrammaticScroll) return;

      const trackCenter = track.scrollLeft + track.offsetWidth / 2;
      let closestIdx = 0;
      let minDiff = Infinity;

      cards.forEach((card, i) => {
        const cardCenter = card.offsetLeft + card.offsetWidth / 2;
        const diff = Math.abs(trackCenter - cardCenter);
        if (diff < minDiff) {
          minDiff = diff;
          closestIdx = i;
        }
      });

      setActiveStage(closestIdx);
    }, 50);
  }, { passive: true });

  // Desktop Hover expansion & focus
  cards.forEach(card => {
    card.addEventListener('mouseenter', () => {
      if (window.innerWidth > 960) {
        cards.forEach(c => c.classList.remove('is-expanded'));
        card.classList.add('is-expanded');
      }
    });
  });

  if (track) {
    track.addEventListener('mouseleave', () => {
      if (window.innerWidth > 960) {
        cards.forEach(c => c.classList.remove('is-expanded'));
      }
    });
  }
}

/* --------------------------------------------------------------------------
   7. TESTIMONIAL ROTATION (3 Distinct Quotes, Dots, Auto-Advance)
   -------------------------------------------------------------------------- */
function initTestimonialRotation() {
  const slides = document.querySelectorAll('.testimonial-slide');
  const dots = document.querySelectorAll('.testimonial-dot');
  if (!slides.length) return;

  let currentIndex = 0;
  let timer = null;

  const showSlide = (index) => {
    slides.forEach((s, i) => {
      s.classList.toggle('active', i === index);
    });
    dots.forEach((d, i) => {
      d.classList.toggle('active', i === index);
    });
    currentIndex = index;
  };

  const nextSlide = () => {
    const next = (currentIndex + 1) % slides.length;
    showSlide(next);
  };

  const startAuto = () => {
    stopAuto();
    timer = setInterval(nextSlide, 7000);
  };

  const stopAuto = () => {
    if (timer) clearInterval(timer);
  };

  dots.forEach(dot => {
    dot.addEventListener('click', () => {
      const idx = parseInt(dot.dataset.slide, 10);
      showSlide(idx);
      startAuto();
    });
  });

  const container = document.querySelector('.testimonial-carousel-container');
  if (container) {
    container.addEventListener('mouseenter', stopAuto);
    container.addEventListener('mouseleave', startAuto);
  }

  showSlide(0);
  startAuto();
}

/* --------------------------------------------------------------------------
   8. VIDEO PLAY BUTTONS & FULLSCREEN VIDEO MODAL
   -------------------------------------------------------------------------- */
function initVideoModals() {
  const videoBlocks = document.querySelectorAll('.media-block-wrap[data-video-src]');
  const modal = document.getElementById('globalVideoModal');
  const modalPlayer = document.getElementById('globalVideoPlayer');
  const closeBtn = document.getElementById('globalVideoClose');

  if (!videoBlocks.length || !modal || !modalPlayer) return;

  const openVideo = (src) => {
    modalPlayer.src = src;
    modal.classList.add('is-open');
    modalPlayer.play().catch(() => {});
    document.body.style.overflow = 'hidden';
  };

  const closeVideo = () => {
    modal.classList.remove('is-open');
    modalPlayer.pause();
    modalPlayer.src = '';
    document.body.style.overflow = '';
  };

  videoBlocks.forEach(block => {
    block.addEventListener('click', () => {
      const src = block.dataset.videoSrc;
      if (src) openVideo(src);
    });
  });

  if (closeBtn) closeBtn.addEventListener('click', closeVideo);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeVideo();
  });
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('is-open')) closeVideo();
  });
}

/* --------------------------------------------------------------------------
   9. GALLERY LIGHTBOX & PRO CATEGORY FILTER SYSTEM
   -------------------------------------------------------------------------- */
function initGalleryLightbox() {
  const filterBtns = document.querySelectorAll('.gallery-filter-btn');
  const sheetItems = document.querySelectorAll('.category-sheet-item');
  const galleryItems = document.querySelectorAll('.gallery-item');
  const resultsText = document.getElementById('galleryResultsText');
  const resetBtn = document.getElementById('filterResetBtn');
  const sheetModal = document.getElementById('categorySheetModal');
  const openSheetBtn = document.getElementById('openCategorySheet');
  const closeSheetBtn = document.getElementById('closeCategorySheet');
  const sheetBackdrop = document.getElementById('categorySheetBackdrop');

  const lightbox = document.getElementById('globalLightbox');
  const lbImg = document.getElementById('lightboxImg');
  const lbTitle = document.getElementById('lightboxTitle');
  const lbCat = document.getElementById('lightboxCat');
  const lbClose = document.getElementById('lightboxClose');
  const lbPrev = document.getElementById('lightboxPrev');
  const lbNext = document.getElementById('lightboxNext');

  // Category Names Map
  const categoryNames = {
    all: 'Masterworks',
    elopements: 'Elopements & Weddings',
    studio: 'Studio Portraits',
    events: 'Life Events'
  };

  // 9.1 Dynamic Count Calculation
  if (galleryItems.length) {
    const counts = { all: galleryItems.length, elopements: 0, studio: 0, events: 0 };
    galleryItems.forEach(item => {
      const cat = item.dataset.category;
      if (cat && counts[cat] !== undefined) counts[cat]++;
    });

    // Update Pill badges
    filterBtns.forEach(btn => {
      const cat = btn.dataset.filter;
      const countEl = btn.querySelector('.filter-count');
      if (countEl && counts[cat] !== undefined) {
        countEl.textContent = counts[cat];
      }
    });

    // Update Sheet badges
    sheetItems.forEach(item => {
      const cat = item.dataset.filter;
      const countEl = item.querySelector('.sheet-item-count');
      if (countEl && counts[cat] !== undefined) {
        countEl.textContent = `${counts[cat]} works`;
      }
    });
  }

  // 9.2 Core Filter Execution
  const applyFilter = (filter, originElement = null) => {
    // Sync Buttons Active State
    filterBtns.forEach(b => {
      const match = b.dataset.filter === filter;
      b.classList.toggle('active', match);
      b.setAttribute('aria-selected', match ? 'true' : 'false');
      if (match && originElement) {
        const filterBar = document.getElementById('galleryFilterBar');
        if (filterBar && filterBar.scrollWidth > filterBar.clientWidth) {
          const scrollLeft = b.offsetLeft - (filterBar.clientWidth / 2) + (b.clientWidth / 2);
          filterBar.scrollTo({ left: Math.max(0, scrollLeft), behavior: 'smooth' });
        }
      }
    });

    // Sync Sheet Items Active State
    sheetItems.forEach(item => {
      const match = item.dataset.filter === filter;
      item.classList.toggle('active', match);
    });

    // Filter Items with Smooth Visual Transition
    let matchCount = 0;
    galleryItems.forEach(item => {
      const matches = (filter === 'all' || item.dataset.category === filter);
      if (matches) {
        matchCount++;
        item.style.display = 'block';
        requestAnimationFrame(() => {
          setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0) scale(1)';
          }, 20);
        });
      } else {
        item.style.opacity = '0';
        item.style.transform = 'translateY(12px) scale(0.98)';
        setTimeout(() => {
          item.style.display = 'none';
        }, 220);
      }
    });

    // Update Results Bar
    if (resultsText) {
      if (filter === 'all') {
        resultsText.innerHTML = `Showing all <strong>${matchCount}</strong> curated masterworks`;
        if (resetBtn) resetBtn.style.display = 'none';
      } else {
        const catLabel = categoryNames[filter] || filter;
        resultsText.innerHTML = `Showing <strong>${matchCount}</strong> works in <em>${catLabel}</em>`;
        if (resetBtn) resetBtn.style.display = 'inline-flex';
      }
    }
  };

  // 9.3 Filter Buttons Click Handler
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const filter = btn.dataset.filter;
      applyFilter(filter, btn);
    });
  });

  // 9.4 Mobile Category Sheet Handlers
  const openSheet = () => {
    if (!sheetModal) return;
    sheetModal.classList.add('is-open');
    sheetModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  };

  const closeSheet = () => {
    if (!sheetModal) return;
    sheetModal.classList.remove('is-open');
    sheetModal.setAttribute('aria-hidden', 'true');
    if (!lightbox || !lightbox.classList.contains('is-open')) {
      document.body.style.overflow = '';
    }
  };

  if (openSheetBtn) openSheetBtn.addEventListener('click', openSheet);
  if (closeSheetBtn) closeSheetBtn.addEventListener('click', closeSheet);
  if (sheetBackdrop) sheetBackdrop.addEventListener('click', closeSheet);

  sheetItems.forEach(item => {
    item.addEventListener('click', () => {
      const filter = item.dataset.filter;
      const targetBtn = document.querySelector(`.gallery-filter-btn[data-filter="${filter}"]`);
      applyFilter(filter, targetBtn);
      closeSheet();
    });
  });

  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      const allBtn = document.querySelector('.gallery-filter-btn[data-filter="all"]');
      applyFilter('all', allBtn);
    });
  }

  // 9.5 Lightbox Logic
  if (!lightbox || !galleryItems.length) return;

  let visibleItems = [];
  let currentIdx = 0;

  const updateVisibleList = () => {
    visibleItems = Array.from(galleryItems).filter(item => item.style.display !== 'none');
  };

  const showLightboxImage = (idx) => {
    if (idx < 0) idx = visibleItems.length - 1;
    if (idx >= visibleItems.length) idx = 0;
    currentIdx = idx;

    const target = visibleItems[currentIdx];
    if (!target) return;
    const img = target.querySelector('img');
    const title = target.querySelector('.gallery-item-title');
    const cat = target.querySelector('.gallery-item-category');

    if (lbImg && img) lbImg.src = img.src;
    if (lbTitle && title) lbTitle.textContent = title.textContent;
    if (lbCat && cat) lbCat.textContent = cat.textContent;
  };

  galleryItems.forEach(item => {
    item.addEventListener('click', () => {
      updateVisibleList();
      const idx = visibleItems.indexOf(item);
      if (idx !== -1) {
        showLightboxImage(idx);
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
      }
    });
  });

  const closeLightbox = () => {
    lightbox.classList.remove('is-open');
    document.body.style.overflow = '';
  };

  if (lbClose) lbClose.addEventListener('click', closeLightbox);
  if (lbPrev) lbPrev.addEventListener('click', () => showLightboxImage(currentIdx - 1));
  if (lbNext) lbNext.addEventListener('click', () => showLightboxImage(currentIdx + 1));

  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) closeLightbox();
  });

  window.addEventListener('keydown', (e) => {
    if (sheetModal && sheetModal.classList.contains('is-open') && e.key === 'Escape') {
      closeSheet();
      return;
    }
    if (!lightbox.classList.contains('is-open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') showLightboxImage(currentIdx - 1);
    if (e.key === 'ArrowRight') showLightboxImage(currentIdx + 1);
  });
}

/* --------------------------------------------------------------------------
   10. DIRECT WHATSAPP ENQUIRY FORM
   -------------------------------------------------------------------------- */
function initWhatsAppEnquiryForm() {
  const form = document.getElementById('whatsappEnquiryForm') || document.querySelector('.contact-inquiry-form');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const nameInput = document.getElementById('inquiryName');
    const phoneInput = document.getElementById('inquiryPhone');
    const serviceInput = document.getElementById('inquiryCommissionType');
    const dateInput = document.getElementById('inquiryDate');
    const messageInput = document.getElementById('inquiryMessage');
    const submitBtn = document.getElementById('btnWhatsappSubmit') || form.querySelector('button[type="submit"]');
    const statusMsg = document.getElementById('formStatusMsg');

    const name = nameInput ? nameInput.value.trim() : '';
    const phone = phoneInput ? phoneInput.value.trim() : '';
    const service = serviceInput ? serviceInput.value.trim() : '';
    const date = dateInput ? dateInput.value.trim() : '';
    const message = messageInput ? messageInput.value.trim() : '';

    if (!name || !phone || !service) {
      if (!name && nameInput) nameInput.style.borderColor = 'var(--color-burgundy)';
      if (!phone && phoneInput) phoneInput.style.borderColor = 'var(--color-burgundy)';
      if (!service && serviceInput) serviceInput.style.borderColor = 'var(--color-burgundy)';
      return;
    }

    // Reset border colors
    if (nameInput) nameInput.style.borderColor = '';
    if (phoneInput) phoneInput.style.borderColor = '';
    if (serviceInput) serviceInput.style.borderColor = '';

    // Build structured WhatsApp message
    let waText = `📸 *NEW ENQUIRY - ZYAGO STUDIO*\n`;
    waText += `━━━━━━━━━━━━━━━━━━━━\n`;
    waText += `👤 *Name:* ${name}\n`;
    waText += `📱 *Phone:* ${phone}\n`;
    waText += `✨ *Service:* ${service}\n`;
    if (date) {
      waText += `📅 *Date / Month:* ${date}\n`;
    }
    if (message) {
      waText += `📍 *Location / Details:* ${message}\n`;
    }
    waText += `━━━━━━━━━━━━━━━━━━━━\n`;
    waText += `Sent via zyagostudio.com`;

    const encodedText = encodeURIComponent(waText);
    const whatsappNumber = '916381683178';
    const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodedText}`;

    // Update button & feedback status
    if (submitBtn) {
      const originalHtml = submitBtn.innerHTML;
      submitBtn.innerHTML = `<span>✓ Opening WhatsApp...</span>`;
      submitBtn.style.background = '#20BA5A';
      setTimeout(() => {
        submitBtn.innerHTML = originalHtml;
        submitBtn.style.background = '';
      }, 4000);
    }

    if (statusMsg) {
      statusMsg.style.display = 'flex';
      setTimeout(() => {
        statusMsg.style.display = 'none';
      }, 5000);
    }

    // Open WhatsApp in a new tab
    window.open(whatsappUrl, '_blank');
  });
}

/* --------------------------------------------------------------------------
   11. MAGNETIC BUTTONS EFFECT
   -------------------------------------------------------------------------- */
function initMagneticButtons() {
  if (window.innerWidth < 1024 || window.matchMedia('(pointer: coarse)').matches) return;

  const magneticBtns = document.querySelectorAll('.btn-contact, .btn-primary');
  magneticBtns.forEach(btn => {
    btn.addEventListener('mousemove', (e) => {
      const rect = btn.getBoundingClientRect();
      const x = e.clientX - (rect.left + rect.width / 2);
      const y = e.clientY - (rect.top + rect.height / 2);
      btn.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px)`;
    });

    btn.addEventListener('mouseleave', () => {
      btn.style.transform = 'translate(0px, 0px)';
    });
  });
}

/* --------------------------------------------------------------------------
   12. CINEMATIC HERO SLIDESHOW
   -------------------------------------------------------------------------- */
function initHeroSlideshow() {
  const slides = document.querySelectorAll('.hero-bg-slide');
  const dots = document.querySelectorAll('.hero-dot');
  if (!slides.length) return;

  let currentIdx = 0;
  let timer = null;

  const showSlide = (idx) => {
    if (idx < 0) idx = slides.length - 1;
    if (idx >= slides.length) idx = 0;
    currentIdx = idx;

    slides.forEach((slide, i) => {
      slide.classList.toggle('active', i === currentIdx);
    });

    dots.forEach((dot, i) => {
      dot.classList.toggle('active', i === currentIdx);
      dot.setAttribute('aria-selected', i === currentIdx ? 'true' : 'false');
    });
  };

  const prevSlide = () => {
    showSlide((currentIdx - 1 + slides.length) % slides.length);
  };

  const nextSlide = () => {
    showSlide((currentIdx + 1) % slides.length);
  };

  const startAutoPlay = () => {
    stopAutoPlay();
    timer = setInterval(nextSlide, 4500);
  };

  const stopAutoPlay = () => {
    if (timer) clearInterval(timer);
  };

  dots.forEach(dot => {
    dot.addEventListener('click', () => {
      const idx = parseInt(dot.dataset.slide, 10);
      showSlide(idx);
      startAutoPlay();
    });
  });

  const prevBtns = document.querySelectorAll('#heroPrevBtn, #heroSidePrev');
  const nextBtns = document.querySelectorAll('#heroNextBtn, #heroSideNext');

  prevBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      prevSlide();
      startAutoPlay();
    });
  });

  nextBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      nextSlide();
      startAutoPlay();
    });
  });

  // Pause briefly if user is explicitly hovering over slide nav
  const slideNav = document.getElementById('heroSlideNav');
  if (slideNav) {
    slideNav.addEventListener('mouseenter', stopAutoPlay);
    slideNav.addEventListener('mouseleave', startAutoPlay);
  }

  showSlide(0);
  startAutoPlay();
}

/* --------------------------------------------------------------------------
   13. FLOATING LUXURY CONNECT WIDGET (CALL & WHATSAPP)
   -------------------------------------------------------------------------- */
function initConnectWidget() {
  const widget = document.getElementById('luxuryConnectWidget');
  const triggerBtn = document.getElementById('luxuryConnectBtn');
  const popover = document.getElementById('connectPopover');
  const cardCloseBtn = document.getElementById('connectCardCloseBtn');

  if (!widget || !triggerBtn || !popover) return;

  const toggleWidget = (e) => {
    e.stopPropagation();
    const isOpen = widget.classList.toggle('is-open');
    triggerBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    popover.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
  };

  const closeWidget = () => {
    if (widget.classList.contains('is-open')) {
      widget.classList.remove('is-open');
      triggerBtn.setAttribute('aria-expanded', 'false');
      popover.setAttribute('aria-hidden', 'true');
    }
  };

  triggerBtn.addEventListener('click', toggleWidget);

  if (cardCloseBtn) {
    cardCloseBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      closeWidget();
    });
  }

  // Close when clicking outside
  document.addEventListener('click', (e) => {
    if (!widget.contains(e.target)) {
      closeWidget();
    }
  });

  // Close on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeWidget();
    }
  });
}

/* --------------------------------------------------------------------------
   LUXURY ENQUIRY PROFILE CARD INTERACTION
   -------------------------------------------------------------------------- */
function initCardEnquiry() {
  const touchBtn = document.getElementById('enquiryGetInTouchBtn');
  const bookmarkBtn = document.getElementById('enquiryBookmarkBtn');
  const drawer = document.getElementById('enquiryModalDrawer');
  if (!drawer) return;

  const toggleDrawer = (e) => {
    if (e) e.preventDefault();
    drawer.classList.toggle('active');
    if (drawer.classList.contains('active')) {
      drawer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  };

  if (touchBtn) touchBtn.addEventListener('click', toggleDrawer);
  if (bookmarkBtn) bookmarkBtn.addEventListener('click', toggleDrawer);
}

function submitCardInquiry() {
  const form = document.getElementById('cardInquiryForm');
  const msg = document.getElementById('enquirySuccessMsg');
  if (form && msg) {
    form.style.display = 'none';
    msg.style.display = 'block';
  }
}



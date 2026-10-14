/**
 * ============================================================
 * Zyago Studio — About Page Client Hydration (about-sync.js)
 * Fetches dynamic content from /api/about.php and updates the DOM
 * while strictly preserving all existing typography, classes,
 * animations, and responsive layouts.
 * ============================================================
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', initAboutSync);

  async function initAboutSync() {
    try {
      // Relative path: from pages/about.html -> ../api/about.php
      const apiUrl = '../api/about.php';
      const res = await fetch(apiUrl, { cache: 'no-cache' });
      if (!res.ok) return;

      const result = await res.json();
      if (!result || !result.ok || !result.data) return;

      hydrateAboutDOM(result.data);
    } catch (err) {
      // Graceful fallback: maintain existing static HTML content
      console.warn('Zyago Studio: using static fallback for About page.', err);
    }
  }

  function hydrateAboutDOM(data) {
    if (!data) return;

    // ── 1. Hero ──────────────────────────────────────────────
    if (data.hero) {
      const heroTagline = document.querySelector('[data-about="hero-tagline"]') ||
                          document.querySelector('#aboutHero .uppercase-track');
      if (heroTagline && data.hero.tagline) {
        heroTagline.textContent = data.hero.tagline;
      }

      const titleMain = document.querySelector('[data-about="hero-title-main"]');
      if (titleMain && data.hero.title_main) {
        titleMain.textContent = data.hero.title_main;
      }

      const titleItalic = document.querySelector('[data-about="hero-title-italic"]');
      if (titleItalic && data.hero.title_italic) {
        titleItalic.textContent = data.hero.title_italic;
      }

      const titleSub = document.querySelector('[data-about="hero-title-sub"]');
      if (titleSub && data.hero.title_sub) {
        titleSub.textContent = data.hero.title_sub;
      }
    }

    // ── 2. Founder ────────────────────────────────────────────
    if (data.founder) {
      // Role captions
      document.querySelectorAll('[data-about="founder-role"]').forEach(el => {
        if (data.founder.role) el.textContent = data.founder.role;
      });

      const founderName = document.querySelector('[data-about="founder-name"]');
      if (founderName && data.founder.name) {
        founderName.textContent = data.founder.name;
      }

      const founderSubhead = document.querySelector('[data-about="founder-subhead"]');
      if (founderSubhead && data.founder.subhead) {
        founderSubhead.textContent = data.founder.subhead;
      }

      // Bio Paragraphs
      const bioContainer = document.querySelector('[data-about="founder-bio"]');
      if (bioContainer && Array.isArray(data.founder.bio_paragraphs) && data.founder.bio_paragraphs.length > 0) {
        bioContainer.innerHTML = '';
        data.founder.bio_paragraphs.forEach((pText, idx) => {
          const p = document.createElement('p');
          p.style.fontSize = idx === 0 ? '15.5px' : '15px';
          p.style.color = 'var(--text-dark-secondary)';
          p.style.lineHeight = '1.85';
          p.style.marginBottom = idx === data.founder.bio_paragraphs.length - 1 ? '24px' : '18px';
          p.textContent = pText;
          bioContainer.appendChild(p);
        });
      }

      const founderQuote = document.querySelector('[data-about="founder-quote"]');
      if (founderQuote && data.founder.quote) {
        founderQuote.textContent = `“${data.founder.quote.replace(/^["“]|["”]$/g, '').trim()}”`;
      }
    }

    // ── 3. Our Story ─────────────────────────────────────────
    if (data.story) {
      const storyTitle = document.querySelector('[data-about="story-title"]');
      if (storyTitle && data.story.title) {
        storyTitle.textContent = data.story.title;
      }

      const b1Heading = document.querySelector('[data-about="story-b1-heading"]');
      if (b1Heading && data.story.block1_heading) {
        b1Heading.textContent = data.story.block1_heading;
      }

      const b1Body = document.querySelector('[data-about="story-b1-body"]');
      if (b1Body && data.story.block1_text) {
        renderParagraphs(b1Body, data.story.block1_text, {
          fontSize: '14.5px',
          color: 'var(--text-dark-secondary)',
          lineHeight: '1.85',
          marginBottom: '14px'
        });
      }

      const b2Heading = document.querySelector('[data-about="story-b2-heading"]');
      if (b2Heading && data.story.block2_heading) {
        b2Heading.textContent = data.story.block2_heading;
      }

      const b2Body = document.querySelector('[data-about="story-b2-body"]');
      if (b2Body && data.story.block2_text) {
        renderParagraphs(b2Body, data.story.block2_text, {
          fontSize: '14.5px',
          color: 'var(--text-dark-secondary)',
          lineHeight: '1.85',
          marginBottom: '14px'
        });
      }

      const b2Highlight = document.querySelector('[data-about="story-b2-highlight"]');
      if (b2Highlight && data.story.block2_highlight) {
        b2Highlight.textContent = data.story.block2_highlight;
      }
    }

    // ── 4. Vision ────────────────────────────────────────────
    if (data.vision) {
      const visionTitle = document.querySelector('[data-about="vision-title"]');
      if (visionTitle && data.vision.title) {
        visionTitle.textContent = data.vision.title;
      }

      const visionSubhead = document.querySelector('[data-about="vision-subhead"]');
      if (visionSubhead && data.vision.subhead) {
        visionSubhead.textContent = data.vision.subhead;
      }

      const visionBody = document.querySelector('[data-about="vision-body"]');
      if (visionBody && data.vision.text) {
        renderParagraphs(visionBody, data.vision.text, {
          fontSize: '14.5px',
          color: 'var(--text-dark-secondary)',
          lineHeight: '1.85',
          marginBottom: '14px'
        });
      }

      const visionHighlight = document.querySelector('[data-about="vision-highlight"]');
      if (visionHighlight && data.vision.highlight) {
        visionHighlight.textContent = data.vision.highlight;
      }
    }

    // ── 5. Mission ───────────────────────────────────────────
    if (data.mission) {
      const missionTitle = document.querySelector('[data-about="mission-title"]');
      if (missionTitle && data.mission.title) {
        missionTitle.textContent = data.mission.title;
      }

      const missionSubhead = document.querySelector('[data-about="mission-subhead"]');
      if (missionSubhead && data.mission.subhead) {
        missionSubhead.textContent = data.mission.subhead;
      }

      const missionIntro = document.querySelector('[data-about="mission-intro"]');
      if (missionIntro && data.mission.intro) {
        missionIntro.textContent = data.mission.intro;
      }

      const pointsList = document.querySelector('[data-about="mission-points-list"]');
      if (pointsList && Array.isArray(data.mission.points) && data.mission.points.length > 0) {
        pointsList.innerHTML = '';
        data.mission.points.forEach(point => {
          if (!point || (!point.title && !point.desc)) return;
          const li = document.createElement('li');
          li.style.display = 'flex';
          li.style.alignItems = 'flex-start';
          li.style.gap = '10px';
          li.style.fontSize = '14px';
          li.style.lineHeight = '1.65';
          li.style.color = 'var(--text-dark-secondary)';

          const bullet = document.createElement('span');
          bullet.style.color = 'var(--color-gold)';
          bullet.style.fontSize = '18px';
          bullet.style.lineHeight = '1.2';
          bullet.style.flexShrink = '0';
          bullet.textContent = '•';

          const textSpan = document.createElement('span');
          if (point.title) {
            const strong = document.createElement('strong');
            strong.style.color = 'var(--text-dark)';
            strong.style.fontWeight = '600';
            strong.textContent = point.title.trim() + ' ';
            textSpan.appendChild(strong);
          }
          if (point.desc) {
            textSpan.appendChild(document.createTextNode(point.desc.trim()));
          }

          li.appendChild(bullet);
          li.appendChild(textSpan);
          pointsList.appendChild(li);
        });
      }

      const missionOutro = document.querySelector('[data-about="mission-outro"]');
      if (missionOutro && data.mission.outro) {
        missionOutro.textContent = data.mission.outro;
      }

      const missionHighlight = document.querySelector('[data-about="mission-highlight"]');
      if (missionHighlight && data.mission.highlight) {
        missionHighlight.textContent = data.mission.highlight;
      }
    }

    // ── 6. Inspiration ───────────────────────────────────────
    if (data.inspiration) {
      const inspEyebrow = document.querySelector('[data-about="inspiration-eyebrow"]');
      if (inspEyebrow && data.inspiration.eyebrow) {
        inspEyebrow.textContent = data.inspiration.eyebrow;
      }

      const inspTitle = document.querySelector('[data-about="inspiration-title"]');
      if (inspTitle && data.inspiration.title) {
        inspTitle.textContent = data.inspiration.title;
      }

      const inspText = document.querySelector('[data-about="inspiration-text"]');
      if (inspText && data.inspiration.text) {
        inspText.textContent = data.inspiration.text;
      }
    }
  }

  function renderParagraphs(container, text, styles = {}) {
    if (!container || !text) return;
    container.innerHTML = '';
    const paragraphs = text.split(/\r\n\r\n|\n\n|\r\r/).map(p => p.trim()).filter(Boolean);
    paragraphs.forEach((pText, i) => {
      const p = document.createElement('p');
      Object.assign(p.style, styles);
      if (i === paragraphs.length - 1) {
        p.style.marginBottom = '0';
      }
      p.textContent = pText;
      container.appendChild(p);
    });
  }

})();

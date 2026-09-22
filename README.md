# Zyago Studio — Luxury Editorial Photography & Cinematography

A bespoke, responsive web experience crafted for **Zyago Studio**, an editorial wedding, fine art studio portraiture, and milestone event photography house based out of London and available worldwide.

---

## ✨ Features & Architecture

- **Cinematic Responsive Design**: Editorial typography, ivory-to-stone palette, and fluid scaling across all devices (Mobile, Tablet, Desktop).
- **Interactive Multi-Page Structure**:
  - `index.html`: Flagship landing experience with dynamic header, video showcase, and interactive stage curation.
  - `pages/studio-portrait.html`: Fine art portraiture collections and sitters' vision.
  - `pages/life-events.html`: Luxury weddings, milestone celebrations, and documentary highlights.
  - `pages/galleries.html`: Filterable portfolio grids with lightbox & dynamic story modals.
  - `pages/about.html`: Philosophy, artist manifesto, and testimonials.
  - `pages/contact.html`: Roman arch concierge booking card with interactive calendar date pickers.
- **Optimized Media Delivery**: WebP compression, responsive video loops, and dynamic dark/light header logo switching.
- **Micro-Interactions**: Custom cursor, preloader reveal, smooth fullscreen mobile navigation overlay, and bespoke luxury concierge floating pill.

---

## 📁 Project Directory Structure

```
zyago-studio/
├── assets/
│   ├── images/          # Curated WebP photography & brand badges
│   ├── videos/          # Compressed high-definition cinematic reels
│   └── manifest.json    # Gallery collections metadata
├── css/
│   ├── style.css        # Luxury design system, typography & animations
│   └── responsive.css   # Breakpoint adaptations for all screens
├── js/
│   └── script.js        # Lightbox, mobile drawer, scroll reveals & filter logic
├── pages/
│   ├── about.html
│   ├── contact.html
│   ├── galleries.html
│   ├── life-events.html
│   └── studio-portrait.html
├── docs/
│   └── DESIGN_ANALYSIS.md
├── index.html
└── README.md
```

---

## 🚀 Getting Started

Simply open `index.html` in any modern web browser or serve with a lightweight local web server:

```bash
# Using Python
python -m http.server 8000

# Using Node.js / npx
npx serve .
```

---

## 📜 License & Copyright

© 2026 Zyago Studio. All Rights Reserved.

# DESIGN ANALYSIS & SPECIFICATION
## Zyago Studio
### Autonomous Pixel-Perfect Website Specification

---

## 1. PAGE STRUCTURE & ARCHITECTURE

The website consists of 8 main pages, 1 full-screen navigation overlay, and a persistent conversational chat widget.

### Pages:
1. **Home (`index.html`)**
   - Fixed Navigation Header
   - Hero Section: Editorial headline with split typography, large primary photo, floating circular stamp badge ("• HIRE US • BOOK YOUR ELOPEMENT •")
   - Intro Statement / Brand Manifesto Section: Large serif headline ("AUTHENTIC, TIMELESS & INTENTIONAL STORYTELLING"), signature script
   - Featured Portfolios / Service Categories Grid (Elopement, Couples, Portraits) with hover zooms and editorial captions
   - Oval "HD" Monogram Seal & Philosophy Block
   - Full-width Cinematic Parallax / Image Feature Banner
   - "The Experience" / Workflow Process (01 Initial Consultation, 02 The Planning, 03 The Day, 04 Gallery Delivery)
   - Client Love / Testimonial Carousel with editorial serif quotes and `←` `→` arrows
   - Featured Stories / Blog Teasers Grid
   - Final Call to Action (CTA): "LET'S CREATE SOMETHING TIMELESS TOGETHER"
   - Universal Footer with brand emblem box, navigation links, and 5-photo Instagram strip
   - Persistent Chat Widget ("Ivy here...")

2. **About (`pages/about.html`)**
   - Header with active state on "ABOUT"
   - Page Hero: "MEET HADI" / "THE STORY BEHIND THE LENS"
   - Dual-column Bio Layout: Portrait photograph + narrative text, artistic philosophy
   - Editorial Quote Banner ("Capturing fleeting moments in their purest, most unscripted form")
   - Behind the Scenes / Studio & Equipment Showcase
   - Core Values (Intentional, Unobtrusive, Timeless, Emotion-first)
   - Accolades, Publications & Recognition list
   - Universal Footer & Chat Widget

3. **Pricing & Packages (`pages/pricing.html`)**
   - Header with active state on "PRICING"
   - Page Header: "INVESTMENT & PACKAGES"
   - Three Editorial Tier Cards:
     - The Intimate / Elopement Collection
     - The Full Day Wedding Collection
     - The Editorial / Studio Portrait Session
   - Package inclusions list with custom bullet marks
   - A La Carte Add-ons (Second shooter, Fine Art Albums, Super 8 Video)
   - FAQ Accordion (Booking retainer, turnaround time, travel fees, delivery format)
   - Booking CTA Banner
   - Universal Footer & Chat Widget

4. **Elopement Photography in London (`pages/elopement.html`)**
   - Header (Portfolio dropdown active)
   - Hero: "LONDON ELOPEMENT PHOTOGRAPHER"
   - Editorial Narrative on intimate ceremonies in London
   - Curated Elopement Gallery Grid (masonry / alternating column layout)
   - Iconic London Elopement Locations Guide (Old Marylebone Town Hall, Chelsea, Somerset House)
   - Client Testimonial specific to elopements
   - Elopement Package Snapshot & Inquiry CTA
   - Universal Footer & Chat Widget

5. **Couples & Engagement (`pages/couples.html`)**
   - Header (Portfolio dropdown active)
   - Hero: "COUPLES & ENGAGEMENTS"
   - Gallery of intimate love stories and editorial street sessions
   - What to Expect / Styling & Direction Advice
   - Testimonials
   - Inquiry CTA
   - Universal Footer & Chat Widget

6. **Portrait & Fashion (`pages/portrait.html`)**
   - Header (Portfolio dropdown active)
   - Hero: "STUDIO PORTRAIT & EDITORIAL FASHION"
   - High-contrast studio lighting gallery showcasing fashion and individual portraits
   - Session Details: Styling consultation, lighting setup, retouching
   - Inquiry CTA
   - Universal Footer & Chat Widget

7. **Contact (`pages/contact.html`)**
   - Warm Ochre/Sand Header Banner: "LET'S TALK ABOUT YOUR DAY"
   - Dual-column Layout:
     - Left: Contact details, studio location (London, UK), direct email, Instagram link, availability notes
     - Right: Editorial Inquiry Form (Name, Email, Phone, Event Date, Event Type dropdown, Estimated Budget, How did you hear about us, Message, Submit Button)
   - Direct Call-to-Action note & FAQ summary
   - Universal Footer & Chat Widget

8. **Blog (`pages/blog.html`)**
   - Header with active state on "BLOG"
   - Page Hero: "STORIES, GUIDES & INSPIRATION"
   - Featured Article Hero Card
   - Blog Article Grid (3 columns):
     - London City Elopement Planning Guide
     - What to Wear for Your Editorial Engagement Session
     - Top Intimate Wedding Venues in Central London
   - Pagination / Load More button
   - Universal Footer & Chat Widget

9. **Navigation Menu Overlay (`00_navigation_menu_overlay_desktop.png` / `mobile.png`)**
   - Fullscreen modal triggered by the hamburger icon
   - Warm earthy ochre/taupe background (`#998465`)
   - Close button (`✕`) top right
   - Giant serif navigation links:
     - HOME
     - ABOUT
     - PORTFOLIO (expandable / sub-items: London Elopement, Couples & Engagements, Studio Portraits)
     - PRICING & PACKAGES
     - STORIES (BLOG)
     - GET IN TOUCH
   - Bottom metadata / Social links: Instagram, Pinterest, Email, Location ("London, UK & Worldwide")

10. **Floating Chat Widget ("Ivy")**
    - Bottom-right fixed floating bubble
    - Circular avatar photo + green online status indicator
    - Speech bubble banner: "Ivy here, let me know if you need any help!"
    - Quick actions: "Call" and "Chat" buttons + "⚡ by ElevenAgents" badge

---

## 2. GLOBAL DESIGN SYSTEM

### Color Palette
| Token | Hex Value | Semantic Usage |
|---|---|---|
| `--color-bg-cream` | `#EAE7DF` | Primary page background, card surfaces |
| `--color-bg-light` | `#F4F2EC` | Elevated card surfaces, inputs, light accents |
| `--color-bg-dark` | `#141713` | Dark contrast sections, editorial dark banners |
| `--color-bg-menu` | `#998465` | Fullscreen overlay menu background |
| `--color-bg-contact-header`| `#D5CDB7` | Contact page hero section |
| `--color-accent-bronze` | `#A27E54` | Primary CTAs, active states, key highlights |
| `--color-accent-bronze-dark` | `#8C6B43` | Button hover state |
| `--color-accent-gold` | `#BFA069` | Footer badge frame, emblem accents |
| `--color-text-primary` | `#1A1A18` | Primary headings, dark body text |
| `--color-text-secondary`| `#5A5751` | Subheads, captions, metadata |
| `--color-text-light` | `#EAE7DF` | White/cream text on dark backgrounds |
| `--color-border-subtle` | `rgba(0, 0, 0, 0.10)` | Light section dividers, card borders |
| `--color-border-dark` | `rgba(255, 255, 255, 0.15)` | Dark section borders |

### Typography Scale
| Family | Font Stack | Usage | Weights |
|---|---|---|---|
| **Editorial Display / Headings** | `'Cormorant Garamond', Georgia, serif` | H1, H2, H3, H4, Large Quotes, Nav Overlay | 300, 400, 500, 600, Italic |
| **Editorial Body Text** | `'Roboto Slab', Georgia, serif` | Paragraphs, descriptions, bio narrative, list details | 300, 400, 500 |
| **UI, Meta & Tracking** | `'Inter', -apple-system, sans-serif` | Nav items, buttons, category tags, form labels, footer links | 400, 500, 600 (uppercase, `letter-spacing: 0.1em - 0.2em`) |
| **Signature Accent** | Hand-drawn script (`logo_extracted.png` & inline cursive) | Decorative sub-signatures and brand emblems | Regular |

### Spacing & Grid System
- **Desktop Max Container Width**: `1320px` (with `clamp(24px, 5vw, 64px)` side padding)
- **Narrow Content Container**: `920px` (for articles, about bio, contact form)
- **Vertical Rhythm / Section Spacing**:
  - Desktop: `clamp(80px, 8vw, 140px)` padding top & bottom
  - Tablet: `clamp(60px, 6vw, 90px)`
  - Mobile: `48px` to `64px`
- **Component Gap System**:
  - Micro: `8px` - `12px`
  - Small: `16px` - `24px`
  - Medium: `32px` - `48px`
  - Large: `64px` - `80px`

### Radii & Shadows
- **Border Radius**:
  - Buttons: `2px` (subtle refinement, virtually sharp editorial look)
  - Cards: `0px` to `4px` (sharp architectural lines)
  - Badges / Avatars: `50%` (circular badges, stamp seals, chat avatar)
  - Chat Widget Container: `12px` - `16px` rounded corners
- **Box Shadows**:
  - Subtle cards: `0 4px 20px rgba(0, 0, 0, 0.04)`
  - Floating badges & chat: `0 10px 30px rgba(0, 0, 0, 0.12)`
  - Header sticky state: `0 2px 15px rgba(0, 0, 0, 0.05)`

---

## 3. COMPONENT SYSTEM SPECIFICATION

1. **Header & Navigation Bar**:
   - Height: `80px` (Desktop), `68px` (Mobile)
   - Left: Signature script logo ("Zyago Studio PRODUCTIONS")
   - Center/Right (Desktop):
     - Navigation items: `HOME`, `ABOUT`, `PORTFOLIO` (with hover dropdown arrow and menu), `PRICING`, `BLOG`
     - Action button: `CONTACT US` (bronze background `#A27E54`, white uppercase text, `font-size: 11px`, `letter-spacing: 0.15em`, padding `11px 22px`)
     - Hamburger icon: Two thin horizontal bars, toggles full-screen navigation overlay
   - Mobile: Logo left, hamburger right.

2. **Full-Screen Navigation Overlay**:
   - Fixed full screen (`z-index: 9999`)
   - Background: `#998465` with smooth fade/slide transition
   - Header with white logo left and `✕` close button right
   - Main menu: Centered or left-offset giant serif links with smooth hover underline/color change
   - Portfolio submenu: Accordion toggle revealing sub-links
   - Footer bar: Social links (`INSTAGRAM`, `PINTEREST`, `EMAIL`), copyright note

3. **Circular Stamp Badge**:
   - Dimensions: `120px` × `120px` (Desktop), `90px` × `90px` (Mobile)
   - Rotating animation: `animation: rotate 24s linear infinite;`
   - Concentric text: "• HIRE US • BOOK YOUR ELOPEMENT •" around central star/monogram

4. **Oval Monogram Badge ("HD")**:
   - Double-line oval perimeter with delicate flourishes and "HD" serif letters
   - Displayed alongside editorial quotes and philosophy sections

5. **Editorial Cards & Media Grids**:
   - Image ratio: `3:4` (portrait), `16:10` (landscape), `1:1` (square)
   - Hover effect: Subtle image scale `transform: scale(1.03)` with `transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1)`
   - Editorial captions below image: Category in uppercase spaced sans-serif, title in serif italic or roman

6. **Testimonial Slider Component**:
   - Centered large serif quotation
   - Couple names & wedding location (e.g., "ELEANOR & JAMES — SOMERSET HOUSE, LONDON")
   - Circular arrow controls `←` and `→` with smooth transition

7. **Universal Footer**:
   - Top right: Back-to-top button `↑`
   - Left column: Gold-bordered square container featuring Hadi gold signature logo, "ZYAGO STUDIO", subtitle "Elopement Wedding Photographer", location "Based out of London - Available Worldwide", paragraph on inclusivity and LGBTQ+ welcoming, copyright year.
   - Middle/Right columns: Navigation links, quick contact, Instagram handle.
   - Bottom: Full-width continuous 5-column Instagram feed strip.

8. **Ivy Chat Widget**:
   - Fixed position `bottom: 24px; right: 24px; z-index: 990;`
   - Circular portrait icon (36px) with active indicator
   - Text pill: "Ivy here, let me know if you need any help!"
   - Two pill action buttons: `Call` and `Chat`
   - Sub-label: `⚡ by ElevenAgents`

---

## 4. ASSET AUDIT & PREPARATION PLAN

### Existing High-Res Content:
- `Events/Weddings/`: 8 original high-res photos (`ASM02772.jpg`, `ASM03260.jpg`, `IMG_2220.jpg`, etc.)
- `Studio Portrait/`: 62 original portrait photos (`_MG_1482.jpg`, `_MG_1749.jpg`, etc.)
- Extracted brand assets:
  - `logo_extracted.png`: Hand-drawn script logo
  - `hd_badge_clean.png`: Oval HD monogram badge
  - `stamp_badge_extracted.png`: Circular rotating stamp seal

### Asset Optimization Strategy:
- High-res images are up to 26MB each; they will be resized to high-quality web dimensions (1600px width for heroes/banners, 800px for cards and gallery items) and converted into optimized JPEG / transparent PNG files in `assets/images/`.
- Logo will be provided in transparent dark (`logo-dark.png`), white (`logo-white.png`), and gold (`logo-gold.png`).
- Badges will be transparent PNGs ready for crisp display on any background.

---

## 5. RESPONSIVE BREAKPOINTS & ADAPTATION

- **Desktop (1200px - 1440px+)**:
  - Full desktop navigation with visible text links and contact button.
  - Multi-column grids (3 or 4 columns for galleries and packages).
  - Floating badges positioned offset over hero images.
- **Tablet (768px - 1024px)**:
  - Header condenses; navigation moves to hamburger menu if needed.
  - 2-column grids for galleries, packages, and footer columns.
  - Container padding `32px`.
- **Mobile (< 768px, specifically 393px × 852px viewport)**:
  - Header: Logo left, hamburger right.
  - Single column stacked layout.
  - Full-width hero imagery with typography stacked vertically.
  - Testimonial navigation arrows easily tappable.
  - Chat widget collapses neatly to avoid obstructing touch targets.

# Skillsync AI — Design Philosophy

## Visual Identity

### Color System
Every color has a purpose, not just an aesthetic. The palette is:
- **Background** `#080e1a` — deep navy, not pure black. Softer on eyes, more depth.
- **Card surface** `#111827` — one step lighter. Creates a clear layer hierarchy.
- **Lifted state** `#161f31` — used for hover states and focused inputs.
- **Borders** `#1f2d45` → `#2e3f5e` on hover. Subtle but communicates interactivity.
- **Primary** `#6366f1` indigo — trustworthy, modern, tech-forward.
- **Secondary** `#ec4899` pink — energy, creativity, used sparingly for accents.
- **Accent** `#14b8a6` teal — success states, secondary highlights.

### Gradient Philosophy
Gradients convey *feature category*, not just decoration:
- `--g1` Purple→Violet: Primary actions, AI features, identity
- `--g2` Pink→Rose: Jobs, opportunities, outbound actions
- `--g3` Teal→Cyan: Building, creating, constructive actions
- `--g4` Amber→Orange: Progress, warnings, stats

### Typography
- **Sora** — headings only. Bold, geometric, distinctive. Never used for body text.
- **Inter** — all UI text, labels, buttons. Clean and legible at any size.
- **Crimson Pro** — resume documents only. Professional serif for print context.
- `clamp()` for fluid heading sizes. No fixed px for h1/h2.

---

## Component Architecture

### Layer System
```
z-index -1  → ambient glow (body::before)
z-index 0   → page content
z-index 100 → sticky navbar
z-index 999 → modals / dropdowns
z-index 1000 → toast notifications
```

### Card Anatomy
Every card follows this pattern:
1. `background: var(--bg-card)` base
2. `border: 1px solid var(--border)` at rest
3. `border-color: var(--border-lit)` on hover
4. `::before` pseudo-element = top accent strip (scaleX 0→1 on hover)
5. `translateY(-5px)` lift on hover
6. Gradient variant (c1/c2/c3) assigned by feature category

### Interaction Model
- **Hover** → lift + border illuminate + accent strip reveal
- **Focus** → primary color border + soft glow ring (3px rgba)
- **Active/loading** → opacity 0.6 + pointer-events none
- **Toast** → slide in from right, auto-dismiss 4s

---

## Page Structure Pattern

Every authenticated page follows:
```
navbar (80px height, fixed)
body padding-top: 80px
  .wrap (max-width, auto margins, 2.5rem padding)
    [hero section]        ← greeting / context
    [stats/summary row]   ← quick data (dashboard only)
    [main content grid]   ← 2-col on desktop, 1-col mobile
  footer (minimal: brand + copyright)
```

### Hero Section Rules
- Always has a `.hero-label` pill with a pulsing dot
- Greeting uses `<em>` with gradient text fill for username
- Subtitle in `var(--muted)` — never bold, never primary color
- Ambient glow bubble via `::after` pseudo in top-right

---

## Responsive Strategy

Mobile-first with three real breakpoints:
- `< 640px` — single column, reduced padding, hide decorative elements
- `< 900px` — 2→1 column grid collapses, stats go 2-wide
- `< 1100px` — builder/preview stack vertically

Never use `display:none` for content. Use `grid-template-columns: 1fr` to reflow.
Decorative blobs and illustrations (`resume-blobs`, ambient glows) hidden on mobile.

---

## Animation Principles

1. **Entrance** — `fadeUp` (18px + opacity). Pages feel alive, not jarring.
2. **Stagger** — 0.1s delay increments per card. Never all at once.
3. **Micro** — `translateY(-3 to -8px)` lifts on hover. Always paired with shadow.
4. **Never animate layout** — no width/height animations. Only transform + opacity.
5. **Duration** — 0.3s interactions, 0.5s entrances. Nothing slower than 0.6s.

Easing: `cubic-bezier(0.4, 0, 0.2, 1)` — Material Design standard. Feels natural.

---

## Resume Document Design (PDF context)

The resume exists in a different context to the app UI — it's a *print artifact*.
Rules diverge deliberately:
- White background, dark text — print-ready by default
- Serif font (Crimson Pro) — more readable in document form
- Accent color user-controlled — their resume, their brand
- No shadows, no glassmorphism — clean for ATS scanning
- Divider lines under section headers — classic, readable structure
- Profile picture optional, always circular by default — professional standard
### Hero Section Rules

- Always has a `.hero-label` pill with a pulsing dot
- Greeting uses `<em>` with gradient text fill for username
- Subtitle in `var(--muted)` — never bold, never primary color
- Ambient glow bubble via `::after` pseudo in top-right

---

## Responsive Strategy

Mobile-first with three real breakpoints:
- `< 640px` — single column, reduced padding, hide decorative elements
- `< 900px` — 2→1 column grid collapses, stats go 2-wide
- `< 1100px` — builder/preview stack vertically

Never use `display:none` for content. Use `grid-template-columns: 1fr` to reflow.
Decorative blobs and illustrations (`resume-blobs`, ambient glows) hidden on mobile.

---

## Animation Principles

1. **Entrance** — `fadeUp` (18px + opacity). Pages feel alive, not jarring.
2. **Stagger** — 0.1s delay increments per card. Never all at once.
3. **Micro** — `translateY(-3 to -8px)` lifts on hover. Always paired with shadow.
4. **Never animate layout** — no width/height animations. Only transform + opacity.
5. **Duration** — 0.3s interactions, 0.5s entrances. Nothing slower than 0.6s.

Easing: `cubic-bezier(0.4, 0, 0.2, 1)` — Material Design standard. Feels natural.
xaa
---

## Resume Document Design (PDF context)

The resume exists in a different context to the app UI — it's a *print artifact*.
Rules diverge deliberately:
- White background, dark text — print-ready by default
- Serif font (Crimson Pro) — more readable in document form
- Accent color user-controlled — their resume, their brand
- No shadows, no glassmorphism — clean for ATS scanning
- Divider lines under section headers — classic, readable structure
- Profile picture optional, always circular by default — professional standard
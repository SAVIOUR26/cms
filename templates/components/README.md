# KandaNews Africa — Component Library

Reusable HTML/CSS snippets. Copy the relevant block into your page.
Every component is self-contained — it includes its own CSS scoped to its class names.

---

## Available Components

| File | Component | Use on |
|---|---|---|
| `stat-counter-grid.html` | Animated count-up stat grid (2×2) | Success Story, Did You Know |
| `quote-card.html` | Glassmorphism attributed quote | Inspiration, Success Story |
| `news-card.html` | Category badge + headline + body + meta | Trending, Entertainment |
| `profile-header.html` | Photo/avatar + name + title + location | Success Story, Podcast |
| `progress-bar.html` | Labelled animated fill bar | Careers, Life Hack |
| `section-header.html` | Gradient header block (icon + title + subtitle) | Any page |
| `badge.html` | Category/label badge variants | Any page |
| `achievement-item.html` | Single stat block with icon and count-up | Success Story |
| `breathing-circle.html` | CSS breathing animation circle | Mental Health |
| `waveform.html` | Audio waveform animation bars | Podcast, Song of Africa |
| `timeline-item.html` | Single vertical timeline entry | Success Story, Book Review |

---

## How to Use

1. Open the component file
2. Copy the CSS into your page's `<style>` block
3. Copy the HTML into your page's content area
4. Copy the JS (if any) into your page's `<script>` block
5. Replace placeholder values

---

## Adding New Components

When you build a pattern in a page that will be used again:
1. Extract it into a new file here
2. Use the naming convention: `{purpose}.html`
3. Update this README table
4. Keep the component self-contained (its own CSS variables, no external dependencies)

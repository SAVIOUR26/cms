# KandaNews Africa — Claude Code Edition Builder

## Who You Are Working With

KandaNews Africa is a digital newspaper for Africa's young generation — university students,
young professionals, entrepreneurs, and changemakers across Uganda, Kenya, Nigeria, and South
Africa. The edition is a flipbook of interactive HTML pages delivered inside a mobile app.

Your job is to read raw content provided by the editorial team and build complete, stunning,
interactive HTML edition pages that follow every rule in this file.

---

## HOW TO BUILD AN EDITION

When asked to **"build edition from raw/{slug}"**, follow this exact procedure:

### Step 1 — Read Everything First
```
raw/{slug}/meta.json     → Edition type, country, theme, page list, issue number
raw/{slug}/brief.md      → All content: stories, quotes, stats, names, photos
raw/{slug}/assets/       → Images (reference them by filename)
```
Do not start building until you have read all three. If content is missing for a required
page, note it and skip that page — do not invent facts.

### Step 2 — Build Pages in Order
Build each page listed in `meta.json` → `pages` array, in order.
Each page goes into: `templates/pages/{slug}/page-NN-{type}.html`

Use the numbering from `meta.json`. Gaps are intentional (ad slots, etc.).

### Step 3 — Follow the Editorial Journey (see below)
Every page has a specific emotional purpose. Write copy that serves that purpose.
Do not treat pages as independent articles — they are chapters of one story.

### Step 4 — Quality Checklist Before Committing
Run through every built page:
- [ ] Something animates within 0.5s of slide becoming active
- [ ] At least one stat or number uses a count-up animation (if page has numbers)
- [ ] Text does not overflow the 600px height (use scrollable content section if needed)
- [ ] Accent color matches the page type color defined in the Design System
- [ ] No external CDN calls — all assets reference `/assets/vendor/` or are inline
- [ ] All images reference `assets/{filename}` relative path, not absolute URLs
- [ ] `data-category` attribute is set on `<html>` tag
- [ ] Page works standalone (open `page-XX.html` directly in browser)

### Step 5 — Commit and Push
```bash
git add templates/pages/{slug}/
git commit -m "feat(edition): build {slug} — {N} pages"
git push -u origin claude/review-repo-branches-NxYSX
```

---

## THE CANVAS — Non-Negotiable Rules

```
Width:      461px (fixed, never change)
Height:     600px max-height (content scrolls within, page does not)
Overflow:   overflow-y: auto on .page-wrapper, overflow-x: hidden
Scrollbar:  6px wide, color matches page accent, border-radius: 3px
Box model:  box-sizing: border-box on *
```

Every page starts with this exact wrapper:
```html
<!DOCTYPE html>
<html lang="en" data-category="{PAGE_TYPE}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{Page Title} - KandaNews Africa</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        .page-wrapper {
            width: 461px;
            max-height: 600px;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
        }
        /* Page-specific styles below */
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- content -->
    </div>
    <script>/* animations */</script>
</body>
</html>
```

---

## DESIGN SYSTEM

### Color Palette

```css
/* Brand */
--kn-navy:     #1e2b42   /* Primary dark background */
--kn-orange:   #f05a1a   /* Primary accent, CTA, fire */
--kn-orange-lt:#ff7a3d   /* Lighter orange for gradients */

/* Page-type Accents (each page type owns one accent) */
--color-cover:          #f05a1a   /* Orange — Arrival */
--color-trending:       #1e7e34   /* Green — Ground / Reality */
--color-success:        #f39c12   /* Amber — Achievement */
--color-mental-health:  #6c63ff   /* Indigo — Calm / Reflection */
--color-inspiration:    #9B59B6   /* Purple — Emotion / Spirit */
--color-careers:        #3498db   /* Blue — Opportunity / Action */
--color-entertainment:  #e91e8c   /* Magenta — Energy / Joy */
--color-did-you-know:   #00bcd4   /* Cyan — Curiosity / Wonder */
--color-community:      #ff6b35   /* Coral — Belonging / Warmth */
--color-song-africa:    #8B4513   /* Earth — Culture / Roots */
--color-book-review:    #2c5f2e   /* Forest — Growth / Wisdom */
--color-podcast:        #1DB954   /* Spotify Green — Audio / Discovery */
--color-back-cover:     #1e2b42   /* Navy — Closing / Movement */

/* Neutrals */
--kn-white:    #ffffff
--kn-light:    #f8f9fa
--kn-muted:    #6c757d
--kn-dark:     #1a1a2e
```

### Typography

```css
/* Font stack — system fonts, no external imports needed */
font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
             'Helvetica Neue', Arial, sans-serif;

/* Scale */
.text-display:  font-size: 32-36px; font-weight: 800; line-height: 1.1;
.text-headline: font-size: 24-28px; font-weight: 700; line-height: 1.2;
.text-title:    font-size: 18-20px; font-weight: 700; line-height: 1.3;
.text-body:     font-size: 13-14px; font-weight: 400; line-height: 1.7;
.text-caption:  font-size: 10-12px; font-weight: 600; line-height: 1.4;

/* Gradient text technique */
background: linear-gradient(135deg, #ffffff 0%, #f05a1a 100%);
-webkit-background-clip: text;
-webkit-text-fill-color: transparent;
background-clip: text;
```

### Spacing (8px grid)
```
4px, 8px, 12px, 16px, 20px, 24px, 30px, 35px, 40px, 48px
```

### Shadows
```css
--shadow-sm:  0 2px 8px rgba(0,0,0,0.08);
--shadow-md:  0 4px 15px rgba(0,0,0,0.12);
--shadow-lg:  0 8px 25px rgba(0,0,0,0.15);
--shadow-xl:  0 12px 40px rgba(0,0,0,0.2);
/* Colored glow (use accent color) */
--shadow-glow: 0 4px 20px rgba({accent-rgb}, 0.35);
```

### Border Radius
```
pill:   border-radius: 50px
badge:  border-radius: 20px
card:   border-radius: 12-15px
input:  border-radius: 8px
```

---

## ANIMATION SYSTEM

### Rule: Animations Trigger on Slide Entry
All animations must be controlled by JavaScript using CSS classes — NOT CSS `animation`
auto-play. This is critical because Swiper loads all slides in the DOM simultaneously.
Auto-playing CSS animations will have already finished before the reader reaches the page.

**Standard pattern for every page:**
```javascript
// At bottom of every page's <script> block
function initPage() {
    // Add animation classes to trigger CSS transitions
    document.querySelectorAll('[data-animate]').forEach((el, i) => {
        setTimeout(() => el.classList.add('is-visible'), i * 120);
    });
    // Trigger stat counters
    document.querySelectorAll('[data-count]').forEach(el => {
        countUp(el, 0, parseInt(el.dataset.count), el.dataset.suffix || '');
    });
    // Trigger progress bars
    document.querySelectorAll('[data-fill]').forEach(el => {
        el.style.width = el.dataset.fill;
    });
}

// Self-activating: runs if this page is open directly in browser
// When embedded in flipbook, the Swiper listener calls initPage() instead
if (document.querySelector('.page-wrapper')) {
    setTimeout(initPage, 300);
}
```

**CSS animation base classes (include in every page):**
```css
[data-animate] {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease, transform 0.5s ease;
}
[data-animate].is-visible {
    opacity: 1;
    transform: translateY(0);
}
[data-animate][data-delay="1"] { transition-delay: 0.1s; }
[data-animate][data-delay="2"] { transition-delay: 0.2s; }
[data-animate][data-delay="3"] { transition-delay: 0.3s; }
[data-animate][data-delay="4"] { transition-delay: 0.4s; }
[data-animate][data-delay="5"] { transition-delay: 0.5s; }
```

**Count-up function (include wherever stats exist):**
```javascript
function countUp(el, start, end, suffix) {
    const duration = 1500;
    const startTime = performance.now();
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
        el.textContent = Math.round(start + (end - start) * eased).toLocaleString() + suffix;
        if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
}
```

**Progress bar transition (include wherever bars exist):**
```css
.progress-bar-fill {
    width: 0%;
    transition: width 1.2s ease-out 0.3s;
}
```

### Standard Animations Available
```css
/* Use these @keyframes in pages that need continuous CSS animation (cover only) */
@keyframes float    { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
@keyframes pulse    { 0%,100% { transform: scale(1); }      50% { transform: scale(1.06); } }
@keyframes glow     { 0%,100% { box-shadow: 0 0 10px rgba(240,90,26,0.4); } 50% { box-shadow: 0 0 25px rgba(240,90,26,0.8); } }
@keyframes shimmer  { 0% { background-position: -200% center; } 100% { background-position: 200% center; } }
@keyframes spin     { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
```

---

## PAGE CATALOG

Every edition uses a subset of these page types. The `meta.json` file specifies which ones.
Reference the existing built examples in `templates/pages/` as your baseline.

### 1. COVER (`data-category="cover"`)
**Emotional purpose:** ARRIVE — First impression. Make them stop scrolling. Spark curiosity.
**Must include:** KandaNews logo area, edition date, bold headline, content preview (what's inside),
animated particles or background, edition badges.
**Reference:** `templates/pages/page-01-cover.html`
**Accent:** `#f05a1a`
**Note:** Cover is the ONLY page where CSS animations may auto-play (no Swiper trigger needed).

### 2. WHY KANDANEWS / WELCOME (`data-category="welcome"`)
**Emotional purpose:** ORIENT — "You belong here. This space is yours."
**Must include:** Warm welcome message, mission statement, what to expect, 3 value pillars.
**Tone:** Inclusive, warm, like a friend speaking. Use "you" and "your".
**Reference:** `templates/pages/page-02-why-kandanews.html`

### 3. TRENDING NEWS — Country (`data-category="trending"`)
**Emotional purpose:** GROUND — Connect them to what's real and happening right now.
**Must include:** Country flag + name, 3-5 news stories, category badges, source + time.
**Each story needs:** badge (category), headline, 2-3 sentence body, meta (time, source).
**Accent:** `#1e7e34` (green)
**Reference:** `templates/pages/page-04-trending-uganda.html`
**Content required in brief:** story title, body text, category, source, time ago.

### 4. DID YOU KNOW (`data-category="did-you-know"`)
**Emotional purpose:** SURPRISE — Expand their world. Challenge what they assumed was normal.
**Must include:** 4-5 numbered facts, icons, animated number reveals, a "challenge" question at end.
**Copy rule:** Each fact starts with the surprising NUMBER, then the context. Never bury the stat.
**Accent:** `#00bcd4` (cyan)
**Reference:** `templates/pages/page-06-did-you-know.html`

### 5. SUCCESS STORY (`data-category="success-story"`)
**Emotional purpose:** ASPIRE — "If they built that from here, so can you."
**Must include:** Profile photo or avatar, name, title, location, 2 story sections (The Innovation
+ The Journey), achievement grid (4 stats with count-up animation), signature quote, lesson box.
**Achievement grid:** Always 4 stats — animated count-up from zero.
**Quote rule:** Use the person's actual words. Bold and prominent. No paraphrasing.
**Tone:** Celebratory but grounded. Not distant. "Brian didn't need a PhD. He needed curiosity."
**Accent:** `#f39c12` (amber)
**Reference:** `templates/pages/page-10-success-story.html`

### 6. MENTAL HEALTH (`data-category="mental-health"`)
**Emotional purpose:** SOFTEN — Create a moment of permission to breathe and feel.
**Must include:** Soft gradient header, a breathing exercise or calming visual, affirming message,
one actionable tip, a "check-in" question.
**Tone:** No advice-giving. No telling them what to do. Gentle, present, human.
**Rule:** NEVER use the words "just", "simply", "easy". These invalidate feelings.
**Accent:** `#6c63ff` (indigo)
**Reference:** `templates/pages/page-09-mental-health.html`

### 7. INSPIRATION / POEM (`data-category="inspiration"`)
**Emotional purpose:** FEEL — The emotional peak of the edition. Let language do the work.
**Must include:** A poem, quote, or short piece of writing with artistic typographic treatment.
Word-by-word or line-by-line animated reveal. Bold visual presentation.
**Copy rule:** The writing on this page must be original or properly attributed. If from brief,
use it exactly. Do not summarize.
**Tone:** Poetic, elevated, intentional. Every word earns its place.
**Accent:** `#9B59B6` (purple)
**Reference:** `templates/pages/page-08-inspiration-poem.html`

### 8. CAREERS & JOBS (`data-category="careers"`)
**Emotional purpose:** ACT — Here is your next concrete step. Opportunity made visible.
**Must include:** 3-5 job/opportunity listings, salary or stipend if available, deadline,
a "featured opportunity" highlighted at top, application CTA.
**Copy rule:** Listings should feel attainable, not intimidating. No corporate jargon.
**Accent:** `#3498db` (blue)
**Reference:** `templates/pages/page-13-careers-jobs.html`

### 9. ENTERTAINMENT (`data-category="entertainment"`)
**Emotional purpose:** CELEBRATE — Joy, culture, vibrancy. Let them laugh and enjoy.
**Must include:** Top entertainment stories, music/film/sport highlights, trending topic,
fun interactive element (rating, favourite pick).
**Accent:** `#e91e8c` (magenta)
**Reference:** `templates/pages/page-16-entertainment.html`

### 10. SONG OF AFRICA (`data-category="song-of-africa"`)
**Emotional purpose:** ROOTS — Reconnect them to African culture, heritage, and pride.
**Must include:** Featured song or artist, lyrics excerpt or story behind the song,
artist bio snippet, cultural context, waveform visual.
**Accent:** `#8B4513` (earth/sienna)
**Reference:** `templates/pages/page-18-song-of-africa.html`

### 11. BOOK REVIEW (`data-category="book-review"`)
**Emotional purpose:** GROW — Plant a seed of knowledge they will carry past the edition.
**Must include:** Book cover visual (emoji or image), author, rating (animated stars), 3 key
takeaways, one quote from the book, "Why read it" section.
**Accent:** `#2c5f2e` (forest green)
**Reference:** `templates/pages/page-17-book-review.html`

### 12. LIFE HACK (`data-category="life-hack"`)
**Emotional purpose:** EMPOWER — Give them something immediately useful they can apply today.
**Must include:** 3-5 numbered hacks, clear benefit statement per hack, time/cost saving stat.
**Copy rule:** Lead with the outcome, not the action. "Save 2 hours a week → Use this one habit"
**Reference:** `templates/pages/page-11-life-hack.html`

### 13. COMMUNITY / SHOUTOUTS (`data-category="community"`)
**Emotional purpose:** BELONG — You are not alone. This movement is real and growing.
**Must include:** 4-6 reader shoutouts or community highlights, names + locations, brief message,
community stat (total readers, countries reached), CTA to join.
**Accent:** `#ff6b35` (coral)
**Reference:** `templates/pages/page-20-shoutouts-community.html`

### 14. KANDA PODCAST (`data-category="podcast"`)
**Emotional purpose:** DISCOVER — There is more depth available if they want it.
**Must include:** Episode title, guest name + bio snippet, 3 key topics discussed,
audio waveform visual, episode number + duration, listen CTA.
**Accent:** `#1DB954` (green)
**Reference:** `templates/pages/page-19-kanda-podcast.html`

### 15. NEXT EDITION TEASER (`data-category="next-edition"`)
**Emotional purpose:** ANTICIPATE — Leave them wanting to come back.
**Must include:** "Coming Next" headline, 3 teased topics (vague enough to create curiosity),
next edition date, subscribe/notification CTA.
**Copy rule:** Never give too much away. Intrigue over information.
**Reference:** `templates/pages/page-21-next-edition.html`

### 16. BACK COVER (`data-category="back-cover"`)
**Emotional purpose:** MOVE — This is the closing call to action. What will they do next?
**Must include:** Closing message (warm, not salesy), 2-3 action prompts (share, follow, apply),
KandaNews logo, social handles, one powerful closing line.
**Copy rule:** The last line of the entire edition should be a statement, not a question.
End on power, not uncertainty.
**Reference:** `templates/pages/page-22-back-cover.html`

---

## THE EDITORIAL JOURNEY — Language Flow

This is the most important section. Pages are not independent articles.
They are **chapters of a single intentional movement**.

```
Page 1   COVER          → ARRIVE      "Something important is here. Come in."
Page 2   WELCOME        → ORIENT      "This is your space. You belong here."
Page 3   TRENDING       → GROUND      "This is what's happening in your world right now."
Page 4   DID YOU KNOW   → SURPRISE    "The world is bigger and stranger than you knew."
Page 5   SUCCESS STORY  → ASPIRE      "Someone from here built something extraordinary."
Page 6   MENTAL HEALTH  → SOFTEN      "It is okay to feel what you are feeling."
Page 7   INSPIRATION    → FEEL        "Let these words move you."
Page 8   CAREERS        → ACT         "Here is your concrete next step. Take it."
Page 9   ENTERTAINMENT  → CELEBRATE   "Now — enjoy. You've earned this."
Page 10  COMMUNITY      → BELONG      "Look — others are on this journey with you."
Page 11  BACK COVER     → MOVE        "Go. Do something. The world is waiting for you."
```

### Language Rules That Apply Across All Pages

1. **Write to one person.** Always "you", never "readers" or "young Africans".
   Wrong: "Young Africans across the continent are rising."
   Right: "You are part of something that is rising."

2. **Earn every word.** If a sentence can be removed without losing meaning, remove it.
   Edition pages are not blogs. Every line must pull weight.

3. **Lead with the human, not the fact.**
   Wrong: "A $5M startup was founded in Kampala."
   Right: "At 26, with no investor and no MBA, Amara built a $5M company from a Kampala bedroom."

4. **Action language in CTAs.** No passive invitations.
   Wrong: "You can learn more at..."
   Right: "Read it. Apply it. Tell us what changed."

5. **Progression rule.** Each page should feel like the natural continuation of the one before.
   If you can swap two pages without the reader noticing, the journey is broken.

6. **The closing line rule.** Every page ends with either:
   - A powerful statement (not a question)
   - A direct, confident call to action
   - A line the reader will remember tomorrow

---

## COMPONENT LIBRARY

Reusable HTML snippets are in `templates/components/`. Always check here before
writing a pattern from scratch. If you build something new that will be reused,
add it to the library after the edition is done.

Key components:
- `stat-counter-grid.html`   — 2×2 or 2×3 grid of animated count-up stats
- `quote-card.html`          — Glassmorphism quote with attribution
- `news-card.html`           — Standard news item with badge, title, body, meta
- `profile-header.html`      — Person photo, name, title, location
- `progress-bar.html`        — Labelled animated progress bar
- `badge.html`               — Category/label badge variants
- `section-header.html`      — Gradient header block with icon, title, subtitle

---

## FILE NAMING CONVENTION

```
templates/pages/{edition-slug}/
  page-01-cover.html
  page-02-welcome.html
  page-03-trending-{country}.html
  page-04-did-you-know.html
  page-05-success-story.html
  ...
  page-{NN}-{type}.html
```

Page numbers must be zero-padded two digits: `01`, `02`, `03`...
Types must match `data-category` values from the Page Catalog above.

---

## ASSETS REFERENCE

Images from `raw/{slug}/assets/` should be referenced in built pages as:
```html
<img src="../../raw/{slug}/assets/{filename}" alt="...">
```
During generation, the bundler will resolve and inline these.

If no image is provided for a profile/photo slot, use an emoji avatar in a
styled circle — never leave an empty `<img>` tag.

---

## WHAT YOU MUST NEVER DO

- Do not invent facts, statistics, names, or quotes not in the brief
- Do not use placeholder text (Lorem ipsum, [INSERT], TBD)
- Do not use external CDN links — reference `/assets/vendor/` for FontAwesome
- Do not create files outside of `templates/pages/{slug}/`
- Do not commit without the quality checklist passing
- Do not use the words "just", "simply", "easy" in any reader-facing copy
- Do not end any page on a question — end on a statement or action

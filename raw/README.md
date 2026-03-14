# KandaNews Africa — Raw Content Upload Guide

This folder is where the editorial team drops all raw content for a new edition.
Claude Code reads from here and builds the complete interactive HTML pages.

---

## HOW TO UPLOAD CONTENT FOR A NEW EDITION

### Step 1 — Name Your Edition Folder

Create a new folder inside `raw/` using this exact naming format:

```
YYYY-MM-DD-{country-code}-{type}
```

| Edition Type | Example Folder Name |
|---|---|
| Daily edition | `2026-03-14-ug-daily` |
| Special edition | `2026-04-01-ug-university` |
| Holiday edition | `2026-12-25-ug-christmas` |
| Regional edition | `2026-03-14-ke-daily` |
| Multi-country | `2026-03-14-ea-daily` |

**Country codes:** `ug` Uganda · `ke` Kenya · `ng` Nigeria · `za` South Africa · `ea` East Africa

### Step 2 — Copy the Template

Copy the entire `_template/` folder and rename it to your edition name:
```
cp -r raw/_template/ raw/2026-03-14-ug-daily/
```

### Step 3 — Fill in the Three Files

Your edition folder must contain exactly these three things:

```
raw/
└── 2026-03-14-ug-daily/
    ├── meta.json          ← Edition settings and page list
    ├── brief.md           ← All content (stories, quotes, stats, copy)
    └── assets/            ← Photos and images
        ├── cover-bg.jpg
        └── ...
```

### Step 4 — Tell Claude to Build

Once your three files are ready, run Claude Code and type:

```
Build the edition from raw/2026-03-14-ug-daily
```

Claude will read everything, build all pages, and push to the branch.

---

## FILE SPECIFICATIONS

### `meta.json` — Edition Settings

This tells Claude what kind of edition this is and which pages to build.

**Required fields:**

| Field | Type | Description |
|---|---|---|
| `slug` | string | Must match the folder name exactly |
| `title` | string | Full edition title as it appears on the cover |
| `date` | string | Display date e.g. `"March 14, 2026"` |
| `issue_number` | string | e.g. `"047"` (zero-padded) |
| `volume` | string | e.g. `"Vol. 2"` |
| `edition_type` | string | `"daily"` or `"special_edition"` |
| `country` | string | Full country name e.g. `"Uganda"` |
| `country_code` | string | ISO code e.g. `"UG"` |
| `country_flag` | string | Flag emoji e.g. `"🇺🇬"` |
| `theme` | string | See theme options below |
| `tagline` | string | Short cover tagline, max 8 words |
| `pages` | array | Ordered list of page types to build |

**Theme options:** `"default"` · `"university"` · `"health"` · `"business"` · `"elections"` · `"culture"`

**Page type values for the `pages` array:**
```
"cover", "welcome", "trending", "did-you-know", "success-story",
"mental-health", "inspiration", "careers", "entertainment",
"song-of-africa", "book-review", "life-hack", "community",
"podcast", "next-edition", "back-cover"
```

See `_template/meta.json` for the full example.

---

### `brief.md` — Content Specification

This is the single most important file. It contains every piece of content
that will appear in the edition. Claude will not invent anything that is not
written here.

**Format rules:**
- Use the exact section headers shown in `_template/brief.md`
- Each section header corresponds to one page type
- Only include sections for pages listed in your `meta.json → pages` array
- Stats must include their unit: `"500K patients"` not `"500000"`
- Quotes must be in quotation marks and attributed: `"The quote." — Name, Title`
- Images must reference exact filenames from your `assets/` folder
- Leave a field blank rather than writing placeholder text

**What happens if a section is missing:**
- Claude will skip that page and note it in the build log
- No content will be invented

See `_template/brief.md` for the full format with all sections.

---

### `assets/` — Images and Media

Place all image files here. Name them clearly.

**Naming conventions:**

| Purpose | Filename |
|---|---|
| Cover background photo | `cover-bg.jpg` |
| Success story portrait | `profile-{firstname}.jpg` |
| Podcast guest | `podcast-guest.jpg` |
| Book cover | `book-cover.jpg` |
| Community photos | `community-01.jpg`, `community-02.jpg`... |
| Sponsor logo | `sponsor-{name}.png` |
| General illustration | `hero-{page-type}.jpg` |

**Image specs:**
- Format: JPG or PNG
- Max size per image: 500KB (images are bundled into self-contained HTML)
- Recommended dimensions: at least 600×400px
- Portrait/profile photos: square crop preferred (400×400px minimum)

**If no image is available:**
- Leave `assets/` empty — Claude will use styled emoji avatars and gradient placeholders
- Do NOT upload low-resolution or blurry images
- Do NOT upload images with heavy watermarks

---

## EXAMPLE — What a Complete Upload Looks Like

```
raw/
└── 2026-03-14-ug-daily/
    ├── meta.json
    ├── brief.md
    └── assets/
        ├── cover-bg.jpg         ← Kampala skyline for cover
        ├── profile-amara.jpg    ← Success story portrait
        └── podcast-guest.jpg   ← Episode guest headshot
```

And `brief.md` contains filled content for all 10 pages listed in `meta.json`.

---

## WHAT HAPPENS AFTER YOU UPLOAD

```
You drop content into raw/{slug}/
            ↓
Claude Code reads meta.json + brief.md + assets/
            ↓
Claude builds each page → templates/pages/{slug}/
            ↓
Claude commits and pushes to branch
            ↓
GitHub Actions fires → server receives pages
            ↓
PHP bundler assembles flipbook → output/{slug}/index.html
            ↓
API registers edition as draft
            ↓
Portal editor reviews → clicks Publish
            ↓
Edition goes live in the app
```

---

## FOLDER STRUCTURE AT A GLANCE

```
raw/
├── README.md                    ← This file (the guide)
├── _template/                   ← Copy this to start every edition
│   ├── meta.json
│   ├── brief.md
│   └── assets/
│       └── .gitkeep
│
├── 2026-03-14-ug-daily/         ← Example completed edition
│   ├── meta.json
│   ├── brief.md
│   └── assets/
│
└── {your-next-edition}/         ← Your new edition goes here
    ├── meta.json
    ├── brief.md
    └── assets/
```

---

## COMMON MISTAKES TO AVOID

| Mistake | What Happens | Fix |
|---|---|---|
| Folder name doesn't match `slug` in meta.json | Build fails | Make them identical |
| Missing a page section in brief.md | That page is skipped | Add the section |
| Image filename in brief doesn't match actual file | Broken image | Check spelling exactly |
| Placeholder text left in brief.md | Placeholder published live | Fill all fields |
| More pages in meta.json than in brief.md | Skipped pages | Write content for every page listed |

---

*For questions about this process, see the full system documentation in `docs/`.*

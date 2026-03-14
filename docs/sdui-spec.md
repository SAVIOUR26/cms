# KandaNews Africa — SDUI Specification
## Server-Driven UI for Special Editions

**Version:** 1.0
**Applies to:** `edition_type = 'special'` and `edition_type = 'rate_card'`
**Controlled from:** Portal → Special Editions → Design Card
**Consumed by:** Flutter app — `SpecialEditionCard` widget

---

## What SDUI Means Here

For Special Editions, the Flutter app contains **zero hardcoded UI decisions**.
Every visual property of a special edition card — colors, layout, badge text,
background treatment, call-to-action label — comes from the server.

The portal operator can:
- Launch a new special edition theme without an app release
- Update the visual identity of any edition in real time
- A/B test different card designs by country
- Ensure every edition has a distinct, on-brand visual identity

The app's job is to be a faithful **renderer** of what the server describes.

---

## Where the Config Lives

```sql
editions.card_config  -- JSON column, NULL for daily editions
```

This column is populated from the portal's SDUI Card Builder.
The Flutter API endpoint returns it decoded as an object in the response.

---

## The JSON Schema

### Full Example

```json
{
  "version": 1,
  "preset": "university",
  "card": {
    "layout": "full_bleed",
    "background": {
      "type": "gradient",
      "colors": ["#1e2b42", "#3B82F6"],
      "angle": 135
    },
    "cover_treatment": "overlay"
  },
  "badge": {
    "text": "UNIVERSITY",
    "bg_color": "#3B82F6",
    "text_color": "#FFFFFF",
    "icon": "school"
  },
  "typography": {
    "title_color": "#FFFFFF",
    "subtitle_color": "rgba(255,255,255,0.80)",
    "accent_color": "#FCD34D"
  },
  "cta": {
    "label": "Read Edition",
    "bg_color": "#FCD34D",
    "text_color": "#1e2b42"
  }
}
```

---

## Field Reference

### Root

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `version` | integer | Yes | Schema version. Always `1` for now. App uses this for backwards compatibility. |
| `preset` | string | No | Named preset used (e.g. `"university"`). Informational only — the rendered config overrides all preset defaults. |
| `card` | object | Yes | Card-level layout and background settings |
| `badge` | object | Yes | The pill/chip label shown on the card |
| `typography` | object | Yes | Text color overrides |
| `cta` | object | Yes | Call-to-action button config |

---

### `card` Object

| Field | Type | Options | Default | Description |
|-------|------|---------|---------|-------------|
| `layout` | string | `full_bleed` `split` `compact` `hero` | `full_bleed` | Card layout variant |
| `background.type` | string | `gradient` `solid` `image` `image_overlay` | `gradient` | How the card background is rendered |
| `background.colors` | array | 1–3 hex strings | — | Gradient stops (or single solid color) |
| `background.angle` | integer | 0–360 | `135` | CSS gradient angle in degrees |
| `cover_treatment` | string | `none` `overlay` `blur_bottom` | `overlay` | How the cover image is treated when layout includes an image |

**Layout options:**
- `full_bleed` — Cover image fills the entire card; text overlaid with dark gradient
- `split` — Image on left half, text on right half
- `compact` — Small thumbnail top-left, text alongside; for list-style displays
- `hero` — Large immersive card; prominent visual, minimal text overlay

---

### `badge` Object

| Field | Type | Description |
|-------|------|-------------|
| `text` | string | Badge label (max 14 chars). Use uppercase. e.g. `"UNIVERSITY"`, `"SPECIAL"`, `"NEW"` |
| `bg_color` | hex string | Badge background color |
| `text_color` | hex string | Badge text color (ensure WCAG AA contrast with bg_color) |
| `icon` | string | Flutter `Icons.*` key without the `Icons.` prefix. e.g. `"school"`, `"work"`, `"podcast"` |

**Common icon keys:**
```
school          → University
business        → Corporate
rocket_launch   → Entrepreneurship
campaign        → Campaigns
work            → Jobs & Careers
podcasts        → Podcasts
play_circle     → Episodes
price_change    → Rate Card
star            → Featured / General Special
favorite        → Community
```

---

### `typography` Object

| Field | Type | Description |
|-------|------|-------------|
| `title_color` | hex / rgba string | Edition title text color |
| `subtitle_color` | hex / rgba string | Date, tag, or subtitle text color |
| `accent_color` | hex string | Decorative accent; used for icons, dividers, highlights |

---

### `cta` Object

| Field | Type | Description |
|-------|------|-------------|
| `label` | string | Button text (max 20 chars). e.g. `"Read Edition"`, `"Open Now"`, `"Explore"` |
| `bg_color` | hex string | Button background color |
| `text_color` | hex string | Button text color |

---

## Preset Library

These are the standard starting points. The portal card builder applies them with one click.
The operator can then customise any field after applying a preset.

| Preset Key | Category | Background | Badge Color | Accent |
|---|---|---|---|---|
| `university` | University | `#1e2b42` → `#3B82F6` at 135° | `#3B82F6` | `#FCD34D` |
| `corporate` | Corporate | `#1E2B42` → `#374151` at 160° | `#1E2B42` | `#F59E0B` |
| `entrepreneurship` | Entrepreneurship | `#c2410c` → `#F05A1A` at 135° | `#F05A1A` | `#FCD34D` |
| `campaigns` | Campaigns | `#7c3aed` → `#EF4444` at 145° | `#EF4444` | `#FBBF24` |
| `jobs_careers` | Jobs & Careers | `#065f46` → `#10B981` at 135° | `#10B981` | `#FFFFFF` |
| `podcasts` | Podcasts | `#4c1d95` → `#8B5CF6` at 150° | `#8B5CF6` | `#FCD34D` |
| `episodes` | Episodes | `#92400e` → `#F59E0B` at 135° | `#F59E0B` | `#FFFFFF` |
| `culture` | Culture / Song of Africa | `#78350f` → `#B45309` at 135° | `#8B4513` | `#FCD34D` |
| `health` | Health | `#065f46` → `#059669` at 135° | `#059669` | `#FFFFFF` |
| `elections` | Elections | `#1e2b42` → `#DC2626` at 135° | `#DC2626` | `#FBBF24` |

---

## API Response

When `card_config` is populated, the API includes it decoded in all edition responses:

### `GET /editions?type=special`

```json
{
  "ok": true,
  "data": {
    "editions": [
      {
        "id": 42,
        "title": "KandaNews University Edition 2026",
        "slug": "2026-03-01-ug-university",
        "country": "ug",
        "edition_date": "2026-03-01",
        "edition_type": "special",
        "category": "university",
        "cover_image": "https://ug.kandanews.africa/output/.../cover.jpg",
        "html_url": "https://ug.kandanews.africa/output/.../index.html",
        "page_count": 18,
        "is_free": false,
        "accessible": true,
        "card_config": {
          "version": 1,
          "preset": "university",
          "card": {
            "layout": "full_bleed",
            "background": {
              "type": "gradient",
              "colors": ["#1e2b42", "#3B82F6"],
              "angle": 135
            },
            "cover_treatment": "overlay"
          },
          "badge": {
            "text": "UNIVERSITY",
            "bg_color": "#3B82F6",
            "text_color": "#FFFFFF",
            "icon": "school"
          },
          "typography": {
            "title_color": "#FFFFFF",
            "subtitle_color": "rgba(255,255,255,0.80)",
            "accent_color": "#FCD34D"
          },
          "cta": {
            "label": "Read Edition",
            "bg_color": "#FCD34D",
            "text_color": "#1e2b42"
          }
        }
      }
    ]
  }
}
```

### When `card_config` is NULL

The API returns `"card_config": null`.
The Flutter app falls back to its default special edition card rendering using:
- `category` field to look up color from `edition_categories`
- `cover_image` as the card background
- Generic "Read Edition" CTA

---

## Flutter Implementation Contract

The `SpecialEditionCard` widget must:

1. Check if `card_config != null`. If null → use default rendering.
2. Read `card_config.card.layout` and select the corresponding layout widget.
3. Apply `card_config.card.background`:
   - `gradient` → `LinearGradient` with `colors` and angle converted from CSS degrees
   - `solid` → `BoxDecoration(color: Color(...))`
   - `image` → `DecorationImage` with `cover_image` URL
   - `image_overlay` → `DecorationImage` + semi-transparent gradient overlay
4. Render badge using `badge.bg_color`, `badge.text_color`, `badge.text`, `badge.icon`
5. Apply `typography` colors to title, subtitle, and accent elements
6. Render CTA button with `cta.bg_color`, `cta.text_color`, `cta.label`
7. On tap → navigate to reader with `html_url`

**Color parsing helper:**
```dart
// hex string (#RRGGBB or rgba(r,g,b,a)) → Color
Color parseColor(String hex) {
  if (hex.startsWith('rgba')) {
    final parts = RegExp(r'[\d.]+').allMatches(hex).map((m) => m.group(0)!).toList();
    return Color.fromRGBO(int.parse(parts[0]), int.parse(parts[1]),
        int.parse(parts[2]), double.parse(parts[3]));
  }
  final clean = hex.replaceFirst('#', '');
  return Color(int.parse('FF$clean', radix: 16));
}
```

**Gradient angle conversion:**
```dart
// CSS angle (degrees, 0=top) → Flutter AlignmentGeometry
Alignment gradientBegin(int angle) =>
    Alignment(-sin(angle * pi / 180), -cos(angle * pi / 180));
Alignment gradientEnd(int angle) =>
    Alignment(sin(angle * pi / 180), cos(angle * pi / 180));
```

---

## Backwards Compatibility

- Daily editions never have `card_config` — they are always rendered with default app UI
- If `card_config.version > 1`, the app should fall back to default (future-proofing)
- Adding new fields to the JSON does not break existing apps (Flutter ignores unknown keys)
- Removing or renaming fields requires a version bump from `1` to `2`

---

## Portal Workflow

```
Portal → Special Editions → [Design Card] button
  ↓
edition-sdui.php?id={edition_id}
  ↓
Select preset → customise → live preview updates
  ↓
Save → card_config JSON written to editions.card_config
  ↓
API immediately returns new config on next request
  ↓
Flutter app renders updated card on next home screen load
```

No deployment required. No app release required. Changes are live immediately.

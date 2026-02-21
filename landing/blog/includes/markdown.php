<?php
/**
 * KandaNews — Lightweight Flat-File Blog Engine
 * Parses YAML front matter + Markdown → HTML.
 * Zero external dependencies.
 */

/**
 * Parse front matter (between --- delimiters) from a markdown string.
 * Returns ['meta' => [...], 'body' => '...']
 */
function parse_front_matter(string $raw): array {
    $meta = [];
    $body = $raw;

    if (preg_match('/\A---\s*\n(.*?)\n---\s*\n(.*)\z/s', $raw, $m)) {
        foreach (explode("\n", $m[1]) as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $val] = explode(':', $line, 2);
                $meta[trim($key)] = trim($val);
            }
        }
        $body = $m[2];
    }

    return ['meta' => $meta, 'body' => $body];
}

/**
 * Convert Markdown string to HTML.
 * Handles: headers, bold, italic, links, images, code blocks,
 * inline code, lists, blockquotes, horizontal rules, paragraphs.
 */
function markdown_to_html(string $md): string {
    $html = '';
    $md = str_replace("\r\n", "\n", $md);

    // ── Fenced code blocks (```lang ... ```) ──
    $md = preg_replace_callback('/```(\w*)\n(.*?)```/s', function ($m) {
        $lang = $m[1] ? ' class="language-' . htmlspecialchars($m[1]) . '"' : '';
        $code = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
        return '<pre><code' . $lang . '>' . $code . '</code></pre>';
    }, $md);

    $lines = explode("\n", $md);
    $out = [];
    $in_list = false;
    $list_type = '';
    $in_bq = false;
    $bq_buf = [];

    $flush_bq = function () use (&$out, &$in_bq, &$bq_buf) {
        if ($in_bq) {
            $inner = markdown_to_html(implode("\n", $bq_buf));
            $out[] = '<blockquote>' . $inner . '</blockquote>';
            $bq_buf = [];
            $in_bq = false;
        }
    };

    $flush_list = function () use (&$out, &$in_list, &$list_type) {
        if ($in_list) {
            $out[] = $list_type === 'ul' ? '</ul>' : '</ol>';
            $in_list = false;
            $list_type = '';
        }
    };

    foreach ($lines as $line) {
        // ── Pre/code blocks (already converted to HTML above) ──
        if (preg_match('/^<pre>/', $line)) {
            $flush_bq();
            $flush_list();
            $out[] = $line;
            continue;
        }

        // ── Blockquote ──
        if (preg_match('/^>\s?(.*)$/', $line, $m)) {
            $flush_list();
            $in_bq = true;
            $bq_buf[] = $m[1];
            continue;
        } elseif ($in_bq) {
            $flush_bq();
        }

        // ── Horizontal rule ──
        if (preg_match('/^(\*{3,}|-{3,}|_{3,})\s*$/', $line)) {
            $flush_list();
            $out[] = '<hr>';
            continue;
        }

        // ── Headers ──
        if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $m)) {
            $flush_list();
            $lvl = strlen($m[1]);
            $text = inline_md($m[2]);
            $id = slug($m[2]);
            $out[] = '<h' . $lvl . ' id="' . $id . '">' . $text . '</h' . $lvl . '>';
            continue;
        }

        // ── Unordered list ──
        if (preg_match('/^[\*\-\+]\s+(.+)$/', $line, $m)) {
            if (!$in_list || $list_type !== 'ul') {
                $flush_list();
                $out[] = '<ul>';
                $in_list = true;
                $list_type = 'ul';
            }
            $out[] = '<li>' . inline_md($m[1]) . '</li>';
            continue;
        }

        // ── Ordered list ──
        if (preg_match('/^\d+\.\s+(.+)$/', $line, $m)) {
            if (!$in_list || $list_type !== 'ol') {
                $flush_list();
                $out[] = '<ol>';
                $in_list = true;
                $list_type = 'ol';
            }
            $out[] = '<li>' . inline_md($m[1]) . '</li>';
            continue;
        }

        // ── Close list if no list line ──
        $flush_list();

        // ── Blank line ──
        if (trim($line) === '') {
            $out[] = '';
            continue;
        }

        // ── Already HTML (from code block conversion) ──
        if (preg_match('/^</', $line)) {
            $out[] = $line;
            continue;
        }

        // ── Paragraph ──
        $out[] = '<p>' . inline_md($line) . '</p>';
    }

    $flush_bq();
    $flush_list();

    return implode("\n", $out);
}

/**
 * Inline markdown: bold, italic, links, images, inline code.
 */
function inline_md(string $text): string {
    // Inline code
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    // Images
    $text = preg_replace('/!\[([^\]]*)\]\(([^\)]+)\)/', '<img src="$2" alt="$1" loading="lazy">', $text);
    // Links
    $text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $text);
    // Bold
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    // Italic
    $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
    return $text;
}

/**
 * Generate a URL-safe slug from text.
 */
function slug(string $text): string {
    $text = strip_tags($text);
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\-]+/', '-', $text);
    return trim($text, '-');
}

// ─────────────────────────────────────────────
//  Blog data helpers
// ─────────────────────────────────────────────

/**
 * Load all posts from the posts/ directory.
 * Returns array sorted by date descending.
 */
function get_all_posts(string $posts_dir): array {
    $posts = [];
    $files = glob($posts_dir . '/*.md');
    if (!$files) return [];

    foreach ($files as $file) {
        $raw = file_get_contents($file);
        $parsed = parse_front_matter($raw);
        $meta = $parsed['meta'];
        $slug = pathinfo($file, PATHINFO_FILENAME);

        $posts[] = [
            'slug'    => $slug,
            'title'   => $meta['title'] ?? ucwords(str_replace('-', ' ', $slug)),
            'date'    => $meta['date'] ?? '',
            'author'  => $meta['author'] ?? 'KandaNews Team',
            'excerpt' => $meta['excerpt'] ?? '',
            'image'   => $meta['image'] ?? '',
            'tags'    => $meta['tags'] ?? '',
            'file'    => $file,
        ];
    }

    // Sort newest first
    usort($posts, function ($a, $b) {
        return strcmp($b['date'], $a['date']);
    });

    return $posts;
}

/**
 * Load a single post by slug.
 * Returns ['meta' => [...], 'html' => '...'] or null.
 */
function get_post(string $posts_dir, string $slug): ?array {
    // Sanitize slug — alphanumeric, hyphens, dots only
    $slug = preg_replace('/[^a-zA-Z0-9\-\.]/', '', $slug);
    $file = $posts_dir . '/' . $slug . '.md';

    if (!is_file($file)) return null;

    $raw = file_get_contents($file);
    $parsed = parse_front_matter($raw);
    $meta = $parsed['meta'];

    return [
        'slug'    => $slug,
        'title'   => $meta['title'] ?? ucwords(str_replace('-', ' ', $slug)),
        'date'    => $meta['date'] ?? '',
        'author'  => $meta['author'] ?? 'KandaNews Team',
        'excerpt' => $meta['excerpt'] ?? '',
        'image'   => $meta['image'] ?? '',
        'tags'    => $meta['tags'] ?? '',
        'html'    => markdown_to_html($parsed['body']),
    ];
}

/**
 * Format a date string for display.
 */
function format_date(string $date): string {
    $ts = strtotime($date);
    return $ts ? date('F j, Y', $ts) : $date;
}

/**
 * Estimate reading time from markdown body.
 */
function reading_time(string $text): string {
    $words = str_word_count(strip_tags($text));
    $mins = max(1, (int) ceil($words / 220));
    return $mins . ' min read';
}

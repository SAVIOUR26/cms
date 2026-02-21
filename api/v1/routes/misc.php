<?php
/**
 * KandaNews API v1 — Miscellaneous Routes
 *
 * GET /misc/quote   — Quote of the day
 */

function route_misc(string $action, string $method): void {
    switch ("$method $action") {
        case 'GET quote':  misc_quote(); break;
        default: json_error('Not found', 404);
    }
}

/**
 * GET /misc/quote
 *
 * Returns a random quote of the day, consistent for the same calendar day.
 * Uses the day-of-year as a seed to deterministically pick a quote.
 */
function misc_quote(): void {
    $pdo = db();

    // Fetch all active quotes
    $stmt = $pdo->query("SELECT id, quote, author FROM quotes WHERE active = 1 ORDER BY id ASC");
    $quotes = $stmt->fetchAll();

    if (empty($quotes)) {
        json_error('No quotes available', 404);
    }

    // Use day of year as seed for consistent daily selection
    $dayOfYear = (int) date('z'); // 0-365
    $index = $dayOfYear % count($quotes);

    $selected = $quotes[$index];

    json_success([
        'quote'  => $selected['quote'],
        'author' => $selected['author'],
    ]);
}

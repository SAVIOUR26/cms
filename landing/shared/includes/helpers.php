<?php
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function assetUrl($path) {
    // Determine base path relative to current script
    $shared = dirname(__DIR__);
    return '/shared/assets/' . ltrim($path, '/');
}

function countryFlag($cc) {
    $cc = strtoupper($cc);
    if (strlen($cc) !== 2) return "\xF0\x9F\x8C\x8D";
    $base = 0x1F1E6;
    $result = '';
    for ($i = 0; $i < 2; $i++) {
        $char = ord($cc[$i]);
        if ($char < ord('A') || $char > ord('Z')) return "\xF0\x9F\x8C\x8D";
        $result .= mb_chr($base + ($char - ord('A')));
    }
    return $result;
}

function formatMoney($amount, $currency) {
    return $currency . ' ' . number_format($amount);
}

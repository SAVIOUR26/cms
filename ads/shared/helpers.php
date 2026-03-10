<?php
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $url): void { header('Location: ' . $url); exit; }
function flash(string $key, string $msg = null): ?string {
    if ($msg !== null) { $_SESSION['flash'][$key] = $msg; return null; }
    $v = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $v;
}
function json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
function format_ugx(int $amount): string {
    return 'UGX ' . number_format($amount);
}
function calc_price(string $format_key, int $days): array {
    $formats = AD_FORMATS;
    $base = $formats[$format_key]['price'] ?? 0;
    $unit_total = $base * $days;
    $discount = 0;
    if ($days >= 30)     $discount = DISCOUNT_MONTHLY;
    elseif ($days >= 7)  $discount = DISCOUNT_WEEKLY;
    $discounted = (int) round($unit_total * (1 - $discount));
    return [
        'unit_price'   => $base,
        'days'         => $days,
        'subtotal'     => $unit_total,
        'discount_pct' => $discount * 100,
        'total'        => $discounted,
    ];
}

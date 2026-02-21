<?php
/**
 * KandaNews API v1 — JSON Response Helpers
 */

function json_success($data = null, int $status = 200): void {
    http_response_code($status);
    $body = ['ok' => true];
    if ($data !== null) $body['data'] = $data;
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $message, int $status = 400, array $extra = []): void {
    http_response_code($status);
    $body = ['ok' => false, 'error' => $message];
    if ($extra) $body = array_merge($body, $extra);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_input(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

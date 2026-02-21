<?php
/**
 * KandaNews API v1 — Webhook Routes
 *
 * POST /webhooks/flutterwave  — Flutterwave payment callback
 * POST /webhooks/dpo          — DPO payment callback
 *
 * These are called by the payment providers, not by the app.
 */

function route_webhooks(string $action, string $method): void {
    if ($method !== 'POST') json_error('Method not allowed', 405);

    switch ($action) {
        case 'flutterwave': webhook_flutterwave(); break;
        case 'dpo':         webhook_dpo();          break;
        default: json_error('Not found', 404);
    }
}

/**
 * POST /webhooks/flutterwave
 *
 * Flutterwave sends a JSON payload with the transaction details.
 * We verify the hash, then verify the transaction via API.
 */
function webhook_flutterwave(): void {
    global $config;

    // Verify webhook hash
    $hash = $config['fw_webhook_hash'];
    if ($hash) {
        $signature = $_SERVER['HTTP_VERIF_HASH'] ?? '';
        if (!hash_equals($hash, $signature)) {
            error_log('[Webhook] Flutterwave hash mismatch');
            json_error('Unauthorized', 401);
        }
    }

    $payload = json_input();
    $event = $payload['event'] ?? '';
    $data  = $payload['data'] ?? [];

    if ($event !== 'charge.completed') {
        json_success(['message' => 'Event ignored']);
        return;
    }

    $txId   = $data['id'] ?? null;
    $txRef  = $data['tx_ref'] ?? '';
    $status = $data['status'] ?? '';
    $amount = (float) ($data['amount'] ?? 0);
    $currency = $data['currency'] ?? '';

    if ($status !== 'successful' || !$txRef) {
        json_success(['message' => 'Non-successful payment ignored']);
        return;
    }

    // Verify transaction via Flutterwave API
    $verified = flutterwave_verify((string) $txId, $amount, $currency);
    if (!$verified) {
        error_log("[Webhook] Flutterwave verification failed for tx $txId");
        json_error('Verification failed', 400);
    }

    // Activate the subscription
    activate_subscription($txRef, (string) $txId);
    json_success(['message' => 'Subscription activated']);
}

/**
 * POST /webhooks/dpo
 *
 * DPO sends an XML or form-encoded callback.
 */
function webhook_dpo(): void {
    $txRef = $_POST['CompanyRef'] ?? '';
    $txId  = $_POST['TransactionToken'] ?? $_POST['TransID'] ?? '';

    if (!$txRef || !$txId) {
        // Try raw body
        $raw = file_get_contents('php://input');
        error_log("[Webhook DPO] Raw: $raw");

        if (preg_match('/<CompanyRef>(.+?)<\/CompanyRef>/', $raw, $m)) $txRef = $m[1];
        if (preg_match('/<TransactionToken>(.+?)<\/TransactionToken>/', $raw, $m)) $txId = $m[1];
    }

    if (!$txRef) {
        json_error('Missing payment reference', 400);
    }

    // Verify with DPO
    $verified = dpo_verify($txId);
    if (!$verified) {
        error_log("[Webhook] DPO verification failed for tx $txId");
        json_error('Verification failed', 400);
    }

    activate_subscription($txRef, $txId);
    json_success(['message' => 'Subscription activated']);
}

/**
 * Activate a pending subscription by payment reference.
 */
function activate_subscription(string $paymentRef, string $txId): void {
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT id, user_id, plan, status FROM subscriptions
        WHERE payment_ref = ? AND status = 'pending'
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$paymentRef]);
    $sub = $stmt->fetch();

    if (!$sub) {
        error_log("[Webhook] No pending subscription for ref $paymentRef");
        return;
    }

    // Activate
    $pdo->prepare("
        UPDATE subscriptions
        SET status = 'active', payment_tx_id = ?, activated_at = NOW()
        WHERE id = ?
    ")->execute([$txId, $sub['id']]);

    // Log payment
    $pdo->prepare("
        INSERT INTO payment_log (subscription_id, user_id, payment_ref, payment_tx_id, event, created_at)
        VALUES (?, ?, ?, ?, 'activated', NOW())
    ")->execute([$sub['id'], $sub['user_id'], $paymentRef, $txId]);
}

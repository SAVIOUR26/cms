<?php
/**
 * KandaNews API v1 — OTP Generation & SMS Delivery
 *
 * Uses Africa's Talking SMS API.
 * Generates a 6-digit code, stores it hashed in the DB, and sends via SMS.
 */

/**
 * Generate a random numeric OTP.
 */
function otp_generate(): string {
    global $config;
    $len = $config['otp_length'];
    $min = (int) str_pad('1', $len, '0');
    $max = (int) str_pad('', $len, '9');
    return (string) random_int($min, $max);
}

/**
 * Store an OTP for a phone number.
 * Invalidates any existing OTPs for that phone.
 */
function otp_store(string $phone, string $code): void {
    global $config;
    $pdo = db();

    // Invalidate old OTPs
    $pdo->prepare("UPDATE otp_codes SET used = 1 WHERE phone = ? AND used = 0")
        ->execute([$phone]);

    // Insert new
    $stmt = $pdo->prepare("
        INSERT INTO otp_codes (phone, code_hash, expires_at)
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))
    ");
    $stmt->execute([
        $phone,
        password_hash($code, PASSWORD_DEFAULT),
        $config['otp_ttl'],
    ]);
}

/**
 * Verify an OTP code for a phone number.
 * Returns true and marks the OTP as used on success.
 */
function otp_verify(string $phone, string $code): bool {
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT id, code_hash FROM otp_codes
        WHERE phone = ? AND used = 0 AND expires_at > NOW()
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$phone]);
    $row = $stmt->fetch();

    if (!$row) return false;
    if (!password_verify($code, $row['code_hash'])) return false;

    // Mark used
    $pdo->prepare("UPDATE otp_codes SET used = 1 WHERE id = ?")->execute([$row['id']]);
    return true;
}

/**
 * Check rate limit — max N attempts per phone per hour.
 */
function otp_rate_ok(string $phone): bool {
    global $config;
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS cnt FROM otp_codes
        WHERE phone = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $stmt->execute([$phone]);
    $row = $stmt->fetch();

    return ($row['cnt'] ?? 0) < $config['otp_max_attempts'];
}

/**
 * Send OTP via Africa's Talking SMS API.
 */
function otp_send_sms(string $phone, string $code): bool {
    global $config;

    $apiKey   = $config['at_api_key'];
    $username = $config['at_username'];
    $senderId = $config['at_sender_id'];

    if (!$apiKey || !$username) {
        // Development: log instead of sending
        error_log("[OTP] $phone => $code (SMS not configured)");
        return true;
    }

    $message = "Your KandaNews verification code is: $code. Valid for 5 minutes. Do not share this code.";

    $url = ($username === 'sandbox')
        ? 'https://api.sandbox.africastalking.com/version1/messaging'
        : 'https://api.africastalking.com/version1/messaging';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'username' => $username,
            'to'       => $phone,
            'message'  => $message,
            'from'     => $senderId,
        ]),
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'apiKey: ' . $apiKey,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    }

    error_log("[OTP SMS ERROR] HTTP $httpCode — $response");
    return false;
}

/**
 * Normalize a phone number to E.164 format.
 * Handles common African formats.
 */
function normalize_phone(string $phone, string $country = 'ug'): string {
    // Strip spaces, dashes, parens
    $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

    // Country prefixes
    $prefixes = [
        'ug' => '256', 'ke' => '254', 'ng' => '234', 'za' => '27',
    ];
    $prefix = $prefixes[$country] ?? '256';

    // Already has +
    if (str_starts_with($phone, '+')) return $phone;

    // Starts with country code (e.g. 256...)
    if (str_starts_with($phone, $prefix)) return '+' . $phone;

    // Starts with 0 (local format)
    if (str_starts_with($phone, '0')) return '+' . $prefix . substr($phone, 1);

    // Just the number without prefix
    return '+' . $prefix . $phone;
}

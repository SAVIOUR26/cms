<?php
/**
 * KandaNews Africa — CMS Settings & Integrations
 *
 * View system config, check integration status, manage .env values.
 * Sensitive values are never echoed in full — only masked.
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$page_title   = 'Settings';
$page_section = 'system';
$section      = $_GET['section'] ?? 'general';

// Read .env for display (masked)
$env_file = dirname(__DIR__) . '/.env';
$env_vals = [];
if (is_file($env_file)) {
    foreach (file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $env_vals[trim($k)] = trim($v);
    }
}

function env_val(string $key, array $env): string {
    return $env[$key] ?? '';
}
function mask(string $val): string {
    if ($val === '') return '<span style="color:#dc2626;font-weight:700;">⚠ Not set</span>';
    $len = strlen($val);
    if ($len <= 8) return str_repeat('•', $len);
    return substr($val, 0, 4) . str_repeat('•', min($len - 6, 12)) . substr($val, -2);
}
function status_badge(string $val): string {
    if ($val === '') return '<span class="badge" style="background:#fef2f2;color:#dc2626;">NOT CONFIGURED</span>';
    return '<span class="badge badge-active">CONFIGURED</span>';
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page Header ────────────────────────────── -->
<div class="section-header">
    <div>
        <h1><i class="fas fa-cog" style="color:var(--orange);margin-right:8px;"></i>Settings</h1>
        <p>System configuration and integration status.</p>
    </div>
</div>

<!-- ── Tabs ───────────────────────────────────── -->
<div style="display:flex;gap:4px;margin-bottom:28px;border-bottom:2px solid #e5e7eb;padding-bottom:0;">
    <?php foreach (['general' => 'General', 'integrations' => 'Integrations', 'pricing' => 'Pricing', 'admins' => 'Admins'] as $s => $label): ?>
    <a href="<?php echo portal_url('settings.php?section=' . $s); ?>"
       style="padding:10px 20px;font-size:14px;font-weight:600;border-radius:8px 8px 0 0;text-decoration:none;color:<?php echo $section === $s ? 'var(--orange)' : '#888'; ?>;background:<?php echo $section === $s ? '#fff' : 'transparent'; ?>;border:<?php echo $section === $s ? '2px solid #e5e7eb' : '2px solid transparent'; ?>;border-bottom:<?php echo $section === $s ? '2px solid #fff' : '2px solid transparent'; ?>;margin-bottom:-2px;">
        <?php echo $label; ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if ($section === 'general'): ?>
<!-- ── General Settings ─────────────────────── -->
<div class="card">
    <div class="card-header"><h2><i class="fas fa-info-circle"></i> System Info</h2></div>
    <table class="dt">
        <tbody>
            <tr><td style="font-weight:600;width:200px;">App Environment</td><td><?php echo htmlspecialchars(env_val('APP_ENV', $env_vals) ?: 'production'); ?></td></tr>
            <tr><td style="font-weight:600;">App URL</td><td><?php echo htmlspecialchars(env_val('APP_URL', $env_vals) ?: 'https://kandanews.africa'); ?></td></tr>
            <tr><td style="font-weight:600;">API URL</td><td><?php echo htmlspecialchars(env_val('API_URL', $env_vals) ?: 'https://api.kandanews.africa'); ?></td></tr>
            <tr><td style="font-weight:600;">DB Host</td><td><?php echo htmlspecialchars(env_val('DB_HOST', $env_vals) ?: 'localhost'); ?></td></tr>
            <tr><td style="font-weight:600;">DB Name</td><td><?php echo htmlspecialchars(env_val('DB_NAME', $env_vals) ?: 'kandan_api'); ?></td></tr>
            <tr><td style="font-weight:600;">PHP Version</td><td><?php echo PHP_VERSION; ?></td></tr>
            <tr><td style="font-weight:600;">.env File</td><td><?php echo is_file($env_file) ? '<span class="badge badge-active">Found</span>' : '<span class="badge" style="background:#fef2f2;color:#dc2626;">Missing — copy .env.example</span>'; ?></td></tr>
        </tbody>
    </table>
</div>

<div class="card" style="background:linear-gradient(135deg,#1e2b42,#2a3f5f);color:#fff;">
    <h3 style="margin-bottom:12px;"><i class="fas fa-lightbulb" style="color:var(--orange);"></i> Tip: How to configure</h3>
    <p style="font-size:14px;opacity:.85;line-height:1.6;">
        All sensitive configuration is stored in the <code style="background:rgba(255,255,255,.15);padding:2px 6px;border-radius:4px;">.env</code> file at the root of the server.
        Copy <code style="background:rgba(255,255,255,.15);padding:2px 6px;border-radius:4px;">.env.example</code> to <code style="background:rgba(255,255,255,.15);padding:2px 6px;border-radius:4px;">.env</code>
        and fill in your real API keys via your hosting control panel or SSH.
        Never commit <code style="background:rgba(255,255,255,.15);padding:2px 6px;border-radius:4px;">.env</code> to git.
    </p>
</div>

<?php elseif ($section === 'integrations'): ?>
<!-- ── Integrations ──────────────────────────── -->

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;" class="two-col-grid">

    <!-- Africa's Talking -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-sms"></i> Africa's Talking (SMS / OTP)</h2>
            <?php echo status_badge(env_val('AT_API_KEY', $env_vals)); ?>
        </div>
        <table class="dt">
            <tbody>
                <tr><td style="font-weight:600;width:140px;">API Key</td><td><?php echo mask(env_val('AT_API_KEY', $env_vals)); ?></td></tr>
                <tr><td style="font-weight:600;">Username</td><td><?php echo htmlspecialchars(env_val('AT_USERNAME', $env_vals) ?: 'sandbox'); ?></td></tr>
                <tr><td style="font-weight:600;">Sender ID</td><td><?php echo htmlspecialchars(env_val('AT_SENDER_ID', $env_vals) ?: 'KandaNews'); ?></td></tr>
            </tbody>
        </table>
        <?php if (env_val('AT_API_KEY', $env_vals) === ''): ?>
        <div style="margin-top:14px;padding:12px 16px;background:#fef2f2;border-radius:8px;border:1px solid #fecaca;font-size:13px;color:#991b1b;">
            <i class="fas fa-exclamation-circle"></i>
            <strong>OTP SMS will NOT send.</strong>
            Set <code>AT_API_KEY</code> and <code>AT_USERNAME</code> in your <code>.env</code> file.
            Get credentials at <a href="https://africastalking.com" target="_blank" style="color:#991b1b;">africastalking.com</a>.
            Use the test phone <strong>+256772253804</strong> with OTP <strong>202026</strong> during development.
        </div>
        <?php else: ?>
        <div style="margin-top:14px;padding:12px 16px;background:#ecfdf5;border-radius:8px;border:1px solid #a7f3d0;font-size:13px;color:#065f46;">
            <i class="fas fa-check-circle"></i> SMS OTP is configured.
            <?php if (strtolower(env_val('AT_USERNAME', $env_vals)) === 'sandbox'): ?>
            <strong>Note:</strong> Username is still <em>sandbox</em> — switch to your live username before going live.
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Flutterwave -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-credit-card"></i> Flutterwave</h2>
            <?php echo status_badge(env_val('FW_SECRET_KEY', $env_vals)); ?>
        </div>
        <table class="dt">
            <tbody>
                <tr><td style="font-weight:600;width:140px;">Public Key</td><td><?php echo mask(env_val('FW_PUBLIC_KEY', $env_vals)); ?></td></tr>
                <tr><td style="font-weight:600;">Secret Key</td><td><?php echo mask(env_val('FW_SECRET_KEY', $env_vals)); ?></td></tr>
                <tr><td style="font-weight:600;">Webhook Hash</td><td><?php echo mask(env_val('FW_WEBHOOK_HASH', $env_vals)); ?></td></tr>
            </tbody>
        </table>
        <?php if (env_val('FW_SECRET_KEY', $env_vals) === ''): ?>
        <div style="margin-top:14px;padding:12px 16px;background:#fef2f2;border-radius:8px;border:1px solid #fecaca;font-size:13px;color:#991b1b;">
            <i class="fas fa-exclamation-circle"></i>
            <strong>Payment link will fail.</strong>
            Set <code>FW_PUBLIC_KEY</code>, <code>FW_SECRET_KEY</code>, and <code>FW_WEBHOOK_HASH</code> in <code>.env</code>.
            Get credentials at <a href="https://dashboard.flutterwave.com" target="_blank" style="color:#991b1b;">dashboard.flutterwave.com</a>.
        </div>
        <?php else: ?>
        <div style="margin-top:14px;padding:12px 16px;background:#ecfdf5;border-radius:8px;border:1px solid #a7f3d0;font-size:13px;color:#065f46;">
            <i class="fas fa-check-circle"></i> Flutterwave is configured. Webhook URL:
            <code style="background:#f0fdf4;padding:2px 6px;border-radius:4px;"><?php echo htmlspecialchars(env_val('API_URL', $env_vals)); ?>/webhooks/flutterwave</code>
        </div>
        <?php endif; ?>
    </div>

    <!-- DPO -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-university"></i> DPO (3G Direct Pay)</h2>
            <?php echo status_badge(env_val('DPO_COMPANY_TOKEN', $env_vals)); ?>
        </div>
        <table class="dt">
            <tbody>
                <tr><td style="font-weight:600;width:160px;">Company Token</td><td><?php echo mask(env_val('DPO_COMPANY_TOKEN', $env_vals)); ?></td></tr>
                <tr><td style="font-weight:600;">Service Type</td><td><?php echo htmlspecialchars(env_val('DPO_SERVICE_TYPE', $env_vals) ?: '— not set'); ?></td></tr>
            </tbody>
        </table>
        <?php if (env_val('DPO_COMPANY_TOKEN', $env_vals) === ''): ?>
        <div style="margin-top:14px;padding:12px 16px;background:#fef2f2;border-radius:8px;border:1px solid #fecaca;font-size:13px;color:#991b1b;">
            <i class="fas fa-exclamation-circle"></i>
            <strong>DPO payments will fail.</strong>
            Set <code>DPO_COMPANY_TOKEN</code> and <code>DPO_SERVICE_TYPE</code> in <code>.env</code>.
            Get credentials at <a href="https://secure.3gdirectpay.com" target="_blank" style="color:#991b1b;">3gdirectpay.com</a>.
        </div>
        <?php else: ?>
        <div style="margin-top:14px;padding:12px 16px;background:#ecfdf5;border-radius:8px;border:1px solid #a7f3d0;font-size:13px;color:#065f46;">
            <i class="fas fa-check-circle"></i> DPO is configured.
        </div>
        <?php endif; ?>
    </div>

    <!-- JWT -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-key"></i> JWT Auth</h2>
            <?php $jwtSet = env_val('JWT_SECRET', $env_vals) !== '' && !str_starts_with(env_val('JWT_SECRET', $env_vals), 'CHANGE-ME'); echo status_badge($jwtSet ? 'set' : ''); ?>
        </div>
        <table class="dt">
            <tbody>
                <tr><td style="font-weight:600;width:160px;">JWT Secret</td><td><?php echo mask(env_val('JWT_SECRET', $env_vals)); ?></td></tr>
                <tr><td style="font-weight:600;">Token TTL</td><td><?php echo htmlspecialchars(env_val('JWT_TTL', $env_vals) ?: '2592000'); ?> seconds (<?php echo round((int)(env_val('JWT_TTL', $env_vals) ?: 2592000) / 86400); ?> days)</td></tr>
            </tbody>
        </table>
        <?php if (!$jwtSet): ?>
        <div style="margin-top:14px;padding:12px 16px;background:#fffbeb;border-radius:8px;border:1px solid #fde68a;font-size:13px;color:#92400e;">
            <i class="fas fa-exclamation-triangle"></i>
            JWT_SECRET is still the default. Set a strong random secret in <code>.env</code>.
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- .env example -->
<div class="card" style="background:#111827;color:#e2e8f0;">
    <h3 style="color:#f05a1a;margin-bottom:16px;font-size:15px;"><i class="fas fa-file-code"></i> .env Template</h3>
    <pre style="font-size:12px;line-height:1.8;overflow-x:auto;white-space:pre-wrap;font-family:'Courier New',monospace;color:#a5f3fc;"># ── KandaNews .env ─────────────────────────────
APP_ENV=production
APP_URL=https://kandanews.africa
API_URL=https://api.kandanews.africa
APP_DEBUG=false

# Database
DB_HOST=localhost
DB_NAME=kandan_api
DB_USER=kandan_api
DB_PASS=YOUR_DB_PASSWORD

# JWT
JWT_SECRET=GENERATE_WITH_openssl_rand_-hex_64

# Africa's Talking (SMS OTP)
AT_API_KEY=your_AT_api_key_here
AT_USERNAME=your_AT_username       # 'sandbox' for testing
AT_SENDER_ID=KandaNews

# Flutterwave (payments)
FW_PUBLIC_KEY=FLWPUBK_TEST-xxxx
FW_SECRET_KEY=FLWSECK_TEST-xxxx
FW_WEBHOOK_HASH=your_webhook_hash

# DPO (alternative payments)
DPO_COMPANY_TOKEN=your_dpo_token
DPO_SERVICE_TYPE=your_service_type_id

# Editions
EDITIONS_PATH=/home/user/cms/output
EDITIONS_URL=https://ug.kandanews.africa/editions
</pre>
</div>

<?php elseif ($section === 'pricing'): ?>
<!-- ── Pricing ────────────────────────────────── -->
<div class="card">
    <div class="card-header"><h2><i class="fas fa-tags"></i> Subscription Pricing</h2></div>
    <p style="font-size:14px;color:#888;margin-bottom:20px;">
        These prices are configured in <code>api/v1/config/app.php</code> under the <code>pricing</code> key.
        To change, edit that file and redeploy.
    </p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;">
        <?php
        $pricing = [
            'ug' => ['flag'=>'🇺🇬','name'=>'Uganda',       'currency'=>'UGX','daily'=>500,  'weekly'=>2500, 'monthly'=>7500],
            'ke' => ['flag'=>'🇰🇪','name'=>'Kenya',        'currency'=>'KES','daily'=>20,   'weekly'=>100,  'monthly'=>300],
            'ng' => ['flag'=>'🇳🇬','name'=>'Nigeria',      'currency'=>'NGN','daily'=>100,  'weekly'=>500,  'monthly'=>1500],
            'za' => ['flag'=>'🇿🇦','name'=>'South Africa', 'currency'=>'ZAR','daily'=>5,    'weekly'=>25,   'monthly'=>70],
        ];
        foreach ($pricing as $cc => $p):
        ?>
        <div style="border:1.5px solid #e5e7eb;border-radius:12px;padding:20px;">
            <div style="font-size:24px;margin-bottom:8px;"><?php echo $p['flag']; ?></div>
            <div style="font-size:15px;font-weight:700;color:var(--navy);margin-bottom:12px;"><?php echo $p['name']; ?></div>
            <table style="width:100%;font-size:13px;">
                <tr><td style="color:#888;padding:4px 0;">Daily</td><td style="font-weight:700;text-align:right;"><?php echo number_format($p['daily']); ?> <?php echo $p['currency']; ?></td></tr>
                <tr><td style="color:#888;padding:4px 0;">Weekly</td><td style="font-weight:700;text-align:right;"><?php echo number_format($p['weekly']); ?> <?php echo $p['currency']; ?></td></tr>
                <tr style="border-top:1px solid #f3f4f6;"><td style="color:#888;padding:8px 0 4px;">Monthly</td><td style="font-weight:800;font-size:15px;text-align:right;color:var(--orange);"><?php echo number_format($p['monthly']); ?> <?php echo $p['currency']; ?></td></tr>
            </table>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php elseif ($section === 'admins'): ?>
<!-- ── Admin Users ────────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-user-shield"></i> CMS Administrators</h2>
    </div>
    <?php
    $admins = [];
    try {
        $admins = $db->query("SELECT id, username, full_name, role, status, last_login, created_at FROM cms_admins ORDER BY created_at ASC")->fetchAll();
    } catch (PDOException $e) {}
    ?>
    <?php if (empty($admins)): ?>
    <div class="empty-state"><i class="fas fa-user-slash"></i><h3>No admins found</h3><p>Run the seed script to create the first admin account.</p></div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr><th>Name</th><th>Username</th><th>Role</th><th>Last Login</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><strong style="color:var(--navy);"><?php echo htmlspecialchars($admin['full_name'] ?? ''); ?></strong></td>
                    <td style="color:#555;"><?php echo htmlspecialchars($admin['username']); ?></td>
                    <td><span class="badge badge-special"><?php echo ucfirst($admin['role']); ?></span></td>
                    <td style="font-size:12px;color:#888;"><?php echo $admin['last_login'] ? date('M j, Y H:i', strtotime($admin['last_login'])) : 'Never'; ?></td>
                    <td><span class="badge badge-<?php echo $admin['status'] === 'active' ? 'active' : 'archived'; ?>"><?php echo $admin['status']; ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<style>
@media (max-width: 800px) { .two-col-grid { grid-template-columns: 1fr !important; } }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

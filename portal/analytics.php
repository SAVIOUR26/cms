<?php
/**
 * KandaNews Africa — Analytics
 *
 * Overview of content performance, subscriber growth, and revenue metrics.
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$page_title   = 'Analytics';
$page_section = 'overview';
$db           = portal_db();

// ── Edition Stats ──────────────────────────────
$editions = [
    'total'     => 0,
    'published' => 0,
    'draft'     => 0,
    'archived'  => 0,
    'free'      => 0,
    'paid'      => 0,
];
$by_type    = [];
$by_country = [];
$by_month   = [];

// ── Subscriber / Revenue Stats ─────────────────
$subs = [
    'users'       => 0,
    'active'      => 0,
    'expired'     => 0,
    'revenue_ugx' => 0,
    'revenue_kes' => 0,
];
$subs_by_country = [];
$subs_by_plan    = [];
$revenue_by_month = [];

try {
    // Editions
    $editions['total']     = (int) $db->query("SELECT COUNT(*) FROM editions")->fetchColumn();
    $editions['published'] = (int) $db->query("SELECT COUNT(*) FROM editions WHERE status='published'")->fetchColumn();
    $editions['draft']     = (int) $db->query("SELECT COUNT(*) FROM editions WHERE status='draft'")->fetchColumn();
    $editions['archived']  = (int) $db->query("SELECT COUNT(*) FROM editions WHERE status='archived'")->fetchColumn();
    $editions['free']      = (int) $db->query("SELECT COUNT(*) FROM editions WHERE is_free=1 AND status='published'")->fetchColumn();
    $editions['paid']      = (int) $db->query("SELECT COUNT(*) FROM editions WHERE is_free=0 AND status='published'")->fetchColumn();

    // Editions by type
    $rows = $db->query("
        SELECT edition_type, COUNT(*) AS cnt
        FROM editions
        GROUP BY edition_type
        ORDER BY cnt DESC
    ")->fetchAll();
    foreach ($rows as $r) {
        $by_type[$r['edition_type'] ?: 'daily'] = (int) $r['cnt'];
    }

    // Editions by country
    $rows = $db->query("
        SELECT country, COUNT(*) AS cnt
        FROM editions
        WHERE status = 'published'
        GROUP BY country
        ORDER BY cnt DESC
    ")->fetchAll();
    foreach ($rows as $r) {
        $by_country[$r['country'] ?: 'UG'] = (int) $r['cnt'];
    }

    // Editions published per month (last 12 months)
    $rows = $db->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS cnt
        FROM editions
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY ym
        ORDER BY ym ASC
    ")->fetchAll();
    foreach ($rows as $r) {
        $by_month[$r['ym']] = (int) $r['cnt'];
    }

} catch (PDOException $e) { /* editions table may not exist */ }

try {
    // Subscribers
    $subs['users']       = (int) $db->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
    $subs['active']      = (int) $db->query("SELECT COUNT(*) FROM subscriptions WHERE status='active' AND expires_at > NOW()")->fetchColumn();
    $subs['expired']     = (int) $db->query("SELECT COUNT(*) FROM subscriptions WHERE status='active' AND expires_at <= NOW()")->fetchColumn();
    $subs['revenue_ugx'] = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM subscriptions WHERE status='active' AND currency='UGX'")->fetchColumn();
    $subs['revenue_kes'] = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM subscriptions WHERE status='active' AND currency='KES'")->fetchColumn();

    // Subscribers by country
    $rows = $db->query("
        SELECT country, COUNT(*) AS cnt
        FROM users
        WHERE status = 'active'
        GROUP BY country
        ORDER BY cnt DESC
    ")->fetchAll();
    foreach ($rows as $r) {
        $subs_by_country[$r['country'] ?: '??'] = (int) $r['cnt'];
    }

    // Subscriptions by plan
    $rows = $db->query("
        SELECT plan, COUNT(*) AS cnt
        FROM subscriptions
        WHERE status = 'active' AND expires_at > NOW()
        GROUP BY plan
        ORDER BY cnt DESC
    ")->fetchAll();
    foreach ($rows as $r) {
        $subs_by_plan[$r['plan'] ?: 'unknown'] = (int) $r['cnt'];
    }

    // Revenue per month (last 12 months, UGX)
    $rows = $db->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COALESCE(SUM(amount),0) AS total
        FROM subscriptions
        WHERE currency = 'UGX'
          AND status = 'active'
          AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY ym
        ORDER BY ym ASC
    ")->fetchAll();
    foreach ($rows as $r) {
        $revenue_by_month[$r['ym']] = (float) $r['total'];
    }

} catch (PDOException $e) { /* users/subscriptions tables may not exist yet */ }

$countries  = portal_countries();
$type_labels = [
    'daily'           => 'Daily',
    'special'         => 'Special',
    'rate_card'       => 'Rate Card',
    'university'      => 'University',
    'corporate'       => 'Corporate',
    'entrepreneurship'=> 'Entrepreneurship',
];

require_once __DIR__ . '/includes/header.php';

// ── Helpers ────────────────────────────────────
function pct(int $part, int $total): string {
    if ($total === 0) return '0%';
    return round($part / $total * 100) . '%';
}
?>

<div class="section-header">
    <div>
        <h1><i class="fas fa-chart-line" style="color:var(--orange);margin-right:8px;"></i>Analytics</h1>
        <p>Content performance and audience metrics — updated in real time</p>
    </div>
    <a href="<?php echo portal_url('subscribers.php?tab=revenue'); ?>" class="btn btn-outline">
        <i class="fas fa-coins"></i> Revenue Detail
    </a>
</div>

<!-- ── Edition Overview ─────────────────────── -->
<div class="stat-grid">
    <a href="<?php echo portal_url('editions.php'); ?>" class="stat-card">
        <div class="stat-icon si-navy"><i class="fas fa-newspaper"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($editions['total']); ?></div>
            <div class="stat-label">Total Editions</div>
        </div>
    </a>
    <a href="<?php echo portal_url('editions.php?status=published'); ?>" class="stat-card">
        <div class="stat-icon si-green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($editions['published']); ?></div>
            <div class="stat-label">Published</div>
            <div class="stat-trend up"><?php echo pct($editions['published'], $editions['total']); ?> of total</div>
        </div>
    </a>
    <a href="<?php echo portal_url('editions.php?status=draft'); ?>" class="stat-card">
        <div class="stat-icon si-orange"><i class="fas fa-pen"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($editions['draft']); ?></div>
            <div class="stat-label">Drafts</div>
        </div>
    </a>
    <a href="<?php echo portal_url('subscribers.php'); ?>" class="stat-card">
        <div class="stat-icon si-blue"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($subs['users']); ?></div>
            <div class="stat-label">Registered Users</div>
        </div>
    </a>
    <a href="<?php echo portal_url('subscribers.php?tab=active'); ?>" class="stat-card">
        <div class="stat-icon si-purple"><i class="fas fa-id-badge"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($subs['active']); ?></div>
            <div class="stat-label">Active Subscribers</div>
        </div>
    </a>
    <a href="<?php echo portal_url('subscribers.php?tab=revenue'); ?>" class="stat-card">
        <div class="stat-icon si-pink"><i class="fas fa-coins"></i></div>
        <div class="stat-info">
            <div class="stat-value">
                <?php if ($subs['revenue_ugx'] > 0): ?>
                    UGX <?php echo number_format($subs['revenue_ugx'] / 1000, 0); ?>K
                <?php elseif ($subs['revenue_kes'] > 0): ?>
                    KES <?php echo number_format($subs['revenue_kes'] / 1000, 0); ?>K
                <?php else: ?>
                    —
                <?php endif; ?>
            </div>
            <div class="stat-label">Subscription Revenue</div>
        </div>
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">

    <!-- Editions by Type -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-tag"></i> Editions by Type</h2>
        </div>
        <?php if (empty($by_type)): ?>
            <p style="color:#aaa;font-size:14px;">No edition data yet.</p>
        <?php else: ?>
            <?php foreach ($by_type as $type => $cnt): ?>
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                    <span style="font-size:13px;font-weight:600;color:var(--navy);">
                        <?php echo htmlspecialchars($type_labels[$type] ?? ucfirst($type)); ?>
                    </span>
                    <span style="font-size:13px;color:#888;"><?php echo number_format($cnt); ?></span>
                </div>
                <div style="background:#f0f2f5;border-radius:6px;height:8px;overflow:hidden;">
                    <div style="width:<?php echo pct($cnt, $editions['total']); ?>;height:100%;background:var(--orange);border-radius:6px;transition:width .4s;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Editions by Country -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-globe-africa"></i> Published by Country</h2>
        </div>
        <?php if (empty($by_country)): ?>
            <p style="color:#aaa;font-size:14px;">No published editions yet.</p>
        <?php else: ?>
            <?php foreach ($by_country as $code => $cnt): ?>
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                    <span style="font-size:13px;font-weight:600;color:var(--navy);">
                        <?php echo htmlspecialchars($countries[strtoupper($code)] ?? $code); ?>
                    </span>
                    <span style="font-size:13px;color:#888;"><?php echo number_format($cnt); ?></span>
                </div>
                <div style="background:#f0f2f5;border-radius:6px;height:8px;overflow:hidden;">
                    <div style="width:<?php echo pct($cnt, $editions['published']); ?>;height:100%;background:var(--navy);border-radius:6px;transition:width .4s;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">

    <!-- Subscribers by Country -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-map-marker-alt"></i> Users by Country</h2>
        </div>
        <?php if (empty($subs_by_country)): ?>
            <p style="color:#aaa;font-size:14px;">No user data yet.</p>
        <?php else: ?>
            <?php foreach ($subs_by_country as $code => $cnt): ?>
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                    <span style="font-size:13px;font-weight:600;color:var(--navy);">
                        <?php echo htmlspecialchars($countries[strtoupper($code)] ?? $code); ?>
                    </span>
                    <span style="font-size:13px;color:#888;"><?php echo number_format($cnt); ?></span>
                </div>
                <div style="background:#f0f2f5;border-radius:6px;height:8px;overflow:hidden;">
                    <div style="width:<?php echo pct($cnt, $subs['users']); ?>;height:100%;background:var(--purple);border-radius:6px;transition:width .4s;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Subscriptions by Plan -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-layer-group"></i> Active Subscriptions by Plan</h2>
        </div>
        <?php if (empty($subs_by_plan)): ?>
            <p style="color:#aaa;font-size:14px;">No active subscriptions yet.</p>
        <?php else: ?>
            <?php foreach ($subs_by_plan as $plan => $cnt): ?>
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                    <span style="font-size:13px;font-weight:600;color:var(--navy);">
                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $plan))); ?>
                    </span>
                    <span style="font-size:13px;color:#888;"><?php echo number_format($cnt); ?></span>
                </div>
                <div style="background:#f0f2f5;border-radius:6px;height:8px;overflow:hidden;">
                    <div style="width:<?php echo pct($cnt, max($subs['active'], 1)); ?>;height:100%;background:var(--green);border-radius:6px;transition:width .4s;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<!-- ── Editions Published Per Month ─────────── -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-calendar-alt"></i> Editions Published — Last 12 Months</h2>
    </div>
    <?php if (empty($by_month)): ?>
        <p style="color:#aaa;font-size:14px;">No edition activity in the last 12 months.</p>
    <?php else: ?>
        <?php
        $maxVal = max(array_values($by_month));
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $months[] = date('Y-m', strtotime("-{$i} months"));
        }
        ?>
        <div style="display:flex;align-items:flex-end;gap:6px;height:120px;padding-bottom:24px;position:relative;border-bottom:2px solid #f0f2f5;">
            <?php foreach ($months as $ym):
                $cnt    = $by_month[$ym] ?? 0;
                $height = $maxVal > 0 ? round($cnt / $maxVal * 100) : 0;
                $label  = date('M', strtotime($ym . '-01'));
            ?>
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;position:relative;">
                <span style="font-size:10px;color:#888;position:absolute;top:-18px;"><?php echo $cnt > 0 ? $cnt : ''; ?></span>
                <div style="width:100%;background:var(--orange);border-radius:4px 4px 0 0;height:<?php echo $height; ?>%;min-height:<?php echo $cnt > 0 ? '4px' : '0'; ?>;transition:height .4s;" title="<?php echo $ym . ': ' . $cnt; ?> editions"></div>
                <span style="font-size:10px;color:#aaa;position:absolute;bottom:-20px;"><?php echo $label; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($subs['revenue_ugx'] > 0 || $subs['revenue_kes'] > 0): ?>
<!-- ── Revenue Overview ──────────────────────── -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-coins"></i> Revenue Overview</h2>
        <a href="<?php echo portal_url('subscribers.php?tab=revenue'); ?>" class="btn btn-outline btn-sm">
            Full Report <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;">
        <?php if ($subs['revenue_ugx'] > 0): ?>
        <div style="background:linear-gradient(135deg,var(--navy),var(--navy-xl));border-radius:12px;padding:20px;color:#fff;">
            <div style="font-size:12px;opacity:.7;margin-bottom:6px;text-transform:uppercase;letter-spacing:1px;">Uganda (UGX)</div>
            <div style="font-size:28px;font-weight:800;">UGX <?php echo number_format($subs['revenue_ugx']); ?></div>
            <div style="font-size:12px;opacity:.6;margin-top:4px;">Active subscriptions</div>
        </div>
        <?php endif; ?>
        <?php if ($subs['revenue_kes'] > 0): ?>
        <div style="background:linear-gradient(135deg,#059669,#10b981);border-radius:12px;padding:20px;color:#fff;">
            <div style="font-size:12px;opacity:.7;margin-bottom:6px;text-transform:uppercase;letter-spacing:1px;">Kenya (KES)</div>
            <div style="font-size:28px;font-weight:800;">KES <?php echo number_format($subs['revenue_kes']); ?></div>
            <div style="font-size:12px;opacity:.6;margin-top:4px;">Active subscriptions</div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── Content health summary ────────────────── -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-heartbeat"></i> Content Health</h2>
    </div>
    <div style="display:flex;gap:32px;flex-wrap:wrap;">
        <div>
            <div style="font-size:13px;color:#888;margin-bottom:4px;">Free Access Editions</div>
            <div style="font-size:22px;font-weight:800;color:var(--green);"><?php echo number_format($editions['free']); ?></div>
        </div>
        <div>
            <div style="font-size:13px;color:#888;margin-bottom:4px;">Subscriber-Only Editions</div>
            <div style="font-size:22px;font-weight:800;color:var(--orange);"><?php echo number_format($editions['paid']); ?></div>
        </div>
        <div>
            <div style="font-size:13px;color:#888;margin-bottom:4px;">Archived Editions</div>
            <div style="font-size:22px;font-weight:800;color:#9ca3af;"><?php echo number_format($editions['archived']); ?></div>
        </div>
        <div>
            <div style="font-size:13px;color:#888;margin-bottom:4px;">Expired Subscribers</div>
            <div style="font-size:22px;font-weight:800;color:#ef4444;"><?php echo number_format($subs['expired']); ?></div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

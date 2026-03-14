<?php
/**
 * KandaNews Africa — Referral Programme
 *
 * Overview of invite activity: top referrers, recent sign-ups via invite,
 * and total reach across all markets.
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$page_title   = 'Referrals';
$page_section = 'audience';
$db           = portal_db();

$country = strtolower($_GET['country'] ?? 'all');
$search  = trim($_GET['q'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 25;
$offset  = ($page - 1) * $perPage;

// ── Stats ──────────────────────────────────────
$stats = [
    'total_referrals'    => 0,
    'unique_referrers'   => 0,
    'last_30_days'       => 0,
    'codes_generated'    => 0,
];

try {
    $stats['total_referrals']  = (int) $db->query("SELECT COUNT(*) FROM referrals")->fetchColumn();
    $stats['unique_referrers'] = (int) $db->query("SELECT COUNT(DISTINCT referrer_id) FROM referrals")->fetchColumn();
    $stats['last_30_days']     = (int) $db->query("SELECT COUNT(*) FROM referrals WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
    $stats['codes_generated']  = (int) $db->query("SELECT COUNT(*) FROM referral_codes")->fetchColumn();
} catch (PDOException $e) {}

// ── Top Referrers ──────────────────────────────
$topReferrers = [];
try {
    $topReferrers = $db->query("
        SELECT u.id, u.full_name, u.phone, u.country,
               COUNT(r.id) AS referral_count,
               MAX(r.created_at) AS last_referral_at
        FROM referrals r
        JOIN users u ON u.id = r.referrer_id
        GROUP BY r.referrer_id
        ORDER BY referral_count DESC
        LIMIT 10
    ")->fetchAll();
} catch (PDOException $e) {}

// ── Recent Sign-ups via Invite ─────────────────
$recentRows  = [];
$recentTotal = 0;

try {
    $where  = '1=1';
    $params = [];

    if ($country !== 'all') {
        $where .= ' AND u.country = ?';
        $params[] = $country;
    }
    if ($search) {
        $where .= ' AND (u.full_name LIKE ? OR u.phone LIKE ? OR referrer.full_name LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $cntStmt = $db->prepare("
        SELECT COUNT(*)
        FROM referrals r
        JOIN users u        ON u.id = r.referred_user_id
        JOIN users referrer ON referrer.id = r.referrer_id
        WHERE $where
    ");
    $cntStmt->execute($params);
    $recentTotal = (int) $cntStmt->fetchColumn();

    $stmt = $db->prepare("
        SELECT
            u.id            AS user_id,
            u.full_name     AS user_name,
            u.phone         AS user_phone,
            u.country       AS user_country,
            u.created_at    AS joined_at,
            referrer.id         AS referrer_id,
            referrer.full_name  AS referrer_name,
            referrer.phone      AS referrer_phone,
            rc.code             AS referral_code
        FROM referrals r
        JOIN users u            ON u.id = r.referred_user_id
        JOIN users referrer     ON referrer.id = r.referrer_id
        LEFT JOIN referral_codes rc ON rc.user_id = r.referrer_id
        WHERE $where
        ORDER BY r.created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $recentRows = $stmt->fetchAll();

} catch (PDOException $e) {
    $recentRows  = [];
    $recentTotal = 0;
}

$totalPages = max(1, ceil($recentTotal / $perPage));

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page Header ───────────────────────────── -->
<div class="section-header">
    <div>
        <h1><i class="fas fa-share-alt" style="color:var(--orange);margin-right:8px;"></i>Referrals</h1>
        <p>Track invite activity — who's referring, who's joining, and how fast the app is growing via word of mouth.</p>
    </div>
</div>

<!-- ── Stat Cards ────────────────────────────── -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon si-orange"><i class="fas fa-user-plus"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['total_referrals']); ?></div>
            <div class="stat-label">Total Sign-ups via Invite</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-navy"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['unique_referrers']); ?></div>
            <div class="stat-label">Active Referrers</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-green"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['last_30_days']); ?></div>
            <div class="stat-label">Joined (Last 30 Days)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-purple"><i class="fas fa-link"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['codes_generated']); ?></div>
            <div class="stat-label">Invite Codes Generated</div>
        </div>
    </div>
</div>

<!-- ── Top Referrers ─────────────────────────── -->
<?php if (!empty($topReferrers)): ?>
<div class="card" style="margin-bottom:28px;">
    <div class="card-header">
        <h2><i class="fas fa-trophy"></i> Top Referrers</h2>
        <span style="font-size:13px;color:#888;">Top 10 all time</span>
    </div>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Country</th>
                    <th>Referrals</th>
                    <th>Last Referral</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topReferrers as $i => $row): ?>
                <tr>
                    <td style="font-weight:700;color:<?php echo $i === 0 ? '#d97706' : ($i === 1 ? '#6b7280' : ($i === 2 ? '#b45309' : '#aaa')); ?>;">
                        <?php echo $i + 1; ?>
                        <?php if ($i < 3): ?><i class="fas fa-medal" style="margin-left:4px;font-size:11px;"></i><?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0;">
                                <?php echo mb_strtoupper(mb_substr($row['full_name'] ?? 'U', 0, 1)); ?>
                            </div>
                            <div>
                                <strong style="font-size:13px;color:var(--navy);"><?php echo htmlspecialchars($row['full_name'] ?? 'Unknown'); ?></strong>
                                <div style="font-size:11px;color:#9ca3af;"><?php echo htmlspecialchars($row['phone']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:12px;font-weight:700;"><?php echo strtoupper($row['country']); ?></td>
                    <td>
                        <span style="font-size:18px;font-weight:800;color:var(--orange);"><?php echo number_format($row['referral_count']); ?></span>
                        <span style="font-size:11px;color:#888;margin-left:4px;">invite<?php echo $row['referral_count'] != 1 ? 's' : ''; ?></span>
                    </td>
                    <td style="font-size:12px;color:#888;"><?php echo date('M j, Y', strtotime($row['last_referral_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ── Recent Sign-ups via Invite ─────────────── -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-user-plus"></i> Sign-ups via Invite</h2>
        <span style="font-size:13px;color:#888;"><?php echo number_format($recentTotal); ?> total</span>
    </div>

    <!-- Filters -->
    <form method="GET" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
               class="form-control" placeholder="Search user or referrer name/phone..."
               style="flex:1;min-width:200px;max-width:360px;">
        <select name="country" class="form-control" style="width:180px;">
            <option value="all" <?php echo $country === 'all' ? 'selected' : ''; ?>>All Countries</option>
            <option value="ug" <?php echo $country === 'ug' ? 'selected' : ''; ?>>🇺🇬 Uganda</option>
            <option value="ke" <?php echo $country === 'ke' ? 'selected' : ''; ?>>🇰🇪 Kenya</option>
            <option value="ng" <?php echo $country === 'ng' ? 'selected' : ''; ?>>🇳🇬 Nigeria</option>
            <option value="za" <?php echo $country === 'za' ? 'selected' : ''; ?>>🇿🇦 South Africa</option>
        </select>
        <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Search</button>
        <?php if ($search || $country !== 'all'): ?>
        <a href="<?php echo portal_url('referrals.php'); ?>" class="btn btn-outline">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (empty($recentRows)): ?>
    <div class="empty-state">
        <i class="fas fa-share-alt"></i>
        <h3>No referrals yet</h3>
        <p>Sign-ups via invite links will appear here once users start sharing the app.</p>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr>
                    <th>New User</th>
                    <th>Country</th>
                    <th>Referred By</th>
                    <th>Code Used</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentRows as $row): ?>
                <tr>
                    <td>
                        <strong style="font-size:13px;color:var(--navy);"><?php echo htmlspecialchars($row['user_name'] ?? 'Unknown'); ?></strong>
                        <div style="font-size:11px;color:#9ca3af;"><?php echo htmlspecialchars($row['user_phone']); ?></div>
                    </td>
                    <td style="font-size:12px;font-weight:700;"><?php echo strtoupper($row['user_country']); ?></td>
                    <td>
                        <strong style="font-size:13px;color:var(--navy);"><?php echo htmlspecialchars($row['referrer_name'] ?? 'Unknown'); ?></strong>
                        <div style="font-size:11px;color:#9ca3af;"><?php echo htmlspecialchars($row['referrer_phone']); ?></div>
                    </td>
                    <td>
                        <?php if ($row['referral_code']): ?>
                        <code style="background:#f3f4f6;padding:3px 8px;border-radius:6px;font-size:12px;font-weight:700;letter-spacing:1px;color:var(--navy);">
                            <?php echo htmlspecialchars($row['referral_code']); ?>
                        </code>
                        <?php else: ?>
                        <span style="color:#aaa;font-size:12px;">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:#888;"><?php echo date('M j, Y', strtotime($row['joined_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="<?php echo portal_url('referrals.php?page=' . ($page-1) . ($search ? '&q=' . urlencode($search) : '') . ($country !== 'all' ? '&country=' . $country : '')); ?>">
            <i class="fas fa-chevron-left"></i> Prev
        </a>
        <?php else: ?>
        <span class="disabled"><i class="fas fa-chevron-left"></i> Prev</span>
        <?php endif; ?>

        <span class="current"><?php echo $page; ?> / <?php echo $totalPages; ?></span>

        <?php if ($page < $totalPages): ?>
        <a href="<?php echo portal_url('referrals.php?page=' . ($page+1) . ($search ? '&q=' . urlencode($search) : '') . ($country !== 'all' ? '&country=' . $country : '')); ?>">
            Next <i class="fas fa-chevron-right"></i>
        </a>
        <?php else: ?>
        <span class="disabled">Next <i class="fas fa-chevron-right"></i></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

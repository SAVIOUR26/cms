<?php
/**
 * KandaNews Africa — Subscribers & Revenue
 *
 * Overview of all registered users and their subscription status.
 * Tabs: All Users | Active Subscriptions | Revenue
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$page_title   = 'Subscribers';
$page_section = 'audience';
$db           = portal_db();

$tab     = $_GET['tab']    ?? 'users';
$country = strtolower($_GET['country'] ?? 'all');
$search  = trim($_GET['q'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 25;
$offset  = ($page - 1) * $perPage;

// ── Stats ─────────────────────────────────────
$stats = ['users' => 0, 'active' => 0, 'expired' => 0, 'revenue_ugx' => 0, 'revenue_kes' => 0];
try {
    $stats['users']       = (int) $db->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
    $stats['active']      = (int) $db->query("SELECT COUNT(*) FROM subscriptions WHERE status='active' AND expires_at > NOW()")->fetchColumn();
    $stats['expired']     = (int) $db->query("SELECT COUNT(*) FROM subscriptions WHERE status='active' AND expires_at <= NOW()")->fetchColumn();
    $stats['revenue_ugx'] = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM subscriptions WHERE status='active' AND currency='UGX'")->fetchColumn();
    $stats['revenue_kes'] = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM subscriptions WHERE status='active' AND currency='KES'")->fetchColumn();
} catch (PDOException $e) {}

// ── Query ─────────────────────────────────────
$rows  = [];
$total = 0;

try {
    if ($tab === 'active') {
        // Active subscriptions
        $where  = "s.status = 'active' AND s.expires_at > NOW()";
        $params = [];
        if ($country !== 'all') { $where .= ' AND u.country = ?'; $params[] = $country; }
        if ($search)            { $where .= ' AND (u.full_name LIKE ? OR u.phone LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

        $total = (int) $db->query("SELECT COUNT(*) FROM subscriptions s JOIN users u ON u.id=s.user_id WHERE $where")->fetchColumn();
        $stmt  = $db->prepare("
            SELECT u.id, u.full_name, u.phone, u.country, s.plan, s.payment_provider,
                   s.amount, s.currency, s.starts_at, s.expires_at, s.status
            FROM subscriptions s
            JOIN users u ON u.id = s.user_id
            WHERE $where
            ORDER BY s.expires_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

    } elseif ($tab === 'revenue') {
        // Revenue breakdown
        $rows = $db->query("
            SELECT currency, plan,
                   COUNT(*) as count,
                   SUM(amount) as total,
                   AVG(amount) as avg_amount
            FROM subscriptions
            WHERE status = 'active'
            GROUP BY currency, plan
            ORDER BY currency, plan
        ")->fetchAll();

    } else {
        // All users
        $where  = 'u.status = ?';
        $params = ['active'];
        if ($country !== 'all') { $where .= ' AND u.country = ?'; $params[] = $country; }
        if ($search)            { $where .= ' AND (u.full_name LIKE ? OR u.phone LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

        $cntStmt = $db->prepare("SELECT COUNT(*) FROM users u WHERE $where");
        $cntStmt->execute($params);
        $total = (int) $cntStmt->fetchColumn();

        $stmt = $db->prepare("
            SELECT u.id, u.full_name, u.first_name, u.surname, u.phone, u.country,
                   u.created_at,
                   (SELECT s.plan FROM subscriptions s WHERE s.user_id=u.id AND s.status='active' AND s.expires_at>NOW() ORDER BY s.expires_at DESC LIMIT 1) as active_plan,
                   (SELECT s.expires_at FROM subscriptions s WHERE s.user_id=u.id AND s.status='active' AND s.expires_at>NOW() ORDER BY s.expires_at DESC LIMIT 1) as expires_at
            FROM users u
            WHERE $where
            ORDER BY u.created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $rows  = [];
    $total = 0;
}

$totalPages = max(1, ceil($total / $perPage));

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page Header ────────────────────────────── -->
<div class="section-header">
    <div>
        <h1><i class="fas fa-users" style="color:var(--orange);margin-right:8px;"></i>Subscribers</h1>
        <p>Manage users, subscriptions, and revenue across all countries.</p>
    </div>
</div>

<!-- ── Stat Cards ─────────────────────────────── -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon si-navy"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['users']); ?></div>
            <div class="stat-label">Registered Users</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['active']); ?></div>
            <div class="stat-label">Active Subscriptions</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-orange"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['expired']); ?></div>
            <div class="stat-label">Expired (need renewal)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-purple"><i class="fas fa-coins"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['revenue_ugx']); ?></div>
            <div class="stat-label">Revenue (UGX)</div>
        </div>
    </div>
</div>

<!-- ── Tabs ───────────────────────────────────── -->
<div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid #e5e7eb;padding-bottom:0;">
    <?php foreach (['users' => 'All Users', 'active' => 'Active Subscriptions', 'revenue' => 'Revenue Breakdown'] as $t => $label): ?>
    <a href="<?php echo portal_url('subscribers.php?tab=' . $t); ?>"
       style="padding:10px 20px;font-size:14px;font-weight:600;border-radius:8px 8px 0 0;text-decoration:none;color:<?php echo $tab === $t ? 'var(--orange)' : '#888'; ?>;background:<?php echo $tab === $t ? '#fff' : 'transparent'; ?>;border:<?php echo $tab === $t ? '2px solid #e5e7eb' : '2px solid transparent'; ?>;border-bottom:<?php echo $tab === $t ? '2px solid #fff' : '2px solid transparent'; ?>;margin-bottom:-2px;">
        <?php echo $label; ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if ($tab !== 'revenue'): ?>
<!-- ── Search + Country Filter ────────────────── -->
<form method="GET" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
           class="form-control" placeholder="Search name or phone..."
           style="flex:1;min-width:200px;max-width:340px;">
    <select name="country" class="form-control" style="width:180px;">
        <option value="all" <?php echo $country === 'all' ? 'selected' : ''; ?>>All Countries</option>
        <option value="ug"  <?php echo $country === 'ug'  ? 'selected' : ''; ?>>🇺🇬 Uganda</option>
        <option value="ke"  <?php echo $country === 'ke'  ? 'selected' : ''; ?>>🇰🇪 Kenya</option>
        <option value="ng"  <?php echo $country === 'ng'  ? 'selected' : ''; ?>>🇳🇬 Nigeria</option>
        <option value="za"  <?php echo $country === 'za'  ? 'selected' : ''; ?>>🇿🇦 South Africa</option>
    </select>
    <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Search</button>
    <?php if ($search || $country !== 'all'): ?>
    <a href="<?php echo portal_url('subscribers.php?tab=' . $tab); ?>" class="btn btn-outline">Clear</a>
    <?php endif; ?>
</form>
<?php endif; ?>

<!-- ── Content ────────────────────────────────── -->
<div class="card">

<?php if ($tab === 'revenue'): ?>
    <!-- Revenue table -->
    <div class="card-header"><h2><i class="fas fa-coins"></i> Revenue Breakdown</h2></div>
    <?php if (empty($rows)): ?>
    <div class="empty-state"><i class="fas fa-chart-bar"></i><h3>No revenue data yet</h3><p>Revenue will appear here once subscriptions are purchased.</p></div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr>
                    <th>Currency</th>
                    <th>Plan</th>
                    <th>Subscriptions</th>
                    <th>Total Revenue</th>
                    <th>Avg. Per Sub</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['currency']); ?></strong></td>
                    <td><span class="badge badge-<?php echo $row['plan'] === 'monthly' ? 'published' : 'draft'; ?>"><?php echo ucfirst($row['plan']); ?></span></td>
                    <td><?php echo number_format($row['count']); ?></td>
                    <td><strong><?php echo number_format((float)$row['total']); ?> <?php echo htmlspecialchars($row['currency']); ?></strong></td>
                    <td><?php echo number_format((float)$row['avg_amount']); ?> <?php echo htmlspecialchars($row['currency']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

<?php elseif ($tab === 'active'): ?>
    <!-- Active subscriptions -->
    <div class="card-header">
        <h2><i class="fas fa-id-badge"></i> Active Subscriptions</h2>
        <span style="font-size:13px;color:#888;"><?php echo number_format($total); ?> active</span>
    </div>
    <?php if (empty($rows)): ?>
    <div class="empty-state"><i class="fas fa-id-badge"></i><h3>No active subscriptions</h3><p>Subscribers will appear here once they complete payment.</p></div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr>
                    <th>Subscriber</th>
                    <th>Country</th>
                    <th>Plan</th>
                    <th>Provider</th>
                    <th>Amount</th>
                    <th>Expires</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <strong style="font-size:13px;color:var(--navy);"><?php echo htmlspecialchars($row['full_name'] ?? 'Unknown'); ?></strong>
                        <div style="font-size:11px;color:#9ca3af;"><?php echo htmlspecialchars($row['phone']); ?></div>
                    </td>
                    <td style="font-size:12px;font-weight:700;"><?php echo strtoupper($row['country']); ?></td>
                    <td><span class="badge badge-<?php echo $row['plan'] === 'monthly' ? 'published' : 'daily'; ?>"><?php echo ucfirst($row['plan']); ?></span></td>
                    <td style="font-size:12px;color:#555;"><?php echo ucfirst($row['payment_provider'] ?? '—'); ?></td>
                    <td style="font-weight:600;font-size:13px;"><?php echo number_format((float)$row['amount']); ?> <?php echo $row['currency']; ?></td>
                    <td style="font-size:12px;<?php echo strtotime($row['expires_at']) < strtotime('+3 days') ? 'color:#dc2626;font-weight:600;' : 'color:#555;'; ?>">
                        <?php echo date('M j, Y', strtotime($row['expires_at'])); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

<?php else: ?>
    <!-- All users -->
    <div class="card-header">
        <h2><i class="fas fa-users"></i> All Users</h2>
        <span style="font-size:13px;color:#888;"><?php echo number_format($total); ?> users</span>
    </div>
    <?php if (empty($rows)): ?>
    <div class="empty-state"><i class="fas fa-users"></i><h3>No users yet</h3><p>Users will appear here once they sign up on the app.</p></div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Country</th>
                    <th>Subscription</th>
                    <th>Expires</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0;">
                                <?php echo mb_strtoupper(mb_substr($row['full_name'] ?? 'U', 0, 1)); ?>
                            </div>
                            <strong style="font-size:13px;color:var(--navy);"><?php echo htmlspecialchars($row['full_name'] ?? 'Unknown'); ?></strong>
                        </div>
                    </td>
                    <td style="font-size:13px;color:#555;"><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td style="font-size:12px;font-weight:700;"><?php echo strtoupper($row['country']); ?></td>
                    <td>
                        <?php if (!empty($row['active_plan'])): ?>
                        <span class="badge badge-active"><?php echo ucfirst($row['active_plan']); ?></span>
                        <?php else: ?>
                        <span class="badge badge-draft">Free</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:#888;">
                        <?php echo !empty($row['expires_at']) ? date('M j, Y', strtotime($row['expires_at'])) : '—'; ?>
                    </td>
                    <td style="font-size:12px;color:#888;">
                        <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
<?php endif; ?>

    <!-- Pagination -->
    <?php if ($tab !== 'revenue' && $totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="<?php echo portal_url('subscribers.php?tab=' . $tab . '&page=' . ($page-1) . ($search ? '&q=' . urlencode($search) : '') . ($country !== 'all' ? '&country=' . $country : '')); ?>">
            <i class="fas fa-chevron-left"></i> Prev
        </a>
        <?php else: ?>
        <span class="disabled"><i class="fas fa-chevron-left"></i> Prev</span>
        <?php endif; ?>

        <span class="current"><?php echo $page; ?> / <?php echo $totalPages; ?></span>

        <?php if ($page < $totalPages): ?>
        <a href="<?php echo portal_url('subscribers.php?tab=' . $tab . '&page=' . ($page+1) . ($search ? '&q=' . urlencode($search) : '') . ($country !== 'all' ? '&country=' . $country : '')); ?>">
            Next <i class="fas fa-chevron-right"></i>
        </a>
        <?php else: ?>
        <span class="disabled">Next <i class="fas fa-chevron-right"></i></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
/**
 * KandaNews Africa — Ads Dashboard
 *
 * Monitor advertisers, bookings, revenue, and active campaigns
 * from the ads.kandanews.africa platform.
 *
 * Tabs: Overview | Advertisers | Bookings | Payments
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$page_title   = 'Ads Dashboard';
$page_section = 'ads';
$db           = portal_db();

$tab    = $_GET['tab']    ?? 'overview';
$search = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? 'all';
$page   = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 30;
$offset  = ($page - 1) * $perPage;

// ── Stats ──────────────────────────────────────
$stats = [
    'advertisers'   => 0,
    'bookings'      => 0,
    'paid_ugx'      => 0,
    'pending_ugx'   => 0,
    'active_today'  => 0,
];
try {
    $stats['advertisers']  = (int) $db->query("SELECT COUNT(*) FROM ads_advertisers WHERE status='active'")->fetchColumn();
    $stats['bookings']     = (int) $db->query("SELECT COUNT(*) FROM ads_bookings")->fetchColumn();
    $stats['paid_ugx']     = (float) $db->query("SELECT COALESCE(SUM(total_price),0) FROM ads_bookings WHERE payment_status='paid'")->fetchColumn();
    $stats['pending_ugx']  = (float) $db->query("SELECT COALESCE(SUM(total_price),0) FROM ads_bookings WHERE payment_status='pending'")->fetchColumn();
    $stats['active_today'] = (int) $db->query("SELECT COUNT(*) FROM ads_bookings WHERE status IN ('confirmed','active') AND start_date <= CURDATE() AND end_date >= CURDATE()")->fetchColumn();
} catch (PDOException $e) {}

// ── Tab data ───────────────────────────────────
$rows       = [];
$total      = 0;
$formatRows = [];
$recentRows = [];

try {
    if ($tab === 'advertisers') {
        $where  = "a.status = 'active'";
        $params = [];
        if ($search) {
            $where .= ' AND (a.company_name LIKE ? OR a.email LIKE ? OR a.phone LIKE ?)';
            $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
        }
        $cntStmt = $db->prepare("SELECT COUNT(*) FROM ads_advertisers a WHERE $where");
        $cntStmt->execute($params);
        $total = (int) $cntStmt->fetchColumn();

        $stmt = $db->prepare("
            SELECT a.id, a.company_name, a.contact_name, a.email, a.phone, a.country,
                   a.created_at,
                   COUNT(b.id)                                          AS total_bookings,
                   SUM(CASE WHEN b.payment_status='paid'
                            THEN b.total_price ELSE 0 END)              AS total_paid,
                   MAX(b.created_at)                                    AS last_booking
            FROM ads_advertisers a
            LEFT JOIN ads_bookings b ON b.advertiser_id = a.id
            WHERE $where
            GROUP BY a.id
            ORDER BY a.created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

    } elseif ($tab === 'bookings') {
        $where  = '1=1';
        $params = [];
        if ($search) {
            $where .= ' AND (a.company_name LIKE ? OR b.format_label LIKE ?)';
            $params[] = "%$search%"; $params[] = "%$search%";
        }
        if ($status !== 'all') {
            $where .= ' AND b.payment_status = ?';
            $params[] = $status;
        }
        $cntStmt = $db->prepare("SELECT COUNT(*) FROM ads_bookings b JOIN ads_advertisers a ON a.id=b.advertiser_id WHERE $where");
        $cntStmt->execute($params);
        $total = (int) $cntStmt->fetchColumn();

        $stmt = $db->prepare("
            SELECT b.id, b.format_label, b.start_date, b.end_date, b.days,
                   b.total_price, b.discount_pct, b.payment_status, b.status,
                   b.created_at, b.flw_ref,
                   a.company_name, a.phone
            FROM ads_bookings b
            JOIN ads_advertisers a ON a.id = b.advertiser_id
            WHERE $where
            ORDER BY b.created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

    } elseif ($tab === 'payments') {
        $where  = '1=1';
        $params = [];
        if ($search) {
            $where .= ' AND (p.flw_ref LIKE ? OR p.flw_tx_id LIKE ?)';
            $params[] = "%$search%"; $params[] = "%$search%";
        }
        $cntStmt = $db->prepare("SELECT COUNT(*) FROM ads_payment_log p WHERE $where");
        $cntStmt->execute($params);
        $total = (int) $cntStmt->fetchColumn();

        $stmt = $db->prepare("
            SELECT p.id, p.booking_id, p.flw_ref, p.flw_tx_id, p.event,
                   p.amount, p.currency, p.created_at,
                   a.company_name
            FROM ads_payment_log p
            LEFT JOIN ads_advertisers a ON a.id = p.advertiser_id
            WHERE $where
            ORDER BY p.created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

    } else {
        // Overview: recent bookings + format performance + active today
        $recentRows = $db->query("
            SELECT b.id, b.format_label, b.start_date, b.end_date,
                   b.total_price, b.payment_status, b.status, b.created_at,
                   a.company_name
            FROM ads_bookings b
            JOIN ads_advertisers a ON a.id = b.advertiser_id
            ORDER BY b.created_at DESC
            LIMIT 10
        ")->fetchAll();

        $formatRows = $db->query("
            SELECT format_key, format_label,
                   COUNT(*) AS total_bookings,
                   SUM(CASE WHEN payment_status='paid' THEN total_price ELSE 0 END) AS revenue_ugx
            FROM ads_bookings
            GROUP BY format_key, format_label
            ORDER BY revenue_ugx DESC
        ")->fetchAll();
    }
} catch (PDOException $e) {
    $rows = $recentRows = $formatRows = [];
}

$totalPages = max(1, ceil($total / $perPage));

require_once __DIR__ . '/includes/header.php';

// ── Helpers ────────────────────────────────────
function ads_payment_badge(string $s): string {
    $map = [
        'paid'    => 'badge-active',
        'pending' => 'badge-draft',
        'failed'  => 'badge-archived',
    ];
    return '<span class="badge ' . ($map[$s] ?? 'badge-draft') . '">' . ucfirst($s) . '</span>';
}
function ads_status_badge(string $s): string {
    $map = [
        'confirmed' => 'badge-published',
        'active'    => 'badge-active',
        'completed' => 'badge-university',
        'pending'   => 'badge-draft',
        'cancelled' => 'badge-archived',
    ];
    return '<span class="badge ' . ($map[$s] ?? 'badge-draft') . '">' . ucfirst($s) . '</span>';
}
function ads_event_badge(string $e): string {
    $map = [
        'success' => 'badge-active',
        'webhook' => 'badge-published',
        'init'    => 'badge-draft',
        'failed'  => 'badge-archived',
        'cancelled' => 'badge-archived',
    ];
    return '<span class="badge ' . ($map[$e] ?? 'badge-draft') . '">' . ucfirst($e) . '</span>';
}
?>

<!-- ── Page Header ────────────────────────────── -->
<div class="section-header">
    <div>
        <h1><i class="fas fa-bullhorn" style="color:var(--orange);margin-right:8px;"></i>Ads Dashboard</h1>
        <p>Monitor advertisers, campaign bookings, and payments from ads.kandanews.africa</p>
    </div>
    <div>
        <a href="https://ads.kandanews.africa" target="_blank" class="btn btn-outline btn-sm">
            <i class="fas fa-external-link-alt"></i> Ads Portal
        </a>
    </div>
</div>

<!-- ── Stat Cards ─────────────────────────────── -->
<div class="stat-grid">
    <a href="?tab=advertisers" class="stat-card">
        <div class="stat-icon si-navy"><i class="fas fa-building"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['advertisers']); ?></div>
            <div class="stat-label">Advertisers</div>
        </div>
    </a>
    <a href="?tab=bookings" class="stat-card">
        <div class="stat-icon si-blue"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['bookings']); ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
    </a>
    <div class="stat-card">
        <div class="stat-icon si-green"><i class="fas fa-coins"></i></div>
        <div class="stat-info">
            <div class="stat-value">UGX <?php echo number_format($stats['paid_ugx']); ?></div>
            <div class="stat-label">Revenue Collected</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-orange"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-info">
            <div class="stat-value">UGX <?php echo number_format($stats['pending_ugx']); ?></div>
            <div class="stat-label">Pending Payment</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-purple"><i class="fas fa-play-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['active_today']); ?></div>
            <div class="stat-label">Campaigns Live Today</div>
        </div>
    </div>
</div>

<!-- ── Tabs ───────────────────────────────────── -->
<div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid #e5e7eb;padding-bottom:0;">
    <?php foreach (['overview' => 'Overview', 'advertisers' => 'Advertisers', 'bookings' => 'Bookings', 'payments' => 'Payment Log'] as $t => $label): ?>
    <a href="?tab=<?php echo $t; ?>"
       style="padding:10px 20px;font-size:14px;font-weight:600;border-radius:8px 8px 0 0;text-decoration:none;
              color:<?php echo $tab === $t ? 'var(--orange)' : '#888'; ?>;
              background:<?php echo $tab === $t ? '#fff' : 'transparent'; ?>;
              border:<?php echo $tab === $t ? '2px solid #e5e7eb' : '2px solid transparent'; ?>;
              border-bottom:<?php echo $tab === $t ? '2px solid #fff' : '2px solid transparent'; ?>;
              margin-bottom:-2px;">
        <?php echo $label; ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if ($tab === 'overview'): ?>
<!-- ══════════════════════════════════════
     OVERVIEW TAB
══════════════════════════════════════ -->

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="two-col-grid">

    <!-- Recent Bookings -->
    <div class="card" style="grid-column:1 / -1;">
        <div class="card-header">
            <h2><i class="fas fa-calendar-alt"></i> Recent Bookings</h2>
            <a href="?tab=bookings" class="btn btn-outline btn-sm">View All</a>
        </div>
        <?php if (empty($recentRows)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>No bookings yet</h3>
            <p>Bookings will appear here once advertisers start booking campaigns.</p>
        </div>
        <?php else: ?>
        <div class="table-wrapper">
            <table class="dt">
                <thead>
                    <tr>
                        <th>Advertiser</th>
                        <th>Format</th>
                        <th>Campaign Dates</th>
                        <th>Amount (UGX)</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Booked</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentRows as $row): ?>
                    <tr>
                        <td><strong style="font-size:13px;color:var(--navy);"><?php echo htmlspecialchars($row['company_name']); ?></strong></td>
                        <td style="font-size:13px;"><?php echo htmlspecialchars($row['format_label']); ?></td>
                        <td style="font-size:12px;color:#555;">
                            <?php echo date('M j', strtotime($row['start_date'])); ?> →
                            <?php echo date('M j, Y', strtotime($row['end_date'])); ?>
                        </td>
                        <td style="font-weight:600;font-size:13px;"><?php echo number_format((float)$row['total_price']); ?></td>
                        <td><?php echo ads_payment_badge($row['payment_status']); ?></td>
                        <td><?php echo ads_status_badge($row['status']); ?></td>
                        <td style="font-size:12px;color:#888;"><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Format Performance -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-chart-bar"></i> Format Performance</h2>
        </div>
        <?php if (empty($formatRows)): ?>
        <div class="empty-state" style="padding:30px 20px;">
            <i class="fas fa-chart-bar"></i>
            <h3>No data yet</h3>
        </div>
        <?php else: ?>
        <div class="table-wrapper">
            <table class="dt">
                <thead>
                    <tr>
                        <th>Ad Format</th>
                        <th>Bookings</th>
                        <th>Revenue (UGX)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($formatRows as $row): ?>
                    <tr>
                        <td style="font-size:13px;font-weight:600;"><?php echo htmlspecialchars($row['format_label']); ?></td>
                        <td><?php echo number_format((int)$row['total_bookings']); ?></td>
                        <td style="font-weight:600;color:var(--green);"><?php echo number_format((float)$row['revenue_ugx']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Active Campaigns Today -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-broadcast-tower"></i> Live Campaigns Today</h2>
            <span style="font-size:13px;color:#888;"><?php echo $stats['active_today']; ?> running</span>
        </div>
        <?php
        try {
            $liveRows = $db->query("
                SELECT b.format_label, b.end_date, b.total_price, a.company_name, a.phone
                FROM ads_bookings b
                JOIN ads_advertisers a ON a.id = b.advertiser_id
                WHERE b.status IN ('confirmed','active')
                  AND b.start_date <= CURDATE()
                  AND b.end_date   >= CURDATE()
                ORDER BY b.end_date ASC
                LIMIT 10
            ")->fetchAll();
        } catch (PDOException $e) { $liveRows = []; }
        ?>
        <?php if (empty($liveRows)): ?>
        <div class="empty-state" style="padding:30px 20px;">
            <i class="fas fa-satellite-dish"></i>
            <h3>No campaigns live today</h3>
        </div>
        <?php else: ?>
        <div class="table-wrapper">
            <table class="dt">
                <thead>
                    <tr><th>Advertiser</th><th>Format</th><th>Ends</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($liveRows as $row): ?>
                    <tr>
                        <td>
                            <strong style="font-size:13px;color:var(--navy);"><?php echo htmlspecialchars($row['company_name']); ?></strong>
                            <div style="font-size:11px;color:#9ca3af;"><?php echo htmlspecialchars($row['phone']); ?></div>
                        </td>
                        <td style="font-size:13px;"><?php echo htmlspecialchars($row['format_label']); ?></td>
                        <td style="font-size:12px;<?php echo strtotime($row['end_date']) <= strtotime('+2 days') ? 'color:#dc2626;font-weight:600;' : 'color:#555;'; ?>">
                            <?php echo date('M j, Y', strtotime($row['end_date'])); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php elseif ($tab === 'advertisers'): ?>
<!-- ══════════════════════════════════════
     ADVERTISERS TAB
══════════════════════════════════════ -->

<!-- Search -->
<form method="GET" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <input type="hidden" name="tab" value="advertisers">
    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
           class="form-control" placeholder="Search company, email or phone..."
           style="flex:1;min-width:220px;max-width:380px;">
    <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Search</button>
    <?php if ($search): ?>
    <a href="?tab=advertisers" class="btn btn-outline">Clear</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-building"></i> Registered Advertisers</h2>
        <span style="font-size:13px;color:#888;"><?php echo number_format($total); ?> registered</span>
    </div>
    <?php if (empty($rows)): ?>
    <div class="empty-state">
        <i class="fas fa-building"></i>
        <h3>No advertisers yet</h3>
        <p>Advertisers will appear here once they register on ads.kandanews.africa</p>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Contact</th>
                    <th>Country</th>
                    <th>Bookings</th>
                    <th>Total Paid (UGX)</th>
                    <th>Last Booking</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:34px;height:34px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0;">
                                <?php echo mb_strtoupper(mb_substr($row['company_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <strong style="font-size:13px;color:var(--navy);"><?php echo htmlspecialchars($row['company_name']); ?></strong>
                                <div style="font-size:11px;color:#9ca3af;"><?php echo htmlspecialchars($row['email']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:13px;"><?php echo htmlspecialchars($row['contact_name']); ?></div>
                        <div style="font-size:11px;color:#9ca3af;"><?php echo htmlspecialchars($row['phone']); ?></div>
                    </td>
                    <td style="font-size:13px;"><?php echo htmlspecialchars($row['country']); ?></td>
                    <td style="font-weight:700;font-size:14px;text-align:center;"><?php echo (int)$row['total_bookings']; ?></td>
                    <td style="font-weight:600;color:var(--green);font-size:13px;">
                        <?php echo $row['total_paid'] > 0 ? number_format((float)$row['total_paid']) : '—'; ?>
                    </td>
                    <td style="font-size:12px;color:#888;">
                        <?php echo $row['last_booking'] ? date('M j, Y', strtotime($row['last_booking'])) : '—'; ?>
                    </td>
                    <td style="font-size:12px;color:#888;"><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?><a href="?tab=advertisers&page=<?php echo $page-1; echo $search ? '&q='.urlencode($search) : ''; ?>"><i class="fas fa-chevron-left"></i> Prev</a><?php else: ?><span class="disabled"><i class="fas fa-chevron-left"></i> Prev</span><?php endif; ?>
        <span class="current"><?php echo $page; ?> / <?php echo $totalPages; ?></span>
        <?php if ($page < $totalPages): ?><a href="?tab=advertisers&page=<?php echo $page+1; echo $search ? '&q='.urlencode($search) : ''; ?>">Next <i class="fas fa-chevron-right"></i></a><?php else: ?><span class="disabled">Next <i class="fas fa-chevron-right"></i></span><?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php elseif ($tab === 'bookings'): ?>
<!-- ══════════════════════════════════════
     BOOKINGS TAB
══════════════════════════════════════ -->

<!-- Search + Filter -->
<form method="GET" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <input type="hidden" name="tab" value="bookings">
    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
           class="form-control" placeholder="Search company or format..."
           style="flex:1;min-width:200px;max-width:340px;">
    <select name="status" class="form-control" style="width:180px;">
        <option value="all"    <?php echo $status==='all'     ? 'selected':'' ?>>All Payments</option>
        <option value="paid"   <?php echo $status==='paid'    ? 'selected':'' ?>>Paid</option>
        <option value="pending"<?php echo $status==='pending' ? 'selected':'' ?>>Pending</option>
        <option value="failed" <?php echo $status==='failed'  ? 'selected':'' ?>>Failed</option>
    </select>
    <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Search</button>
    <?php if ($search || $status !== 'all'): ?>
    <a href="?tab=bookings" class="btn btn-outline">Clear</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-calendar-check"></i> All Bookings</h2>
        <span style="font-size:13px;color:#888;"><?php echo number_format($total); ?> bookings</span>
    </div>
    <?php if (empty($rows)): ?>
    <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <h3>No bookings found</h3>
        <p>Bookings appear here once advertisers submit campaigns.</p>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Advertiser</th>
                    <th>Format</th>
                    <th>Campaign Dates</th>
                    <th>Days</th>
                    <th>Discount</th>
                    <th>Amount (UGX)</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Reference</th>
                    <th>Booked</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td style="font-size:12px;color:#9ca3af;"><?php echo $row['id']; ?></td>
                    <td>
                        <strong style="font-size:13px;color:var(--navy);"><?php echo htmlspecialchars($row['company_name']); ?></strong>
                        <div style="font-size:11px;color:#9ca3af;"><?php echo htmlspecialchars($row['phone']); ?></div>
                    </td>
                    <td style="font-size:13px;"><?php echo htmlspecialchars($row['format_label']); ?></td>
                    <td style="font-size:12px;color:#555;white-space:nowrap;">
                        <?php echo date('M j', strtotime($row['start_date'])); ?> →
                        <?php echo date('M j, Y', strtotime($row['end_date'])); ?>
                    </td>
                    <td style="text-align:center;font-size:13px;"><?php echo (int)$row['days']; ?></td>
                    <td style="text-align:center;font-size:13px;">
                        <?php echo $row['discount_pct'] > 0 ? '<span style="color:var(--green);font-weight:600;">' . (int)$row['discount_pct'] . '%</span>' : '—'; ?>
                    </td>
                    <td style="font-weight:700;font-size:13px;"><?php echo number_format((float)$row['total_price']); ?></td>
                    <td><?php echo ads_payment_badge($row['payment_status']); ?></td>
                    <td><?php echo ads_status_badge($row['status']); ?></td>
                    <td style="font-size:11px;color:#9ca3af;max-width:120px;overflow:hidden;text-overflow:ellipsis;">
                        <?php echo $row['flw_ref'] ? htmlspecialchars($row['flw_ref']) : '—'; ?>
                    </td>
                    <td style="font-size:12px;color:#888;white-space:nowrap;"><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php
        $qs = '&tab=bookings' . ($search ? '&q='.urlencode($search) : '') . ($status !== 'all' ? '&status='.$status : '');
        ?>
        <?php if ($page > 1): ?><a href="?page=<?php echo $page-1 . $qs; ?>"><i class="fas fa-chevron-left"></i> Prev</a><?php else: ?><span class="disabled"><i class="fas fa-chevron-left"></i> Prev</span><?php endif; ?>
        <span class="current"><?php echo $page; ?> / <?php echo $totalPages; ?></span>
        <?php if ($page < $totalPages): ?><a href="?page=<?php echo $page+1 . $qs; ?>">Next <i class="fas fa-chevron-right"></i></a><?php else: ?><span class="disabled">Next <i class="fas fa-chevron-right"></i></span><?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php elseif ($tab === 'payments'): ?>
<!-- ══════════════════════════════════════
     PAYMENT LOG TAB
══════════════════════════════════════ -->

<!-- Search -->
<form method="GET" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <input type="hidden" name="tab" value="payments">
    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
           class="form-control" placeholder="Search Flutterwave ref or tx ID..."
           style="flex:1;min-width:220px;max-width:380px;">
    <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Search</button>
    <?php if ($search): ?>
    <a href="?tab=payments" class="btn btn-outline">Clear</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-receipt"></i> Payment Audit Log</h2>
        <span style="font-size:13px;color:#888;"><?php echo number_format($total); ?> events</span>
    </div>
    <?php if (empty($rows)): ?>
    <div class="empty-state">
        <i class="fas fa-receipt"></i>
        <h3>No payment events yet</h3>
        <p>All Flutterwave payment events are logged here.</p>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Advertiser</th>
                    <th>Booking</th>
                    <th>Event</th>
                    <th>Amount (UGX)</th>
                    <th>FLW Reference</th>
                    <th>Transaction ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td style="font-size:12px;color:#888;white-space:nowrap;">
                        <?php echo date('M j, H:i', strtotime($row['created_at'])); ?>
                    </td>
                    <td style="font-size:13px;"><?php echo $row['company_name'] ? htmlspecialchars($row['company_name']) : '<span style="color:#9ca3af;">—</span>'; ?></td>
                    <td style="font-size:13px;font-weight:600;">
                        <?php echo $row['booking_id'] ? '#' . $row['booking_id'] : '—'; ?>
                    </td>
                    <td><?php echo ads_event_badge($row['event']); ?></td>
                    <td style="font-weight:600;font-size:13px;">
                        <?php echo $row['amount'] > 0 ? number_format((float)$row['amount']) : '—'; ?>
                    </td>
                    <td style="font-size:11px;color:#9ca3af;max-width:130px;overflow:hidden;text-overflow:ellipsis;">
                        <?php echo $row['flw_ref'] ? htmlspecialchars($row['flw_ref']) : '—'; ?>
                    </td>
                    <td style="font-size:11px;color:#9ca3af;">
                        <?php echo $row['flw_tx_id'] ? htmlspecialchars($row['flw_tx_id']) : '—'; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php $qs = '&tab=payments' . ($search ? '&q='.urlencode($search) : ''); ?>
        <?php if ($page > 1): ?><a href="?page=<?php echo $page-1 . $qs; ?>"><i class="fas fa-chevron-left"></i> Prev</a><?php else: ?><span class="disabled"><i class="fas fa-chevron-left"></i> Prev</span><?php endif; ?>
        <span class="current"><?php echo $page; ?> / <?php echo $totalPages; ?></span>
        <?php if ($page < $totalPages): ?><a href="?page=<?php echo $page+1 . $qs; ?>">Next <i class="fas fa-chevron-right"></i></a><?php else: ?><span class="disabled">Next <i class="fas fa-chevron-right"></i></span><?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/shared/config.php';
require_once __DIR__ . '/shared/helpers.php';
require_once __DIR__ . '/shared/auth.php';

ads_session_start();
$__adv = require_ads_auth();

$page_title = 'Dashboard';
$active_nav = 'dashboard';

// Fetch bookings
$bookings    = [];
$stats       = ['total' => 0, 'active' => 0, 'spent' => 0, 'pending' => 0];

try {
    $db = get_db();
    $st = $db->prepare(
        'SELECT id, format_label, start_date, end_date, days, total_price,
                payment_status, status, created_at
         FROM ads_bookings
         WHERE advertiser_id = ?
         ORDER BY created_at DESC
         LIMIT 50'
    );
    $st->execute([$__adv['id']]);
    $bookings = $st->fetchAll();

    // Stats
    $stStats = $db->prepare(
        'SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN status IN ("active","confirmed") THEN 1 ELSE 0 END) AS active_count,
            SUM(CASE WHEN payment_status = "paid" THEN total_price ELSE 0 END) AS total_spent,
            SUM(CASE WHEN payment_status = "pending" THEN 1 ELSE 0 END) AS pending_count
         FROM ads_bookings WHERE advertiser_id = ?'
    );
    $stStats->execute([$__adv['id']]);
    $row   = $stStats->fetch();
    $stats = [
        'total'   => (int)($row['total'] ?? 0),
        'active'  => (int)($row['active_count'] ?? 0),
        'spent'   => (int)($row['total_spent'] ?? 0),
        'pending' => (int)($row['pending_count'] ?? 0),
    ];
} catch (Exception $e) {
    // silently fail — show empty state
}

function status_badge(string $status): string {
    $map = [
        'pending'   => 'kn-badge-pending',
        'confirmed' => 'kn-badge-confirmed',
        'active'    => 'kn-badge-active',
        'completed' => 'kn-badge-completed',
        'cancelled' => 'kn-badge-cancelled',
        'paid'      => 'kn-badge-paid',
        'failed'    => 'kn-badge-failed',
    ];
    $cls   = $map[$status] ?? 'kn-badge-pending';
    $icons = [
        'pending'   => 'fa-clock',
        'confirmed' => 'fa-circle-check',
        'active'    => 'fa-play-circle',
        'completed' => 'fa-flag-checkered',
        'cancelled' => 'fa-ban',
        'paid'      => 'fa-circle-check',
        'failed'    => 'fa-circle-xmark',
    ];
    $icon = $icons[$status] ?? 'fa-circle';
    return '<span class="kn-badge ' . $cls . '"><i class="fa-solid ' . $icon . '"></i> ' . ucfirst($status) . '</span>';
}

require_once __DIR__ . '/shared/header.php';
?>

<div class="kn-page-wrapper">
    <div class="container kn-dashboard">

        <!-- Flash messages -->
        <?php if ($msg = flash('success')): ?>
            <div class="kn-alert kn-alert-success mb-24">
                <i class="fa-solid fa-circle-check kn-alert-icon"></i> <?= h($msg) ?>
            </div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
            <div class="kn-alert kn-alert-error mb-24">
                <i class="fa-solid fa-circle-exclamation kn-alert-icon"></i> <?= h($msg) ?>
            </div>
        <?php endif; ?>

        <!-- Dashboard header -->
        <div class="kn-dashboard-header">
            <div>
                <h1 class="kn-dashboard-title">
                    Welcome, <?= h($__adv['company_name']) ?>!
                </h1>
                <p class="kn-dashboard-subtitle">
                    <i class="fa-solid fa-location-dot" style="color: var(--kn-orange);"></i>
                    Uganda Edition &mdash; Advertiser Dashboard
                </p>
            </div>
            <a href="/book.php" class="kn-btn kn-btn-primary kn-btn-lg">
                <i class="fa-solid fa-calendar-plus"></i> Book New Ad
            </a>
        </div>

        <!-- Stats cards -->
        <div class="kn-stats-cards">
            <div class="kn-stat-card">
                <div class="kn-stat-card-icon navy">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="kn-stat-card-info">
                    <div class="kn-stat-card-value"><?= $stats['total'] ?></div>
                    <div class="kn-stat-card-label">Total Bookings</div>
                </div>
            </div>
            <div class="kn-stat-card">
                <div class="kn-stat-card-icon green">
                    <i class="fa-solid fa-circle-play"></i>
                </div>
                <div class="kn-stat-card-info">
                    <div class="kn-stat-card-value"><?= $stats['active'] ?></div>
                    <div class="kn-stat-card-label">Active Ads</div>
                </div>
            </div>
            <div class="kn-stat-card">
                <div class="kn-stat-card-icon orange">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="kn-stat-card-info">
                    <div class="kn-stat-card-value" style="font-size: 1.1rem;"><?= format_ugx($stats['spent']) ?></div>
                    <div class="kn-stat-card-label">Total Spent</div>
                </div>
            </div>
            <div class="kn-stat-card">
                <div class="kn-stat-card-icon warn">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="kn-stat-card-info">
                    <div class="kn-stat-card-value"><?= $stats['pending'] ?></div>
                    <div class="kn-stat-card-label">Pending</div>
                </div>
            </div>
        </div>

        <!-- Bookings section -->
        <div class="kn-card">
            <div class="kn-card-header">
                <h2 class="kn-card-title">
                    <i class="fa-solid fa-list-check" style="color: var(--kn-orange);"></i>
                    Recent Bookings
                </h2>
                <a href="/book.php" class="kn-btn kn-btn-primary kn-btn-sm">
                    <i class="fa-solid fa-plus"></i> New Booking
                </a>
            </div>
            <div class="kn-card-body" style="padding: 0;">

                <?php if (empty($bookings)): ?>
                    <!-- Empty state -->
                    <div class="kn-empty-state">
                        <div class="kn-empty-icon">
                            <i class="fa-solid fa-calendar-xmark"></i>
                        </div>
                        <h3 class="kn-empty-title">No bookings yet</h3>
                        <p class="kn-empty-desc">You haven't booked any ads yet. Start advertising today and reach 10,000+ subscribers!</p>
                        <a href="/book.php" class="kn-btn kn-btn-primary kn-btn-lg">
                            <i class="fa-solid fa-rocket"></i> Book Your First Ad
                        </a>
                    </div>
                <?php else: ?>
                    <div class="kn-table-wrap" style="border-radius: 0; box-shadow: none; border: none;">
                        <table class="kn-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Ad Format</th>
                                    <th>Dates</th>
                                    <th>Duration</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td data-label="ID"><strong>#<?= h($b['id']) ?></strong></td>
                                    <td data-label="Format">
                                        <strong><?= h($b['format_label']) ?></strong>
                                    </td>
                                    <td data-label="Dates">
                                        <?= h(date('d M Y', strtotime($b['start_date']))) ?>
                                        <span style="color: var(--kn-muted);">&rarr;</span>
                                        <?= h(date('d M Y', strtotime($b['end_date']))) ?>
                                    </td>
                                    <td data-label="Duration"><?= h($b['days']) ?> day<?= $b['days'] != 1 ? 's' : '' ?></td>
                                    <td data-label="Total"><strong><?= format_ugx($b['total_price']) ?></strong></td>
                                    <td data-label="Payment"><?= status_badge($b['payment_status']) ?></td>
                                    <td data-label="Status"><?= status_badge($b['status']) ?></td>
                                    <td data-label="Action">
                                        <?php if ($b['payment_status'] === 'pending'): ?>
                                            <a href="/checkout.php?booking_id=<?= h($b['id']) ?>"
                                               class="kn-btn kn-btn-primary kn-btn-sm">
                                                <i class="fa-solid fa-credit-card"></i> Pay
                                            </a>
                                        <?php else: ?>
                                            <span class="kn-badge kn-badge-completed">
                                                <i class="fa-solid fa-circle-check"></i> Paid
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Account info -->
        <div class="kn-card" style="margin-top: 24px;">
            <div class="kn-card-header">
                <h2 class="kn-card-title">
                    <i class="fa-solid fa-circle-user" style="color: var(--kn-orange);"></i>
                    Account Details
                </h2>
            </div>
            <div class="kn-card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--kn-muted); margin-bottom: 4px;">Business</div>
                        <div style="font-weight: 700; color: var(--kn-navy);"><?= h($__adv['company_name']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--kn-muted); margin-bottom: 4px;">Contact</div>
                        <div style="font-weight: 600;"><?= h($__adv['contact_name']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--kn-muted); margin-bottom: 4px;">Email</div>
                        <div><?= h($__adv['email']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--kn-muted); margin-bottom: 4px;">Phone</div>
                        <div><?= h($__adv['phone']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--kn-muted); margin-bottom: 4px;">Country</div>
                        <div><?= h($__adv['country']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--kn-muted); margin-bottom: 4px;">Edition</div>
                        <div><span class="kn-badge kn-badge-orange"><i class="fa-solid fa-flag"></i> Uganda</span></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/shared/footer.php'; ?>

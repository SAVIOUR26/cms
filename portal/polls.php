<?php
/**
 * KandaNews Africa — Polls Management
 *
 * Create / edit / activate / close polls and manage their vote options.
 *
 * URL params:
 *   ?manage=ID  — show options manager for a specific poll
 *   ?edit=ID    — open edit modal for a specific poll
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$pdo        = portal_db();
$page_title = 'Polls';

// ── Action handlers ──────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!portal_verify_csrf()) {
        portal_flash('error', 'Invalid request. Please try again.');
        header('Location: ' . portal_url('polls.php'));
        exit;
    }

    $action = $_POST['_action'] ?? '';

    // ── Save poll (create or update meta) ────────────────────────────────────
    if ($action === 'save_poll') {
        $id          = (int)($_POST['id'] ?? 0);
        $question    = trim($_POST['question'] ?? '');
        $description = trim($_POST['description'] ?? '') ?: null;
        $cover_url   = trim($_POST['cover_image_url'] ?? '') ?: null;
        $country     = strtolower(trim($_POST['country'] ?? 'ug'));
        $status      = $_POST['status'] ?? 'draft';
        $starts_at   = trim($_POST['starts_at'] ?? '') ?: null;
        $ends_at     = trim($_POST['ends_at'] ?? '') ?: null;
        $sort_order  = max(0, (int)($_POST['sort_order'] ?? 0));

        if (!in_array($status, ['draft', 'active', 'closed'], true)) $status = 'draft';
        if (!in_array($country, ['ug', 'ke', 'ng', 'za'], true)) $country = 'ug';

        if (!$question) {
            portal_flash('error', 'Question is required.');
            header('Location: ' . portal_url('polls.php'));
            exit;
        }

        if ($id > 0) {
            $pdo->prepare("
                UPDATE polls
                SET question=?, description=?, cover_image_url=?, country=?,
                    status=?, starts_at=?, ends_at=?, sort_order=?
                WHERE id=?
            ")->execute([$question, $description, $cover_url, $country,
                         $status, $starts_at, $ends_at, $sort_order, $id]);

            portal_flash('success', 'Poll updated.');

            // If creating fresh options (only when $id > 0 and options submitted)
            // handled by save_options below
        } else {
            // Create poll
            $stmt = $pdo->prepare("
                INSERT INTO polls
                    (question, description, cover_image_url, country,
                     status, starts_at, ends_at, sort_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$question, $description, $cover_url, $country,
                            $status, $starts_at, $ends_at, $sort_order]);
            $id = (int)$pdo->lastInsertId();

            // Save initial options if provided
            $options = $_POST['options'] ?? [];
            foreach ($options as $i => $opt) {
                $text = trim($opt['text'] ?? '');
                $img  = trim($opt['image_url'] ?? '') ?: null;
                if ($text) {
                    $pdo->prepare("
                        INSERT INTO poll_options (poll_id, option_text, image_url, sort_order)
                        VALUES (?, ?, ?, ?)
                    ")->execute([$id, $text, $img, $i]);
                }
            }

            portal_flash('success', 'Poll created. Add or review options below.');
            header('Location: ' . portal_url("polls.php?manage=$id"));
            exit;
        }

        header('Location: ' . portal_url('polls.php'));
        exit;
    }

    // ── Add a single option to an existing poll ──────────────────────────────
    if ($action === 'add_option') {
        $poll_id = (int)($_POST['poll_id'] ?? 0);
        $text    = trim($_POST['option_text'] ?? '');
        $img     = trim($_POST['image_url'] ?? '') ?: null;

        if ($poll_id && $text) {
            $order = (int)$pdo->query(
                "SELECT COALESCE(MAX(sort_order),0)+1 FROM poll_options WHERE poll_id=$poll_id"
            )->fetchColumn();
            $pdo->prepare("
                INSERT INTO poll_options (poll_id, option_text, image_url, sort_order)
                VALUES (?, ?, ?, ?)
            ")->execute([$poll_id, $text, $img, $order]);
            portal_flash('success', 'Option added.');
        }
        header('Location: ' . portal_url("polls.php?manage=$poll_id"));
        exit;
    }

    // ── Delete an option (only if 0 votes) ───────────────────────────────────
    if ($action === 'delete_option') {
        $option_id = (int)($_POST['option_id'] ?? 0);
        $poll_id   = (int)($_POST['poll_id'] ?? 0);

        $votes = (int)$pdo->prepare(
            "SELECT COUNT(*) FROM poll_votes WHERE option_id=?"
        )->execute([$option_id]) ? $pdo->query(
            "SELECT COUNT(*) FROM poll_votes WHERE option_id=$option_id"
        )->fetchColumn() : 0;

        if ($votes === 0) {
            $pdo->prepare("DELETE FROM poll_options WHERE id=?")->execute([$option_id]);
            portal_flash('success', 'Option removed.');
        } else {
            portal_flash('error', "Cannot remove — this option has $votes votes.");
        }
        header('Location: ' . portal_url("polls.php?manage=$poll_id"));
        exit;
    }

    // ── Change poll status ────────────────────────────────────────────────────
    if ($action === 'set_status') {
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if ($id && in_array($status, ['draft', 'active', 'closed'], true)) {
            $pdo->prepare("UPDATE polls SET status=? WHERE id=?")->execute([$status, $id]);
            portal_flash('success', 'Poll status updated.');
        }
        header('Location: ' . portal_url('polls.php'));
        exit;
    }

    // ── Delete poll (only if 0 total votes) ──────────────────────────────────
    if ($action === 'delete_poll') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $votes = (int)$pdo->query(
                "SELECT COUNT(*) FROM poll_votes WHERE poll_id=$id"
            )->fetchColumn();
            if ($votes === 0) {
                $pdo->prepare("DELETE FROM polls WHERE id=?")->execute([$id]);
                portal_flash('success', 'Poll deleted.');
            } else {
                portal_flash('error', "Cannot delete — this poll has $votes votes. Close it instead.");
            }
        }
        header('Location: ' . portal_url('polls.php'));
        exit;
    }
}

// ── Fetch data ───────────────────────────────────────────────────────────────

$polls = $pdo->query("
    SELECT p.id, p.question, p.description, p.cover_image_url,
           p.country, p.status, p.starts_at, p.ends_at, p.sort_order,
           p.created_at,
           COUNT(DISTINCT po.id)  AS option_count,
           COUNT(DISTINCT pv.id)  AS vote_count
    FROM   polls p
    LEFT JOIN poll_options po ON po.poll_id = p.id
    LEFT JOIN poll_votes   pv ON pv.poll_id = p.id
    GROUP  BY p.id
    ORDER  BY p.sort_order ASC, p.id DESC
")->fetchAll();

// ── Edit prefill ─────────────────────────────────────────────────────────────
$edit = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($polls as $p) {
        if ((int)$p['id'] === $editId) { $edit = $p; break; }
    }
}

// ── Options manager ──────────────────────────────────────────────────────────
$managePoll    = null;
$manageOptions = [];
if (isset($_GET['manage'])) {
    $manageId = (int)$_GET['manage'];
    foreach ($polls as $p) {
        if ((int)$p['id'] === $manageId) { $managePoll = $p; break; }
    }
    if ($managePoll) {
        $stmt = $pdo->prepare("
            SELECT po.id, po.option_text, po.image_url, po.sort_order,
                   COUNT(pv.id) AS vote_count
            FROM poll_options po
            LEFT JOIN poll_votes pv ON pv.option_id = po.id
            WHERE po.poll_id = ?
            GROUP BY po.id
            ORDER BY po.sort_order ASC, po.id ASC
        ");
        $stmt->execute([$manageId]);
        $manageOptions = $stmt->fetchAll();
    }
}

// ── Stats ────────────────────────────────────────────────────────────────────
$totalPolls  = count($polls);
$activePolls = count(array_filter($polls, fn($p) => $p['status'] === 'active'));
$closedPolls = count(array_filter($polls, fn($p) => $p['status'] === 'closed'));
$totalVotes  = array_sum(array_column($polls, 'vote_count'));

require_once __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <div>
        <h1><i class="fas fa-poll" style="color:var(--orange);margin-right:8px;"></i>Polls</h1>
        <p>Create voting campaigns visible to app users under Polls & Events.</p>
    </div>
    <button class="btn btn-primary" onclick="openPollModal()">
        <i class="fas fa-plus"></i> New Poll
    </button>
</div>

<!-- ── Stats ──────────────────────────────────────────────────────────────── -->
<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));">
    <div class="stat-card">
        <div class="stat-icon si-navy"><i class="fas fa-poll"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $totalPolls; ?></div>
            <div class="stat-label">Total Polls</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-green"><i class="fas fa-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $activePolls; ?></div>
            <div class="stat-label">Active</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-purple"><i class="fas fa-lock"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $closedPolls; ?></div>
            <div class="stat-label">Closed</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-orange"><i class="fas fa-vote-yea"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($totalVotes); ?></div>
            <div class="stat-label">Total Votes Cast</div>
        </div>
    </div>
</div>

<?php if ($managePoll): ?>
<!-- ── Options Manager ────────────────────────────────────────────────────── -->
<div class="card" style="border-left:4px solid var(--purple);">
    <div class="card-header">
        <h2 style="color:var(--purple);">
            <i class="fas fa-list-ul"></i>
            Options for: "<?php echo htmlspecialchars(mb_strimwidth($managePoll['question'], 0, 70, '…')); ?>"
        </h2>
        <a href="<?php echo portal_url('polls.php'); ?>" class="btn btn-outline btn-sm">
            <i class="fas fa-times"></i> Close
        </a>
    </div>

    <?php if (empty($manageOptions)): ?>
    <div style="padding:16px 0;color:#888;font-size:14px;">
        <i class="fas fa-info-circle" style="color:var(--orange);margin-right:6px;"></i>
        No options yet. Add at least 2 options before activating this poll.
    </div>
    <?php else: ?>
    <div class="table-wrapper" style="margin-bottom:20px;">
        <table class="dt">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Option Text</th>
                    <th>Image URL</th>
                    <th style="text-align:center;">Votes</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($manageOptions as $i => $opt): ?>
            <tr>
                <td style="color:#aaa;font-size:12px;"><?php echo $i + 1; ?></td>
                <td style="font-weight:600;color:var(--navy);">
                    <?php if ($opt['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($opt['image_url']); ?>"
                         style="width:28px;height:28px;border-radius:50%;object-fit:cover;
                                vertical-align:middle;margin-right:8px;border:1px solid #e5e7eb;">
                    <?php endif; ?>
                    <?php echo htmlspecialchars($opt['option_text']); ?>
                </td>
                <td style="font-size:12px;color:#888;">
                    <?php echo $opt['image_url']
                        ? htmlspecialchars(mb_strimwidth($opt['image_url'], 0, 50, '…'))
                        : '<span style="color:#ddd;">—</span>'; ?>
                </td>
                <td style="text-align:center;font-weight:700;color:var(--navy);">
                    <?php echo number_format($opt['vote_count']); ?>
                </td>
                <td>
                    <?php if ($opt['vote_count'] == 0): ?>
                    <form method="post" style="display:inline;"
                          onsubmit="return confirm('Remove this option?')">
                        <?php echo portal_csrf_field(); ?>
                        <input type="hidden" name="_action"  value="delete_option">
                        <input type="hidden" name="option_id" value="<?php echo (int)$opt['id']; ?>">
                        <input type="hidden" name="poll_id"   value="<?php echo (int)$managePoll['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    <?php else: ?>
                    <span style="font-size:12px;color:#bbb;" title="Cannot remove — has votes">
                        <i class="fas fa-lock"></i>
                    </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Add new option form -->
    <div style="background:#f9fafb;border-radius:10px;padding:16px;">
        <div style="font-weight:700;font-size:13px;color:var(--navy);margin-bottom:12px;">
            <i class="fas fa-plus-circle" style="color:var(--purple);margin-right:6px;"></i>
            Add New Option
        </div>
        <form method="post" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <?php echo portal_csrf_field(); ?>
            <input type="hidden" name="_action" value="add_option">
            <input type="hidden" name="poll_id" value="<?php echo (int)$managePoll['id']; ?>">
            <div style="flex:2;min-width:200px;">
                <label class="form-label" style="font-size:12px;">Option Text <span class="req">*</span></label>
                <input type="text" name="option_text" class="form-control"
                       placeholder="e.g. Candidate A" required maxlength="255">
            </div>
            <div style="flex:2;min-width:200px;">
                <label class="form-label" style="font-size:12px;">Photo URL <span style="color:#aaa;font-weight:400;">(optional)</span></label>
                <input type="url" name="image_url" class="form-control"
                       placeholder="https://… (candidate photo or icon)">
            </div>
            <div>
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-plus"></i> Add Option
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ── Polls table ─────────────────────────────────────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> All Polls</h2>
    </div>

    <?php if (empty($polls)): ?>
    <div class="empty-state">
        <i class="fas fa-poll"></i>
        <h3>No polls yet</h3>
        <p>Create your first poll to start collecting votes from app users.</p>
        <button class="btn btn-primary" onclick="openPollModal()">
            <i class="fas fa-plus"></i> New Poll
        </button>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Question</th>
                    <th>Country</th>
                    <th style="text-align:center;">Options</th>
                    <th style="text-align:center;">Votes</th>
                    <th>Schedule</th>
                    <th style="text-align:center;">Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($polls as $p):
                $countries = portal_countries();
                $cLabel = $countries[strtoupper($p['country'])] ?? strtoupper($p['country']);
            ?>
            <tr>
                <td style="color:#aaa;font-size:12px;"><?php echo (int)$p['id']; ?></td>
                <td style="max-width:260px;">
                    <?php if ($p['cover_image_url']): ?>
                    <img src="<?php echo htmlspecialchars($p['cover_image_url']); ?>"
                         style="width:42px;height:28px;border-radius:6px;object-fit:cover;
                                vertical-align:middle;margin-right:8px;border:1px solid #e5e7eb;">
                    <?php endif; ?>
                    <span style="font-weight:700;color:var(--navy);font-size:13px;">
                        <?php echo htmlspecialchars(mb_strimwidth($p['question'], 0, 80, '…')); ?>
                    </span>
                </td>
                <td style="font-size:12px;"><?php echo htmlspecialchars($cLabel); ?></td>
                <td style="text-align:center;">
                    <span style="font-weight:700;color:<?php echo $p['option_count'] < 2 ? '#ef4444' : 'var(--navy)'; ?>">
                        <?php echo (int)$p['option_count']; ?>
                    </span>
                    <?php if ($p['option_count'] < 2): ?>
                    <span style="font-size:10px;color:#ef4444;display:block;">needs 2+</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;font-weight:700;color:var(--navy);">
                    <?php echo number_format($p['vote_count']); ?>
                </td>
                <td style="font-size:12px;color:#888;">
                    <?php if ($p['ends_at']): ?>
                    <div>Ends: <?php echo date('d M Y', strtotime($p['ends_at'])); ?></div>
                    <?php else: ?>
                    <span style="color:#bbb;">No expiry</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <?php
                    $badgeMap = [
                        'active' => 'badge-active',
                        'closed' => 'badge-archived',
                        'draft'  => 'badge-draft',
                    ];
                    ?>
                    <span class="badge <?php echo $badgeMap[$p['status']] ?? 'badge-draft'; ?>">
                        <?php echo ucfirst($p['status']); ?>
                    </span>
                </td>
                <td>
                    <div style="display:flex;gap:5px;flex-wrap:nowrap;">
                        <!-- Manage options -->
                        <a href="?manage=<?php echo (int)$p['id']; ?>"
                           class="btn btn-outline btn-sm" title="Manage Options">
                            <i class="fas fa-list-ul"></i>
                        </a>
                        <!-- Edit meta -->
                        <a href="?edit=<?php echo (int)$p['id']; ?>"
                           class="btn btn-outline btn-sm" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        <!-- Status change -->
                        <?php if ($p['status'] !== 'active'): ?>
                        <form method="post" style="display:inline;">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="_action" value="set_status">
                            <input type="hidden" name="id"     value="<?php echo (int)$p['id']; ?>">
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="btn btn-success btn-sm" title="Activate">
                                <i class="fas fa-play"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                        <?php if ($p['status'] === 'active'): ?>
                        <form method="post" style="display:inline;">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="_action" value="set_status">
                            <input type="hidden" name="id"     value="<?php echo (int)$p['id']; ?>">
                            <input type="hidden" name="status" value="closed">
                            <button type="submit" class="btn btn-warning btn-sm" title="Close Poll">
                                <i class="fas fa-stop"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                        <!-- Delete (only if 0 votes) -->
                        <?php if ($p['vote_count'] == 0): ?>
                        <form method="post" style="display:inline;"
                              onsubmit="return confirm('Delete this poll permanently?')">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="_action" value="delete_poll">
                            <input type="hidden" name="id"     value="<?php echo (int)$p['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     CREATE / EDIT POLL MODAL
══════════════════════════════════════════════════════════════════════════════ -->
<div id="pollModal" style="
    display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
    z-index:1000;overflow-y:auto;padding:40px 20px;">
    <div style="
        background:#fff;border-radius:16px;max-width:640px;
        margin:0 auto;padding:0;box-shadow:0 20px 60px rgba(0,0,0,.2);">

        <!-- Header -->
        <div style="
            background:linear-gradient(135deg,#7c3aed,#a78bfa);
            padding:24px 28px;border-radius:16px 16px 0 0;
            display:flex;align-items:center;justify-content:space-between;">
            <div>
                <h2 style="color:#fff;font-size:18px;font-weight:800;margin:0;">
                    <i class="fas fa-poll" style="margin-right:8px;"></i>
                    <span id="pollModalTitle">New Poll</span>
                </h2>
                <p style="color:rgba(255,255,255,.7);font-size:13px;margin:4px 0 0;">
                    After saving, use the <i class="fas fa-list-ul"></i> button to add vote options
                </p>
            </div>
            <button onclick="closePollModal()" style="
                background:rgba(255,255,255,.2);border:none;color:#fff;
                width:36px;height:36px;border-radius:50%;cursor:pointer;
                font-size:16px;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Body -->
        <form method="post" style="padding:28px;">
            <?php echo portal_csrf_field(); ?>
            <input type="hidden" name="_action" value="save_poll">
            <input type="hidden" name="id" id="pollId" value="0">

            <!-- Question -->
            <div class="form-group">
                <label class="form-label">Question <span class="req">*</span></label>
                <textarea name="question" id="fQuestion" class="form-control"
                          rows="2" maxlength="500" required
                          placeholder="Who should be the next class president?"></textarea>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label class="form-label">Description <span style="color:#aaa;font-weight:400;">(optional)</span></label>
                <textarea name="description" id="fDescription" class="form-control"
                          rows="2" placeholder="Additional context shown below the question"></textarea>
            </div>

            <!-- Cover image -->
            <div class="form-group">
                <label class="form-label">Cover Image URL <span style="color:#aaa;font-weight:400;">(optional)</span></label>
                <input type="url" name="cover_image_url" id="fCoverUrl"
                       class="form-control" placeholder="https://… banner image for the poll card">
            </div>

            <!-- Country + Status + Sort -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Country <span class="req">*</span></label>
                    <select name="country" id="fPollCountry" class="form-control">
                        <?php foreach (portal_countries() as $code => $label): ?>
                        <option value="<?php echo strtolower($code); ?>">
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="fPollStatus" class="form-control">
                        <option value="draft">Draft (hidden)</option>
                        <option value="active">Active (visible)</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>

            <!-- Schedule -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="datetime-local" name="starts_at" id="fPollStartsAt" class="form-control">
                    <div class="form-hint">Leave blank to start immediately</div>
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="datetime-local" name="ends_at" id="fPollEndsAt" class="form-control">
                    <div class="form-hint">Leave blank for no expiry</div>
                </div>
            </div>

            <!-- Sort order -->
            <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" id="fPollSort"
                       class="form-control" value="0" min="0" max="255" style="max-width:160px;">
                <div class="form-hint">Lower = shown first in the app (0 = first)</div>
            </div>

            <!-- Initial options (for new polls only, shown/hidden by JS) -->
            <div id="initialOptionsSection">
                <div style="
                    background:#f5f3ff;border:1.5px solid #ddd6fe;
                    border-radius:10px;padding:16px;margin-bottom:20px;">
                    <div style="font-weight:700;font-size:13px;color:#7c3aed;margin-bottom:12px;">
                        <i class="fas fa-list-ul" style="margin-right:6px;"></i>
                        Initial Options
                        <span style="font-weight:400;color:#888;font-size:12px;margin-left:6px;">
                            (at least 2 required to activate)
                        </span>
                    </div>
                    <div id="optionRows">
                        <div class="option-row" style="display:flex;gap:8px;margin-bottom:8px;">
                            <input type="text" name="options[0][text]"
                                   class="form-control" placeholder="Option 1" style="flex:2;">
                            <input type="url" name="options[0][image_url]"
                                   class="form-control" placeholder="Photo URL (optional)" style="flex:1;">
                        </div>
                        <div class="option-row" style="display:flex;gap:8px;margin-bottom:8px;">
                            <input type="text" name="options[1][text]"
                                   class="form-control" placeholder="Option 2" style="flex:2;">
                            <input type="url" name="options[1][image_url]"
                                   class="form-control" placeholder="Photo URL (optional)" style="flex:1;">
                        </div>
                    </div>
                    <button type="button" onclick="addOptionRow()" class="btn btn-outline btn-sm" style="margin-top:4px;">
                        <i class="fas fa-plus"></i> Add Row
                    </button>
                </div>
            </div>

            <!-- Buttons -->
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closePollModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#7c3aed;border-color:#7c3aed;">
                    <i class="fas fa-save"></i> Save Poll
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
let optionIndex = 2;

function addOptionRow() {
    const container = document.getElementById('optionRows');
    const div = document.createElement('div');
    div.className = 'option-row';
    div.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;';
    div.innerHTML = `
        <input type="text" name="options[${optionIndex}][text]"
               class="form-control" placeholder="Option ${optionIndex + 1}" style="flex:2;">
        <input type="url" name="options[${optionIndex}][image_url]"
               class="form-control" placeholder="Photo URL (optional)" style="flex:1;">
        <button type="button" onclick="this.parentElement.remove()"
                class="btn btn-danger btn-sm" style="flex-shrink:0;">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
    optionIndex++;
}

function openPollModal(data) {
    const isEdit = !!data;
    document.getElementById('pollModalTitle').textContent = isEdit ? 'Edit Poll' : 'New Poll';
    document.getElementById('pollId').value          = data?.id         || '0';
    document.getElementById('fQuestion').value       = data?.question   || '';
    document.getElementById('fDescription').value    = data?.description || '';
    document.getElementById('fCoverUrl').value       = data?.cover_image_url || '';
    document.getElementById('fPollCountry').value    = data?.country    || 'ug';
    document.getElementById('fPollStatus').value     = data?.status     || 'draft';
    document.getElementById('fPollStartsAt').value   = data?.starts_at  ? data.starts_at.slice(0,16) : '';
    document.getElementById('fPollEndsAt').value     = data?.ends_at    ? data.ends_at.slice(0,16)   : '';
    document.getElementById('fPollSort').value       = data?.sort_order || '0';

    // Hide initial options section when editing
    document.getElementById('initialOptionsSection').style.display = isEdit ? 'none' : 'block';

    document.getElementById('pollModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closePollModal() {
    document.getElementById('pollModal').style.display = 'none';
    document.body.style.overflow = '';
    if (history.replaceState) {
        history.replaceState(null, '', '<?php echo portal_url('polls.php'); ?>');
    }
}

document.getElementById('pollModal').addEventListener('click', function(e) {
    if (e.target === this) closePollModal();
});

<?php if ($edit): ?>
openPollModal(<?php echo json_encode([
    'id'              => $edit['id'],
    'question'        => $edit['question'],
    'description'     => $edit['description'],
    'cover_image_url' => $edit['cover_image_url'],
    'country'         => $edit['country'],
    'status'          => $edit['status'],
    'starts_at'       => $edit['starts_at'],
    'ends_at'         => $edit['ends_at'],
    'sort_order'      => $edit['sort_order'],
]); ?>);
<?php endif; ?>
</script>

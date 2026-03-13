<?php
/**
 * KandaNews Africa — Quotes Management
 *
 * Manage the Quote of the Day pool served by GET /misc/quote.
 * One quote is shown per day (deterministically chosen by day-of-year index).
 *
 * Actions: add / edit / toggle active / delete
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$pdo        = portal_db();
$page_title = 'Quotes';

// ── Action handlers ──────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!portal_verify_csrf()) {
        portal_flash('error', 'Invalid request. Please try again.');
        header('Location: ' . portal_url('quotes.php'));
        exit;
    }

    $action = $_POST['_action'] ?? '';

    // ── Save (create or update) ──────────────────────────────────────────────
    if ($action === 'save') {
        $id     = (int)($_POST['id'] ?? 0);
        $quote  = trim($_POST['quote'] ?? '');
        $author = trim($_POST['author'] ?? '') ?: null;
        $active = isset($_POST['active']) ? 1 : 0;

        if (!$quote) {
            portal_flash('error', 'Quote text is required.');
            header('Location: ' . portal_url('quotes.php') . ($id ? "?edit=$id" : '?new=1'));
            exit;
        }

        try {
            if ($id > 0) {
                $pdo->prepare("UPDATE quotes SET quote = ?, author = ?, active = ? WHERE id = ?")
                    ->execute([$quote, $author, $active, $id]);
                portal_flash('success', 'Quote updated.');
            } else {
                $pdo->prepare("INSERT INTO quotes (quote, author, active) VALUES (?, ?, ?)")
                    ->execute([$quote, $author, $active]);
                portal_flash('success', 'Quote added.');
            }
        } catch (PDOException $e) {
            portal_flash('error', 'Database error: ' . $e->getMessage());
        }
        header('Location: ' . portal_url('quotes.php'));
        exit;

    // ── Toggle active ────────────────────────────────────────────────────────
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE quotes SET active = 1 - active WHERE id = ?")
                ->execute([$id]);
            portal_flash('success', 'Quote status updated.');
        }
        header('Location: ' . portal_url('quotes.php'));
        exit;

    // ── Delete ───────────────────────────────────────────────────────────────
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Warn if this would leave zero active quotes
            $activeCount = (int) $pdo->query("SELECT COUNT(*) FROM quotes WHERE active = 1")->fetchColumn();
            $isActive    = (int) $pdo->prepare("SELECT active FROM quotes WHERE id = ?")->execute([$id])
                                    ? (int) $pdo->query("SELECT active FROM quotes WHERE id = $id")->fetchColumn()
                                    : 0;
            if ($isActive && $activeCount <= 1) {
                portal_flash('error', 'Cannot delete the last active quote — the app needs at least one.');
                header('Location: ' . portal_url('quotes.php'));
                exit;
            }
            $pdo->prepare("DELETE FROM quotes WHERE id = ?")->execute([$id]);
            portal_flash('success', 'Quote deleted.');
        }
        header('Location: ' . portal_url('quotes.php'));
        exit;
    }
}

// ── Load data ────────────────────────────────────────────────────────────────

$quotes = $pdo->query("SELECT * FROM quotes ORDER BY active DESC, id ASC")->fetchAll();

$activeCount   = 0;
$inactiveCount = 0;
foreach ($quotes as $q) {
    if ($q['active']) $activeCount++; else $inactiveCount++;
}

// For "today's pick" preview
$todayIndex   = count($quotes) > 0 ? (int) date('z') % count($quotes) : -1;
$todayQuoteId = $todayIndex >= 0 ? (int) $quotes[$todayIndex]['id'] : 0;

// ── Edit pre-fill ────────────────────────────────────────────────────────────
$edit     = null;
$openNew  = isset($_GET['new']);
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM quotes WHERE id = ?");
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$flash = portal_get_flash();

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page header ──────────────────────────────────────────────────────────── -->
<div class="section-header" style="margin-bottom:20px;">
    <div>
        <h1><i class="fas fa-quote-left" style="color:var(--orange);margin-right:8px;"></i>Quotes</h1>
        <p>Quote of the Day pool — one is served to the app per day, deterministically picked by date.</p>
    </div>
    <button onclick="openModal(null)" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Quote
    </button>
</div>

<!-- ── Flash ────────────────────────────────────────────────────────────────── -->
<?php if ($flash): ?>
<div class="flash flash-<?php echo $flash['type']; ?>" style="margin-bottom:16px;">
    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo htmlspecialchars($flash['message']); ?>
</div>
<?php endif; ?>

<!-- ── Stats strip ─────────────────────────────────────────────────────────── -->
<div style="display:flex;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
    <div class="stat-card" style="flex:1;min-width:160px;cursor:default;">
        <div class="stat-icon si-green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $activeCount; ?></div>
            <div class="stat-label">Active Quotes</div>
        </div>
    </div>
    <div class="stat-card" style="flex:1;min-width:160px;cursor:default;">
        <div class="stat-icon si-orange"><i class="fas fa-pause-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $inactiveCount; ?></div>
            <div class="stat-label">Inactive</div>
        </div>
    </div>
    <div class="stat-card" style="flex:1;min-width:160px;cursor:default;">
        <div class="stat-icon si-navy"><i class="fas fa-layer-group"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo count($quotes); ?></div>
            <div class="stat-label">Total Quotes</div>
        </div>
    </div>
    <div class="stat-card" style="flex:1;min-width:260px;cursor:default;background:linear-gradient(135deg,#fdf6ec,#fff9f4);border:1.5px solid #fde8cc;">
        <div class="stat-icon si-orange"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-info">
            <div class="stat-value" style="font-size:14px;line-height:1.3;">
                Today's Pick
            </div>
            <div class="stat-label" style="font-size:11px;color:#888;">
                Day <?php echo date('z') + 1; ?> of year &rarr; index <?php echo $todayIndex; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Quotes table ─────────────────────────────────────────────────────────── -->
<div class="card">
    <?php if (empty($quotes)): ?>
    <div class="empty-state">
        <i class="fas fa-quote-left"></i>
        <h3>No quotes yet</h3>
        <p>Add your first quote to power the Quote of the Day feature.</p>
        <button onclick="openModal(null)" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Quote
        </button>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Quote</th>
                    <th>Author</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Today?</th>
                    <th>Added</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($quotes as $i => $q): ?>
            <tr style="<?php echo !$q['active'] ? 'opacity:.55;' : ''; ?>">
                <td style="color:#aaa;font-size:12px;"><?php echo (int)$q['id']; ?></td>
                <td style="max-width:460px;">
                    <div style="font-size:13px;color:#1e2b42;font-style:italic;line-height:1.5;">
                        &ldquo;<?php echo htmlspecialchars($q['quote']); ?>&rdquo;
                    </div>
                </td>
                <td style="font-size:13px;color:#555;white-space:nowrap;">
                    <?php echo $q['author'] ? htmlspecialchars($q['author']) : '<span style="color:#bbb;">—</span>'; ?>
                </td>
                <td style="text-align:center;">
                    <?php if ($q['active']): ?>
                        <span class="badge badge-published">Active</span>
                    <?php else: ?>
                        <span class="badge badge-archived">Inactive</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <?php if ((int)$q['id'] === $todayQuoteId && $q['active']): ?>
                        <span title="This is today's Quote of the Day" style="color:var(--orange);font-size:16px;">
                            <i class="fas fa-sun"></i>
                        </span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;color:#aaa;white-space:nowrap;">
                    <?php echo date('d M Y', strtotime($q['created_at'])); ?>
                </td>
                <td>
                    <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:nowrap;">
                        <!-- Edit -->
                        <button onclick='openModal(<?php echo json_encode([
                            "id"     => (int)$q['id'],
                            "quote"  => $q['quote'],
                            "author" => $q['author'] ?? '',
                            "active" => (int)$q['active'],
                        ]); ?>)' class="btn btn-outline btn-sm" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <!-- Toggle -->
                        <form method="post" style="display:inline;">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="_action" value="toggle">
                            <input type="hidden" name="id" value="<?php echo (int)$q['id']; ?>">
                            <button type="submit"
                                class="btn btn-sm <?php echo $q['active'] ? 'btn-warning' : 'btn-success'; ?>"
                                title="<?php echo $q['active'] ? 'Deactivate' : 'Activate'; ?>">
                                <i class="fas fa-<?php echo $q['active'] ? 'pause' : 'play'; ?>"></i>
                            </button>
                        </form>
                        <!-- Delete -->
                        <form method="post" style="display:inline;"
                              onsubmit="return confirm('Delete this quote? This cannot be undone.');">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="_action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$q['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     ADD / EDIT MODAL
════════════════════════════════════════════════════════════════════════ -->
<div id="quoteModal" style="
    display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
    z-index:1000;overflow-y:auto;padding:40px 20px;">
    <div style="
        background:#fff;border-radius:16px;max-width:560px;
        margin:0 auto;box-shadow:0 20px 60px rgba(0,0,0,.2);">

        <!-- Modal header -->
        <div style="
            background:linear-gradient(135deg,var(--navy),var(--navy-l));
            padding:22px 28px;border-radius:16px 16px 0 0;
            display:flex;align-items:center;justify-content:space-between;">
            <div>
                <h2 style="color:#fff;font-size:18px;font-weight:800;margin:0;">
                    <i class="fas fa-quote-left" style="color:var(--orange);margin-right:8px;"></i>
                    <span id="modalTitle">New Quote</span>
                </h2>
                <p style="color:rgba(255,255,255,.6);font-size:13px;margin:4px 0 0;">
                    Added to the daily rotation for the app home screen
                </p>
            </div>
            <button onclick="closeModal()" style="
                background:rgba(255,255,255,.15);border:none;color:#fff;
                width:36px;height:36px;border-radius:50%;cursor:pointer;
                font-size:16px;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Modal body -->
        <form method="post" style="padding:28px;">
            <?php echo portal_csrf_field(); ?>
            <input type="hidden" name="_action" value="save">
            <input type="hidden" name="id" id="quoteId" value="0">

            <div class="form-group" style="margin-bottom:18px;">
                <label class="form-label">
                    Quote Text <span class="req">*</span>
                </label>
                <textarea name="quote" id="fQuote" class="form-control"
                          rows="4" maxlength="800" required
                          placeholder="Enter the quote text here…"
                          style="resize:vertical;"></textarea>
                <div class="form-hint">
                    <span id="quoteCharCount">0</span>/800 characters
                </div>
            </div>

            <div class="form-group" style="margin-bottom:18px;">
                <label class="form-label">Author</label>
                <input type="text" name="author" id="fAuthor" class="form-control"
                       maxlength="200" placeholder="e.g. Nelson Mandela, African Proverb">
                <div class="form-hint">Leave blank to show no attribution</div>
            </div>

            <div class="form-group" style="display:flex;align-items:center;gap:10px;margin-bottom:24px;">
                <input type="checkbox" name="active" id="fActive" value="1" checked
                       style="width:18px;height:18px;cursor:pointer;">
                <label for="fActive" class="form-label" style="margin:0;cursor:pointer;">
                    Active — include in the daily rotation
                </label>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Quote
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
function openModal(data) {
    const modal = document.getElementById('quoteModal');
    if (data) {
        document.getElementById('modalTitle').textContent = 'Edit Quote';
        document.getElementById('quoteId').value          = data.id;
        document.getElementById('fQuote').value           = data.quote;
        document.getElementById('fAuthor').value          = data.author || '';
        document.getElementById('fActive').checked        = data.active == 1;
    } else {
        document.getElementById('modalTitle').textContent = 'New Quote';
        document.getElementById('quoteId').value          = '0';
        document.getElementById('fQuote').value           = '';
        document.getElementById('fAuthor').value          = '';
        document.getElementById('fActive').checked        = true;
    }
    updateCharCount();
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    document.getElementById('fQuote').focus();
}

function closeModal() {
    document.getElementById('quoteModal').style.display = 'none';
    document.body.style.overflow = '';
    if (history.replaceState) {
        history.replaceState(null, '', '<?php echo portal_url('quotes.php'); ?>');
    }
}

document.getElementById('quoteModal').addEventListener('click', function (e) {
    if (e.target === this) closeModal();
});

function updateCharCount() {
    const len = document.getElementById('fQuote').value.length;
    const el  = document.getElementById('quoteCharCount');
    el.textContent = len;
    el.style.color = len > 700 ? 'var(--orange)' : '#888';
}
document.getElementById('fQuote').addEventListener('input', updateCharCount);

<?php if ($edit): ?>
openModal(<?php echo json_encode([
    'id'     => (int)$edit['id'],
    'quote'  => $edit['quote'],
    'author' => $edit['author'] ?? '',
    'active' => (int)$edit['active'],
]); ?>);
<?php elseif ($openNew): ?>
openModal(null);
<?php endif; ?>
</script>

<?php
/**
 * KandaNews Africa — Events Management
 *
 * Create / edit / publish / cancel events shown in the app's Polls & Events tab.
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$pdo        = portal_db();
$page_title = 'Events';

// ── Action handlers ──────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!portal_verify_csrf()) {
        portal_flash('error', 'Invalid request. Please try again.');
        header('Location: ' . portal_url('events.php'));
        exit;
    }

    $action = $_POST['_action'] ?? '';

    // ── Save event (create or update) ────────────────────────────────────────
    if ($action === 'save') {
        $id               = (int)($_POST['id'] ?? 0);
        $title            = trim($_POST['title'] ?? '');
        $description      = trim($_POST['description'] ?? '') ?: null;
        $event_date       = trim($_POST['event_date'] ?? '');
        $end_date         = trim($_POST['end_date'] ?? '') ?: null;
        $location         = trim($_POST['location'] ?? '') ?: null;
        $is_online        = isset($_POST['is_online'])  ? 1 : 0;
        $is_free          = isset($_POST['is_free'])    ? 1 : 0;
        $registration_url = trim($_POST['registration_url'] ?? '') ?: null;
        $cover_image_url  = trim($_POST['cover_image_url'] ?? '') ?: null;
        $country          = strtolower(trim($_POST['country'] ?? 'ug'));
        $category         = $_POST['category'] ?? 'other';
        $status           = $_POST['status'] ?? 'draft';

        $valid_cats = ['conference','webinar','workshop','networking','launch','other'];
        $valid_stat = ['draft','published','cancelled'];
        if (!in_array($category, $valid_cats, true)) $category = 'other';
        if (!in_array($status,   $valid_stat, true)) $status   = 'draft';
        if (!in_array($country,  ['ug','ke','ng','za'], true)) $country = 'ug';

        if (!$title || !$event_date) {
            portal_flash('error', 'Title and event date are required.');
            header('Location: ' . portal_url('events.php'));
            exit;
        }

        if ($id > 0) {
            $pdo->prepare("
                UPDATE events
                SET title=?, description=?, event_date=?, end_date=?, location=?,
                    is_online=?, is_free=?, registration_url=?, cover_image_url=?,
                    country=?, category=?, status=?
                WHERE id=?
            ")->execute([
                $title, $description, $event_date, $end_date, $location,
                $is_online, $is_free, $registration_url, $cover_image_url,
                $country, $category, $status, $id,
            ]);
            portal_flash('success', 'Event updated.');
        } else {
            $pdo->prepare("
                INSERT INTO events
                    (title, description, event_date, end_date, location,
                     is_online, is_free, registration_url, cover_image_url,
                     country, category, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $title, $description, $event_date, $end_date, $location,
                $is_online, $is_free, $registration_url, $cover_image_url,
                $country, $category, $status,
            ]);
            portal_flash('success', 'Event created.');
        }

        header('Location: ' . portal_url('events.php'));
        exit;
    }

    // ── Change event status ───────────────────────────────────────────────────
    if ($action === 'set_status') {
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if ($id && in_array($status, ['draft', 'published', 'cancelled'], true)) {
            $pdo->prepare("UPDATE events SET status=? WHERE id=?")->execute([$status, $id]);
            portal_flash('success', 'Event status updated.');
        }
        header('Location: ' . portal_url('events.php'));
        exit;
    }

    // ── Delete event ──────────────────────────────────────────────────────────
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("DELETE FROM events WHERE id=?")->execute([$id]);
            portal_flash('success', 'Event deleted.');
        }
        header('Location: ' . portal_url('events.php'));
        exit;
    }
}

// ── Fetch data ───────────────────────────────────────────────────────────────

$filter_status  = $_GET['status'] ?? 'all';
$filter_country = $_GET['country'] ?? 'all';

$where  = '1=1';
$params = [];

if ($filter_status !== 'all') {
    $where   .= ' AND status = ?';
    $params[] = $filter_status;
}
if ($filter_country !== 'all') {
    $where   .= ' AND country = ?';
    $params[] = $filter_country;
}

$stmt = $pdo->prepare("
    SELECT id, title, description, event_date, end_date, location,
           is_online, is_free, registration_url, cover_image_url,
           country, category, status, created_at
    FROM   events
    WHERE  $where
    ORDER  BY
        CASE WHEN event_date >= NOW() THEN 0 ELSE 1 END ASC,
        event_date ASC
");
$stmt->execute($params);
$events = $stmt->fetchAll();

// ── Edit prefill ─────────────────────────────────────────────────────────────
$edit = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($events as $e) {
        if ((int)$e['id'] === $editId) { $edit = $e; break; }
    }
    if (!$edit) {
        // Could be filtered out — fetch directly
        $s = $pdo->prepare("SELECT * FROM events WHERE id=?");
        $s->execute([$editId]);
        $edit = $s->fetch() ?: null;
    }
}

// ── Stats ─────────────────────────────────────────────────────────────────────
$allEvents   = $pdo->query("SELECT status, event_date FROM events")->fetchAll();
$totalEvents = count($allEvents);
$published   = count(array_filter($allEvents, fn($e) => $e['status'] === 'published'));
$upcoming    = count(array_filter($allEvents,
    fn($e) => $e['status'] === 'published' && strtotime($e['event_date']) >= time()));
$past        = count(array_filter($allEvents,
    fn($e) => $e['status'] === 'published' && strtotime($e['event_date']) < time()));

require_once __DIR__ . '/includes/header.php';

$categories = [
    'conference'  => 'Conference',
    'webinar'     => 'Webinar',
    'workshop'    => 'Workshop',
    'networking'  => 'Networking',
    'launch'      => 'Launch',
    'other'       => 'Other',
];
$categoryColors = [
    'conference' => '#2563eb', 'webinar'    => '#7c3aed',
    'workshop'   => '#d97706', 'networking' => '#059669',
    'launch'     => '#f05a1a', 'other'      => '#6b7280',
];
?>

<div class="section-header">
    <div>
        <h1><i class="fas fa-calendar-alt" style="color:var(--orange);margin-right:8px;"></i>Events</h1>
        <p>Manage upcoming and past events visible in the app's Polls & Events tab.</p>
    </div>
    <button class="btn btn-primary" onclick="openEventModal()">
        <i class="fas fa-plus"></i> New Event
    </button>
</div>

<!-- ── Stats ──────────────────────────────────────────────────────────────── -->
<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));">
    <div class="stat-card">
        <div class="stat-icon si-navy"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $totalEvents; ?></div>
            <div class="stat-label">Total Events</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-green"><i class="fas fa-eye"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $published; ?></div>
            <div class="stat-label">Published</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-orange"><i class="fas fa-arrow-right"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $upcoming; ?></div>
            <div class="stat-label">Upcoming</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-blue"><i class="fas fa-history"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $past; ?></div>
            <div class="stat-label">Past</div>
        </div>
    </div>
</div>

<!-- ── Filters ────────────────────────────────────────────────────────────── -->
<div class="filter-bar">
    <span style="font-size:13px;font-weight:600;color:#888;">Filter:</span>
    <?php
    $statuses = ['all' => 'All', 'published' => 'Published', 'draft' => 'Draft', 'cancelled' => 'Cancelled'];
    foreach ($statuses as $val => $lbl):
        $active = $filter_status === $val ? ' active' : '';
        $url = portal_url("events.php?status=$val&country=$filter_country");
    ?>
    <a href="<?php echo $url; ?>" class="filter-chip<?php echo $active; ?>"><?php echo $lbl; ?></a>
    <?php endforeach; ?>

    <span style="margin-left:12px;font-size:13px;font-weight:600;color:#888;">Country:</span>
    <a href="<?php echo portal_url("events.php?status=$filter_status&country=all"); ?>"
       class="filter-chip<?php echo $filter_country === 'all' ? ' active' : ''; ?>">All</a>
    <?php foreach (portal_countries() as $code => $lbl):
        $active = strtolower($filter_country) === strtolower($code) ? ' active' : '';
        $url = portal_url("events.php?status=$filter_status&country=" . strtolower($code));
    ?>
    <a href="<?php echo $url; ?>" class="filter-chip<?php echo $active; ?>">
        <?php echo htmlspecialchars($lbl); ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- ── Events table ───────────────────────────────────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Events
            <span style="font-size:13px;font-weight:400;color:#888;margin-left:6px;">
                (<?php echo count($events); ?> shown)
            </span>
        </h2>
    </div>

    <?php if (empty($events)): ?>
    <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <h3>No events found</h3>
        <p>Create your first event to list it in the app.</p>
        <button class="btn btn-primary" onclick="openEventModal()">
            <i class="fas fa-plus"></i> New Event
        </button>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Country</th>
                    <th style="text-align:center;">Flags</th>
                    <th style="text-align:center;">Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($events as $e):
                $isPast = strtotime($e['event_date']) < time();
                $catColor = $categoryColors[$e['category']] ?? '#6b7280';
                $catLabel = $categories[$e['category']] ?? $e['category'];
                $countryLabel = portal_countries()[strtoupper($e['country'])] ?? strtoupper($e['country']);
            ?>
            <tr style="<?php echo $isPast ? 'opacity:.65;' : ''; ?>">
                <td style="color:#aaa;font-size:12px;"><?php echo (int)$e['id']; ?></td>
                <td style="max-width:240px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <?php if ($e['cover_image_url']): ?>
                        <img src="<?php echo htmlspecialchars($e['cover_image_url']); ?>"
                             style="width:48px;height:32px;border-radius:6px;object-fit:cover;
                                    flex-shrink:0;border:1px solid #e5e7eb;">
                        <?php else: ?>
                        <div style="width:48px;height:32px;border-radius:6px;flex-shrink:0;
                             background:<?php echo $catColor; ?>22;
                             display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-calendar-alt" style="color:<?php echo $catColor; ?>;font-size:14px;"></i>
                        </div>
                        <?php endif; ?>
                        <div>
                            <div style="font-weight:700;color:var(--navy);font-size:13px;">
                                <?php echo htmlspecialchars(mb_strimwidth($e['title'], 0, 60, '…')); ?>
                            </div>
                            <span style="
                                font-size:10px;font-weight:700;letter-spacing:.3px;
                                color:<?php echo $catColor; ?>;text-transform:uppercase;">
                                <?php echo htmlspecialchars($catLabel); ?>
                            </span>
                        </div>
                    </div>
                </td>
                <td style="font-size:12px;white-space:nowrap;">
                    <div style="font-weight:600;color:var(--navy);">
                        <?php echo date('d M Y', strtotime($e['event_date'])); ?>
                    </div>
                    <div style="color:#888;">
                        <?php echo date('g:i A', strtotime($e['event_date'])); ?>
                    </div>
                    <?php if ($isPast): ?>
                    <span style="font-size:10px;color:#9ca3af;font-weight:600;">PAST</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;color:#555;max-width:140px;">
                    <?php if ($e['is_online']): ?>
                    <i class="fas fa-video" style="color:var(--orange);margin-right:4px;"></i>Online
                    <?php elseif ($e['location']): ?>
                    <i class="fas fa-map-marker-alt" style="color:var(--orange);margin-right:4px;"></i>
                    <?php echo htmlspecialchars(mb_strimwidth($e['location'], 0, 30, '…')); ?>
                    <?php else: ?>
                    <span style="color:#bbb;">—</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;"><?php echo htmlspecialchars($countryLabel); ?></td>
                <td style="text-align:center;">
                    <div style="display:flex;gap:4px;justify-content:center;flex-wrap:wrap;">
                        <?php if ($e['is_free']): ?>
                        <span style="font-size:10px;font-weight:700;padding:2px 6px;
                              background:#dcfce7;color:#15803d;border-radius:4px;">FREE</span>
                        <?php endif; ?>
                        <?php if ($e['is_online']): ?>
                        <span style="font-size:10px;font-weight:700;padding:2px 6px;
                              background:#fff7ed;color:#c2410c;border-radius:4px;">ONLINE</span>
                        <?php endif; ?>
                        <?php if ($e['registration_url']): ?>
                        <span style="font-size:10px;font-weight:700;padding:2px 6px;
                              background:#eff6ff;color:#1d4ed8;border-radius:4px;">REG</span>
                        <?php endif; ?>
                    </div>
                </td>
                <td style="text-align:center;">
                    <?php
                    $sMap = ['published'=>'badge-published','draft'=>'badge-draft','cancelled'=>'badge-archived'];
                    ?>
                    <span class="badge <?php echo $sMap[$e['status']] ?? 'badge-draft'; ?>">
                        <?php echo ucfirst($e['status']); ?>
                    </span>
                </td>
                <td>
                    <div style="display:flex;gap:5px;flex-wrap:nowrap;">
                        <!-- Edit -->
                        <a href="?edit=<?php echo (int)$e['id']; ?>&status=<?php echo $filter_status; ?>&country=<?php echo $filter_country; ?>"
                           class="btn btn-outline btn-sm" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        <!-- Publish / Draft toggle -->
                        <?php if ($e['status'] !== 'published'): ?>
                        <form method="post" style="display:inline;">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="_action" value="set_status">
                            <input type="hidden" name="id"     value="<?php echo (int)$e['id']; ?>">
                            <input type="hidden" name="status" value="published">
                            <button type="submit" class="btn btn-success btn-sm" title="Publish">
                                <i class="fas fa-eye"></i>
                            </button>
                        </form>
                        <?php else: ?>
                        <form method="post" style="display:inline;">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="_action" value="set_status">
                            <input type="hidden" name="id"     value="<?php echo (int)$e['id']; ?>">
                            <input type="hidden" name="status" value="draft">
                            <button type="submit" class="btn btn-warning btn-sm" title="Unpublish">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                        <!-- Cancel -->
                        <?php if ($e['status'] !== 'cancelled'): ?>
                        <form method="post" style="display:inline;"
                              onsubmit="return confirm('Mark this event as cancelled?')">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="_action" value="set_status">
                            <input type="hidden" name="id"     value="<?php echo (int)$e['id']; ?>">
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn btn-outline btn-sm" title="Cancel Event"
                                    style="color:#ef4444;border-color:#fca5a5;">
                                <i class="fas fa-ban"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                        <!-- Delete -->
                        <form method="post" style="display:inline;"
                              onsubmit="return confirm('Delete this event permanently?')">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="_action" value="delete">
                            <input type="hidden" name="id"     value="<?php echo (int)$e['id']; ?>">
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

<!-- ═══════════════════════════════════════════════════════════════════════════
     CREATE / EDIT EVENT MODAL
══════════════════════════════════════════════════════════════════════════════ -->
<div id="eventModal" style="
    display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
    z-index:1000;overflow-y:auto;padding:40px 20px;">
    <div style="
        background:#fff;border-radius:16px;max-width:660px;
        margin:0 auto;padding:0;box-shadow:0 20px 60px rgba(0,0,0,.2);">

        <!-- Header -->
        <div style="
            background:linear-gradient(135deg,var(--navy),var(--navy-l));
            padding:24px 28px;border-radius:16px 16px 0 0;
            display:flex;align-items:center;justify-content:space-between;">
            <div>
                <h2 style="color:#fff;font-size:18px;font-weight:800;margin:0;">
                    <i class="fas fa-calendar-alt" style="color:var(--orange);margin-right:8px;"></i>
                    <span id="eventModalTitle">New Event</span>
                </h2>
                <p style="color:rgba(255,255,255,.6);font-size:13px;margin:4px 0 0;">
                    Visible in the app's Polls & Events tab
                </p>
            </div>
            <button onclick="closeEventModal()" style="
                background:rgba(255,255,255,.15);border:none;color:#fff;
                width:36px;height:36px;border-radius:50%;cursor:pointer;
                font-size:16px;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Body -->
        <form method="post" style="padding:28px;">
            <?php echo portal_csrf_field(); ?>
            <input type="hidden" name="_action" value="save">
            <input type="hidden" name="id" id="eventId" value="0">

            <!-- Title -->
            <div class="form-group">
                <label class="form-label">Event Title <span class="req">*</span></label>
                <input type="text" name="title" id="eTitle" class="form-control"
                       placeholder="KandaNews Innovation Summit 2026" maxlength="255" required>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" id="eDescription" class="form-control"
                          rows="3" placeholder="What is this event about?"></textarea>
            </div>

            <!-- Dates -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Event Date & Time <span class="req">*</span></label>
                    <input type="datetime-local" name="event_date" id="eEventDate"
                           class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">End Date & Time</label>
                    <input type="datetime-local" name="end_date" id="eEndDate" class="form-control">
                    <div class="form-hint">Optional — leave blank for single-time events</div>
                </div>
            </div>

            <!-- Category + Country -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" id="eCategory" class="form-control">
                        <?php foreach ($categories as $val => $lbl): ?>
                        <option value="<?php echo $val; ?>"><?php echo htmlspecialchars($lbl); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Country <span class="req">*</span></label>
                    <select name="country" id="eCountry" class="form-control">
                        <?php foreach (portal_countries() as $code => $lbl): ?>
                        <option value="<?php echo strtolower($code); ?>">
                            <?php echo htmlspecialchars($lbl); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Location -->
            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" name="location" id="eLocation" class="form-control"
                       placeholder="Serena Hotel, Kampala — or leave blank for online-only">
            </div>

            <!-- Registration URL + Cover image -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Registration URL</label>
                    <input type="url" name="registration_url" id="eRegUrl" class="form-control"
                           placeholder="https://…">
                    <div class="form-hint">Button shown: "Register / Learn More"</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Cover Image URL</label>
                    <input type="url" name="cover_image_url" id="eCoverUrl" class="form-control"
                           placeholder="https://… event banner">
                </div>
            </div>

            <!-- Flags row -->
            <div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:20px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="is_online" id="eIsOnline"
                           style="width:16px;height:16px;">
                    <span class="form-label" style="margin:0;">Online event</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="is_free" id="eIsFree"
                           style="width:16px;height:16px;">
                    <span class="form-label" style="margin:0;">Free to attend</span>
                </label>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" id="eStatus" class="form-control" style="max-width:240px;">
                    <option value="draft">Draft (hidden from app)</option>
                    <option value="published">Published (visible in app)</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Buttons -->
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
                <button type="button" onclick="closeEventModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Event
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
function openEventModal(data) {
    const isEdit = !!data;
    document.getElementById('eventModalTitle').textContent = isEdit ? 'Edit Event' : 'New Event';
    document.getElementById('eventId').value       = data?.id               || '0';
    document.getElementById('eTitle').value        = data?.title            || '';
    document.getElementById('eDescription').value  = data?.description      || '';
    document.getElementById('eEventDate').value    = data?.event_date       ? data.event_date.slice(0,16) : '';
    document.getElementById('eEndDate').value      = data?.end_date         ? data.end_date.slice(0,16)   : '';
    document.getElementById('eLocation').value     = data?.location         || '';
    document.getElementById('eRegUrl').value       = data?.registration_url || '';
    document.getElementById('eCoverUrl').value     = data?.cover_image_url  || '';
    document.getElementById('eCategory').value     = data?.category         || 'other';
    document.getElementById('eCountry').value      = data?.country          || 'ug';
    document.getElementById('eStatus').value       = data?.status           || 'draft';
    document.getElementById('eIsOnline').checked   = data?.is_online == 1;
    document.getElementById('eIsFree').checked     = data?.is_free   == 1;

    document.getElementById('eventModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeEventModal() {
    document.getElementById('eventModal').style.display = 'none';
    document.body.style.overflow = '';
    if (history.replaceState) {
        const base = '<?php echo portal_url("events.php"); ?>';
        const params = new URLSearchParams({
            status: '<?php echo htmlspecialchars($filter_status); ?>',
            country: '<?php echo htmlspecialchars($filter_country); ?>'
        });
        history.replaceState(null, '', base + '?' + params.toString());
    }
}

document.getElementById('eventModal').addEventListener('click', function(e) {
    if (e.target === this) closeEventModal();
});

<?php if ($edit): ?>
openEventModal(<?php echo json_encode([
    'id'               => $edit['id'],
    'title'            => $edit['title'],
    'description'      => $edit['description'],
    'event_date'       => $edit['event_date'],
    'end_date'         => $edit['end_date'],
    'location'         => $edit['location'],
    'is_online'        => $edit['is_online'],
    'is_free'          => $edit['is_free'],
    'registration_url' => $edit['registration_url'],
    'cover_image_url'  => $edit['cover_image_url'],
    'category'         => $edit['category'],
    'country'          => $edit['country'],
    'status'           => $edit['status'],
]); ?>);
<?php endif; ?>
</script>


</main><!-- /.portal-body -->
</div><!-- /.main-area -->

<!-- ── Footer ────────────────────────────────── -->
<footer style="margin-left:var(--sidebar-w);padding:14px 28px;color:#bbb;font-size:12px;border-top:1px solid #e5e7eb;background:#fff;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
    <span>&copy; <?php echo date('Y'); ?> KandaNews Africa &mdash; CMS v2.0</span>
    <span>
        <a href="<?php echo portal_url('settings.php'); ?>" style="color:#bbb;margin-right:12px;">Settings</a>
        <a href="<?php echo portal_url('settings.php?section=integrations'); ?>" style="color:#bbb;">Integrations</a>
    </span>
</footer>

<!-- ── JavaScript ────────────────────────────── -->
<script>
/* ── Sidebar ─────────────────────────────── */
function toggleSidebar() {
    var sb = document.getElementById('sidebar');
    var ov = document.getElementById('sidebarOverlay');
    sb.classList.toggle('open');
    ov.classList.toggle('open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
}

/* ── Confirm dialogs ─────────────────────── */
document.addEventListener('click', function(e) {
    var link = e.target.closest('[data-confirm]');
    if (link) {
        if (!confirm(link.dataset.confirm)) {
            e.preventDefault();
        }
    }
});

/* ── Auto-hide flash messages ─────────────── */
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.flash').forEach(function(el) {
        setTimeout(function() {
            el.style.transition = 'opacity .4s, max-height .4s, margin .4s, padding .4s';
            el.style.opacity    = '0';
            el.style.maxHeight  = '0';
            el.style.margin     = '0';
            el.style.padding    = '0';
            el.style.overflow   = 'hidden';
            setTimeout(function() { el.remove(); }, 400);
        }, 5000);
    });
});

/* ── Image preview helper ─────────────────── */
function previewImage(input, imgId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById(imgId);
            if (img) { img.src = e.target.result; img.style.display = 'block'; }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>

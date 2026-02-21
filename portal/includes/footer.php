
</main><!-- /.portal-body -->

<!-- ── Footer ────────────────────────────────── -->
<footer style="text-align:center; padding:24px 16px 32px; color:#999; font-size:13px;">
    &copy; <?php echo date('Y'); ?> KandaNews Africa &mdash; Upload Portal v1.0
</footer>

<!-- ── JavaScript ────────────────────────────── -->
<script>
/* ── Sidebar toggle (mobile) ─────────────── */
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

/* ── User dropdown ────────────────────────── */
function toggleUserMenu() {
    document.getElementById('userDropdown').classList.toggle('open');
}

/* Close dropdown when clicking outside */
document.addEventListener('click', function(e) {
    var menu = document.querySelector('.user-menu');
    var dd   = document.getElementById('userDropdown');
    if (menu && dd && !menu.contains(e.target)) {
        dd.classList.remove('open');
    }
});

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
    var flashes = document.querySelectorAll('.flash-message');
    flashes.forEach(function(el) {
        setTimeout(function() {
            el.style.transition = 'opacity .4s, transform .4s';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(function() { el.remove(); }, 400);
        }, 5000);
    });
});
</script>
</body>
</html>

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../providers/auth_provider.dart';
import '../theme/kn_theme.dart';

/// App sidebar / navigation drawer.
/// Set [embedded] to true for desktop permanent sidebar mode.
class KnDrawer extends ConsumerWidget {
  final bool embedded;

  const KnDrawer({super.key, this.embedded = false});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider).user;

    final content = Column(
      children: [
        // Header
        Container(
          width: double.infinity,
          padding: EdgeInsets.only(
            top: embedded
                ? 24
                : MediaQuery.of(context).padding.top + 24,
            left: 24,
            right: 24,
            bottom: 24,
          ),
          decoration: const BoxDecoration(gradient: KnColors.primaryGradient),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Avatar
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: KnColors.orange,
                  borderRadius: BorderRadius.circular(18),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withAlpha(51),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Center(
                  child: Text(
                    user?.initials ?? '?',
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text(
                user?.displayName ?? 'Guest',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                user?.phone ?? '',
                style: TextStyle(
                  color: Colors.white.withAlpha(179),
                  fontSize: 14,
                ),
              ),
              if (user?.roleLabel.isNotEmpty == true) ...[
                const SizedBox(height: 4),
                Text(
                  user!.roleLabel,
                  style: TextStyle(
                    color: Colors.white.withAlpha(179),
                    fontSize: 13,
                  ),
                ),
              ],
            ],
          ),
        ),

        // Menu items
        Expanded(
          child: ListView(
            padding: const EdgeInsets.symmetric(vertical: 8),
            children: [
              _DrawerItem(
                icon: Icons.dashboard,
                label: 'Dashboard',
                onTap: () {
                  if (!embedded) Navigator.pop(context);
                  context.go('/dashboard');
                },
              ),
              _DrawerItem(
                icon: Icons.person_outline,
                label: 'My Account',
                onTap: () {
                  if (!embedded) Navigator.pop(context);
                  context.push('/profile');
                },
              ),
              _DrawerItem(
                icon: Icons.library_books_outlined,
                label: 'Editions',
                onTap: () {
                  if (!embedded) Navigator.pop(context);
                  context.push('/archives');
                },
              ),
              _DrawerItem(
                icon: Icons.star_outline,
                label: 'Subscription',
                onTap: () {
                  if (!embedded) Navigator.pop(context);
                  context.push('/subscribe');
                },
              ),
              const Divider(indent: 24, endIndent: 24),
              _DrawerItem(
                icon: Icons.settings_outlined,
                label: 'Settings',
                onTap: () {
                  if (!embedded) Navigator.pop(context);
                },
              ),
            ],
          ),
        ),

        // Logout
        const Divider(height: 1),
        _DrawerItem(
          icon: Icons.logout,
          label: 'Logout',
          color: KnColors.error,
          onTap: () async {
            if (!embedded) Navigator.pop(context);
            await ref.read(authProvider.notifier).logout();
            if (context.mounted) context.go('/login');
          },
        ),
        const SizedBox(height: 16),

        // Footer
        Padding(
          padding: const EdgeInsets.only(bottom: 24),
          child: Text(
            'KandaNews Africa v1.0.0',
            style: TextStyle(color: KnColors.textMuted, fontSize: 12),
          ),
        ),
      ],
    );

    // When embedded (desktop), render as a plain container with a right border
    if (embedded) {
      return Container(
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border(
            right: BorderSide(color: KnColors.border, width: 1),
          ),
        ),
        child: content,
      );
    }

    // Mobile: standard Drawer
    return Drawer(child: content);
  }
}

class _DrawerItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color? color;
  final VoidCallback onTap;

  const _DrawerItem({
    required this.icon,
    required this.label,
    this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Icon(icon, color: color ?? KnColors.navy, size: 24),
      title: Text(
        label,
        style: TextStyle(
          fontWeight: FontWeight.w600,
          color: color ?? KnColors.navy,
          fontSize: 15,
        ),
      ),
      onTap: onTap,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      contentPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 2),
    );
  }
}

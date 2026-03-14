import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../providers/auth_provider.dart';
import '../../providers/subscription_provider.dart';
import '../../theme/kn_theme.dart';
import '../../widgets/careers_bottom_sheet.dart';
import '../../widgets/invite_bottom_sheet.dart';
import '../webview/kn_webview_screen.dart';

class SettingsScreen extends ConsumerStatefulWidget {
  const SettingsScreen({super.key});

  @override
  ConsumerState<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends ConsumerState<SettingsScreen> {
  bool _notificationsEnabled = true;
  bool _editionAlerts = true;
  bool _marketingEmails = false;
  String _appVersion = '1.0.0';
  bool _prefsLoaded = false;

  @override
  void initState() {
    super.initState();
    _loadPrefs();
    _loadVersion();
  }

  Future<void> _loadPrefs() async {
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      setState(() {
        _notificationsEnabled = prefs.getBool('pref_notifications') ?? true;
        _editionAlerts = prefs.getBool('pref_edition_alerts') ?? true;
        _marketingEmails = prefs.getBool('pref_marketing') ?? false;
        _prefsLoaded = true;
      });
    }
  }

  Future<void> _loadVersion() async {
    try {
      final info = await PackageInfo.fromPlatform();
      if (mounted) setState(() => _appVersion = '${info.version}+${info.buildNumber}');
    } catch (_) {}
  }

  Future<void> _setPref(String key, bool value) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(key, value);
  }

  Future<void> _openUrl(String url, {String title = ''}) async {
    final uri = Uri.parse(url);
    // Play Store, WhatsApp, mailto, tel → leave the app (system handles them).
    // All web pages open in the in-app browser.
    if (uri.scheme == 'mailto' ||
        uri.scheme == 'tel' ||
        uri.host.contains('wa.me') ||
        uri.host.contains('play.google.com')) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      KnWebViewScreen.push(context, url, title: title);
    }
  }

  Future<void> _confirmLogout() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Log Out'),
        content: const Text('Are you sure you want to log out of KandaNews?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Log Out',
                style: TextStyle(color: KnColors.error)),
          ),
        ],
      ),
    );
    if (confirmed == true && mounted) {
      await ref.read(authProvider.notifier).logout();
      if (mounted) context.go('/login');
    }
  }

  Future<void> _clearCache() async {
    final prefs = await SharedPreferences.getInstance();
    // Only clear cached data keys, not auth-critical keys
    final keysToKeep = {
      'kn_access_token',
      'kn_refresh_token',
      'kn_user_id',
      'kn_user_country',
      'kn_is_new_user',
      'kn_onboarding_done',
      'pref_notifications',
      'pref_edition_alerts',
      'pref_marketing',
    };
    final allKeys = prefs.getKeys();
    for (final k in allKeys) {
      if (!keysToKeep.contains(k)) await prefs.remove(k);
    }
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Cache cleared'),
          duration: Duration(seconds: 2),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(authProvider).user;
    final subAsync = ref.watch(subscriptionStatusProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF5F6FA),
      appBar: AppBar(
        title: const Text('Settings'),
        backgroundColor: KnColors.navy,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 20, 16, 40),
        children: [
          // ── Account ───────────────────────────────────
          _SectionHeader(label: 'Account'),
          _SettingsCard(children: [
            _AccountTile(user: user),
            const Divider(height: 1, indent: 16),
            _NavTile(
              icon: Icons.person_outline,
              label: 'Edit Profile',
              subtitle: 'Update your name, role & details',
              onTap: () => context.push('/profile'),
            ),
            const Divider(height: 1, indent: 16),
            _NavTile(
              icon: Icons.flag_outlined,
              label: 'Country',
              subtitle: _countryLabel(user?.country),
              onTap: () => _showCountryPicker(context),
            ),
          ]),

          const SizedBox(height: 20),

          // ── Subscription ──────────────────────────────
          _SectionHeader(label: 'Subscription'),
          _SettingsCard(children: [
            subAsync.when(
              data: (sub) => _NavTile(
                icon: sub?.isActive == true
                    ? Icons.verified_outlined
                    : Icons.card_membership_outlined,
                iconColor: sub?.isActive == true
                    ? KnColors.success
                    : KnColors.orange,
                label: sub?.isActive == true
                    ? '${sub!.planLabel} Plan — Active'
                    : 'No Active Subscription',
                subtitle: sub?.isActive == true
                    ? 'Expires: ${sub!.expiresAt}'
                    : 'Unlock all editions',
                trailing: sub?.isActive == true
                    ? const Text('Renew',
                        style: TextStyle(
                            color: KnColors.orange,
                            fontWeight: FontWeight.w700,
                            fontSize: 13))
                    : null,
                onTap: () => context.push('/subscribe'),
              ),
              loading: () => const _LoadingTile(),
              error: (_, __) => _NavTile(
                icon: Icons.card_membership_outlined,
                label: 'Manage Subscription',
                onTap: () => context.push('/subscribe'),
              ),
            ),
            const Divider(height: 1, indent: 16),
            _NavTile(
              icon: Icons.receipt_long_outlined,
              label: 'View All Plans',
              subtitle: 'Daily, Weekly & Monthly options',
              onTap: () => context.push('/subscribe'),
            ),
          ]),

          const SizedBox(height: 20),

          // ── Referral ──────────────────────────────────
          _SectionHeader(label: 'Referral'),
          _SettingsCard(children: [
            _NavTile(
              icon: Icons.card_giftcard_outlined,
              label: 'Invite Friends',
              subtitle: 'Share KandaNews with your network',
              iconColor: KnColors.orange,
              onTap: () => InviteBottomSheet.show(context),
            ),
          ]),

          const SizedBox(height: 20),

          // ── Careers ───────────────────────────────────
          _SectionHeader(label: 'Careers'),
          _SettingsCard(children: [
            _NavTile(
              icon: Icons.work_outline_rounded,
              label: 'Join Our Team',
              subtitle: 'Explore opportunities at KandaNews Africa',
              iconColor: KnColors.navy,
              onTap: () => CareersBottomSheet.show(context),
            ),
          ]),

          const SizedBox(height: 20),

          // ── Notifications ─────────────────────────────
          _SectionHeader(label: 'Notifications'),
          _SettingsCard(children: [
            if (!_prefsLoaded)
              const _LoadingTile()
            else ...[
              _SwitchTile(
                icon: Icons.notifications_outlined,
                label: 'Push Notifications',
                subtitle: 'Receive app notifications',
                value: _notificationsEnabled,
                onChanged: (v) {
                  setState(() => _notificationsEnabled = v);
                  _setPref('pref_notifications', v);
                },
              ),
              const Divider(height: 1, indent: 16),
              _SwitchTile(
                icon: Icons.newspaper_outlined,
                label: 'New Edition Alerts',
                subtitle: 'Get notified when a new edition is published',
                value: _editionAlerts && _notificationsEnabled,
                onChanged: _notificationsEnabled
                    ? (v) {
                        setState(() => _editionAlerts = v);
                        _setPref('pref_edition_alerts', v);
                      }
                    : null,
              ),
              const Divider(height: 1, indent: 16),
              _SwitchTile(
                icon: Icons.campaign_outlined,
                label: 'Promotions & Offers',
                subtitle: 'Subscription deals and special offers',
                value: _marketingEmails,
                onChanged: (v) {
                  setState(() => _marketingEmails = v);
                  _setPref('pref_marketing', v);
                },
              ),
            ],
          ]),

          const SizedBox(height: 20),

          // ── About ─────────────────────────────────────
          _SectionHeader(label: 'About'),
          _SettingsCard(children: [
            _NavTile(
              icon: Icons.info_outline,
              label: 'App Version',
              subtitle: _appVersion,
              onTap: null,
            ),
            const Divider(height: 1, indent: 16),
            _NavTile(
              icon: Icons.star_outline,
              label: 'Rate KandaNews',
              subtitle: 'Love the app? Leave us a rating',
              onTap: () => _openUrl(
                  'https://play.google.com/store/apps/details?id=africa.kandanews'),
            ),
            const Divider(height: 1, indent: 16),
            _NavTile(
              icon: Icons.privacy_tip_outlined,
              label: 'Privacy Policy',
              onTap: () => _openUrl(
                'https://kandanews.africa/privacy-policy',
                title: 'Privacy Policy',
              ),
            ),
            const Divider(height: 1, indent: 16),
            _NavTile(
              icon: Icons.description_outlined,
              label: 'Terms of Service',
              onTap: () => _openUrl(
                'https://kandanews.africa/terms',
                title: 'Terms of Service',
              ),
            ),
          ]),

          const SizedBox(height: 20),

          // ── Support ───────────────────────────────────
          _SectionHeader(label: 'Support'),
          _SettingsCard(children: [
            _NavTile(
              icon: Icons.help_outline,
              label: 'Help & Support',
              subtitle: 'FAQs and contact our team',
              onTap: () => context.push('/support'),
            ),
            const Divider(height: 1, indent: 16),
            _NavTile(
              icon: Icons.chat_bubble_outline,
              label: 'WhatsApp Us',
              subtitle: 'Chat with our support team',
              onTap: () =>
                  _openUrl('https://wa.me/256200901370?text=Hi%20KandaNews%20Support'),
            ),
            const Divider(height: 1, indent: 16),
            _NavTile(
              icon: Icons.bug_report_outlined,
              label: 'Report a Problem',
              subtitle: 'Something not working? Let us know',
              onTap: () =>
                  _openUrl('mailto:support@kandanews.africa?subject=App%20Issue'),
            ),
          ]),

          const SizedBox(height: 20),

          // ── Storage ───────────────────────────────────
          _SectionHeader(label: 'Storage'),
          _SettingsCard(children: [
            _NavTile(
              icon: Icons.cleaning_services_outlined,
              label: 'Clear Cache',
              subtitle: 'Free up space by removing cached data',
              iconColor: KnColors.warning,
              onTap: _clearCache,
            ),
          ]),

          const SizedBox(height: 20),

          // ── Logout ────────────────────────────────────
          _SettingsCard(children: [
            _NavTile(
              icon: Icons.logout,
              label: 'Log Out',
              iconColor: KnColors.error,
              labelColor: KnColors.error,
              onTap: _confirmLogout,
            ),
          ]),

          const SizedBox(height: 24),
          Center(
            child: Text(
              'KandaNews Africa · v$_appVersion',
              style: const TextStyle(
                  fontSize: 12, color: KnColors.textMuted),
            ),
          ),
        ],
      ),
    );
  }

  String _countryLabel(String? code) {
    const map = {
      'ug': '🇺🇬 Uganda',
      'ke': '🇰🇪 Kenya',
      'ng': '🇳🇬 Nigeria',
      'za': '🇿🇦 South Africa',
    };
    return map[code?.toLowerCase()] ?? (code?.toUpperCase() ?? 'Unknown');
  }

  void _showCountryPicker(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const SizedBox(height: 12),
          Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                  color: KnColors.border,
                  borderRadius: BorderRadius.circular(2))),
          const SizedBox(height: 16),
          const Text('Select Country',
              style: TextStyle(
                  fontSize: 17,
                  fontWeight: FontWeight.w700,
                  color: KnColors.navy)),
          const SizedBox(height: 8),
          ...[
            ('ug', '🇺🇬', 'Uganda'),
            ('ke', '🇰🇪', 'Kenya'),
            ('ng', '🇳🇬', 'Nigeria'),
            ('za', '🇿🇦', 'South Africa'),
          ].map((c) => ListTile(
                leading: Text(c.$2, style: const TextStyle(fontSize: 24)),
                title: Text(c.$3,
                    style: const TextStyle(fontWeight: FontWeight.w600)),
                onTap: () {
                  Navigator.pop(ctx);
                  // Note: country change requires re-login; show info
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(
                          'To change your country, please contact support.'),
                      duration: const Duration(seconds: 3),
                    ),
                  );
                },
              )),
          const SizedBox(height: 16),
        ],
      ),
    );
  }
}

// ── Sub-widgets ─────────────────────────────────

class _SectionHeader extends StatelessWidget {
  final String label;
  const _SectionHeader({required this.label});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 8),
      child: Text(
        label.toUpperCase(),
        style: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w700,
          color: KnColors.textMuted,
          letterSpacing: 1.2,
        ),
      ),
    );
  }
}

class _SettingsCard extends StatelessWidget {
  final List<Widget> children;
  const _SettingsCard({required this.children});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withAlpha(10),
              blurRadius: 8,
              offset: const Offset(0, 2)),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: children,
      ),
    );
  }
}

class _AccountTile extends StatelessWidget {
  final dynamic user;
  const _AccountTile({this.user});

  @override
  Widget build(BuildContext context) {
    final name = user?.displayName ?? 'Guest';
    final phone = user?.phone ?? '';
    final initials =
        name.isNotEmpty ? name[0].toUpperCase() : '?';

    return Padding(
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              gradient: KnColors.orangeGradient,
              borderRadius: BorderRadius.circular(14),
            ),
            child: Center(
              child: Text(
                initials,
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w800,
                  color: Colors.white,
                ),
              ),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(name,
                    style: const TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 16,
                        color: KnColors.navy)),
                if (phone.isNotEmpty)
                  Text(phone,
                      style: const TextStyle(
                          fontSize: 13, color: KnColors.textSecondary)),
                if (user?.roleLabel?.isNotEmpty == true)
                  Text(user!.roleLabel,
                      style: const TextStyle(
                          fontSize: 12, color: KnColors.textMuted)),
              ],
            ),
          ),
          IconButton(
            icon: const Icon(Icons.edit_outlined,
                color: KnColors.orange, size: 20),
            onPressed: () => context.push('/profile'),
          ),
        ],
      ),
    );
  }
}

class _NavTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final String? subtitle;
  final Color? iconColor;
  final Color? labelColor;
  final Widget? trailing;
  final VoidCallback? onTap;

  const _NavTile({
    required this.icon,
    required this.label,
    this.subtitle,
    this.iconColor,
    this.labelColor,
    this.trailing,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      contentPadding:
          const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
      leading: Container(
        width: 36,
        height: 36,
        decoration: BoxDecoration(
          color: (iconColor ?? KnColors.navy).withAlpha(15),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Icon(icon,
            color: iconColor ?? KnColors.navy, size: 20),
      ),
      title: Text(
        label,
        style: TextStyle(
          fontWeight: FontWeight.w600,
          fontSize: 14,
          color: labelColor ?? KnColors.navy,
        ),
      ),
      subtitle: subtitle != null
          ? Text(
              subtitle!,
              style: const TextStyle(
                  fontSize: 12, color: KnColors.textSecondary),
            )
          : null,
      trailing: trailing ??
          (onTap != null
              ? const Icon(Icons.chevron_right,
                  color: KnColors.textMuted, size: 20)
              : null),
      onTap: onTap,
    );
  }
}

class _SwitchTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final String? subtitle;
  final bool value;
  final ValueChanged<bool>? onChanged;

  const _SwitchTile({
    required this.icon,
    required this.label,
    this.subtitle,
    required this.value,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      contentPadding:
          const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
      leading: Container(
        width: 36,
        height: 36,
        decoration: BoxDecoration(
          color: KnColors.navy.withAlpha(15),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Icon(icon, color: KnColors.navy, size: 20),
      ),
      title: Text(label,
          style: const TextStyle(
              fontWeight: FontWeight.w600,
              fontSize: 14,
              color: KnColors.navy)),
      subtitle: subtitle != null
          ? Text(subtitle!,
              style: const TextStyle(
                  fontSize: 12, color: KnColors.textSecondary))
          : null,
      trailing: Switch(
        value: value,
        onChanged: onChanged,
        activeColor: KnColors.orange,
        thumbColor: WidgetStateProperty.all(Colors.white),
      ),
    );
  }
}

class _LoadingTile extends StatelessWidget {
  const _LoadingTile();

  @override
  Widget build(BuildContext context) {
    return const Padding(
      padding: EdgeInsets.symmetric(vertical: 16),
      child: Center(
          child: CircularProgressIndicator(
              color: KnColors.orange, strokeWidth: 2)),
    );
  }
}

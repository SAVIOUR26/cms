import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../theme/kn_theme.dart';

class AdvertiseEditorialScreen extends StatefulWidget {
  const AdvertiseEditorialScreen({super.key});

  @override
  State<AdvertiseEditorialScreen> createState() =>
      _AdvertiseEditorialScreenState();
}

class _AdvertiseEditorialScreenState extends State<AdvertiseEditorialScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabs;

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabs.dispose();
    super.dispose();
  }

  Future<void> _launch(String url) async {
    final uri = Uri.parse(url);
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not open link')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF0F2F5),
      appBar: AppBar(
        title: const Text('Advertise & Editorial'),
        backgroundColor: KnColors.navy,
        foregroundColor: Colors.white,
        elevation: 0,
        bottom: TabBar(
          controller: _tabs,
          indicatorColor: KnColors.orange,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white54,
          labelStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
          tabs: const [
            Tab(icon: Icon(Icons.campaign_outlined, size: 18), text: 'Advertise'),
            Tab(icon: Icon(Icons.edit_note_outlined, size: 18), text: 'Editorial'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabs,
        children: [
          _AdvertiseTab(onLaunch: _launch),
          _EditorialTab(onLaunch: _launch),
        ],
      ),
    );
  }
}

// =============================================================================
// TAB 1 — ADVERTISE
// =============================================================================

class _AdvertiseTab extends StatelessWidget {
  final Future<void> Function(String url) onLaunch;
  const _AdvertiseTab({required this.onLaunch});

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 20, 16, 40),
      children: [
        // ── Hero card ──────────────────────────────────────────────────────
        _HeroCard(
          gradient: KnColors.orangeGradient,
          icon: Icons.campaign_outlined,
          eyebrow: 'ADVERTISE WITH US',
          title: 'Reach Thousands\nAcross Africa',
          subtitle:
              'Put your brand in front of professionals, entrepreneurs & students '
              'across Uganda, Kenya, Nigeria and South Africa.',
          stats: const [
            _Stat(value: '10K+', label: 'Readers'),
            _Stat(value: '4', label: 'Countries'),
            _Stat(value: '100%', label: 'Professionals'),
          ],
        ),

        const SizedBox(height: 24),

        // ── Actions ────────────────────────────────────────────────────────
        _SectionLabel(label: 'Get Started'),
        const SizedBox(height: 10),

        _ActionCard(
          gradient: KnColors.orangeGradient,
          icon: Icons.open_in_browser_outlined,
          title: 'Self-Serve Ad Portal',
          subtitle: 'Create, manage and track your campaigns directly',
          tag: 'ads.kandanews.africa',
          tagIcon: Icons.link,
          isPrimary: true,
          onTap: () => onLaunch('https://ads.kandanews.africa'),
        ),

        const SizedBox(height: 12),

        _ActionCard(
          gradient: KnColors.primaryGradient,
          icon: Icons.description_outlined,
          title: 'View Our Rate Card',
          subtitle: 'Pricing for banner, newsletter & special edition placements',
          tag: 'Rates & Packages',
          tagIcon: Icons.article_outlined,
          onTap: () => context.push('/archives', extra: {
            'type': 'rate_card',
            'title': 'Rate Card',
          }),
        ),

        const SizedBox(height: 24),

        _SectionLabel(label: 'Talk to Our Team'),
        const SizedBox(height: 10),

        _ContactTile(
          icon: Icons.chat_outlined,
          iconColor: const Color(0xFF25D366),
          label: 'WhatsApp Our Ads Team',
          subtitle: 'Chat directly with our advertising team',
          onTap: () => onLaunch(
            'https://wa.me/256200901370?text=Hi%20KandaNews%2C%20I%27m%20interested%20in%20advertising.',
          ),
        ),

        const SizedBox(height: 10),

        _ContactTile(
          icon: Icons.email_outlined,
          iconColor: KnColors.orange,
          label: 'ads@kandanews.africa',
          subtitle: 'Send us a detailed enquiry by email',
          onTap: () => onLaunch(
            'mailto:ads@kandanews.africa?subject=Advertising%20Enquiry%20-%20KandaNews',
          ),
        ),

        const SizedBox(height: 24),

        // ── Why advertise ──────────────────────────────────────────────────
        _SectionLabel(label: 'Why KandaNews?'),
        const SizedBox(height: 10),

        _InfoGrid(items: const [
          _InfoItem(
            icon: Icons.people_outline,
            title: 'Premium Audience',
            body:
                'Reach decision-makers, graduates and entrepreneurs who read daily.',
          ),
          _InfoItem(
            icon: Icons.public_outlined,
            title: 'Pan-African Reach',
            body:
                'Campaigns run across Uganda, Kenya, Nigeria & South Africa simultaneously.',
          ),
          _InfoItem(
            icon: Icons.bar_chart_outlined,
            title: 'Full Reporting',
            body:
                'Impression and click analytics delivered straight to your dashboard.',
          ),
          _InfoItem(
            icon: Icons.auto_awesome_outlined,
            title: 'Multiple Formats',
            body:
                'In-app banners, newsletter slots, sponsored editions and more.',
          ),
        ]),
      ],
    );
  }
}

// =============================================================================
// TAB 2 — EDITORIAL
// =============================================================================

class _EditorialTab extends StatelessWidget {
  final Future<void> Function(String url) onLaunch;
  const _EditorialTab({required this.onLaunch});

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 20, 16, 40),
      children: [
        // ── Hero card ──────────────────────────────────────────────────────
        _HeroCard(
          gradient: KnColors.primaryGradient,
          icon: Icons.edit_note_outlined,
          eyebrow: 'EDITORIAL DESK',
          title: 'Your Story Deserves\nTo Be Told',
          subtitle:
              'Share news tips, press releases, and story pitches with the '
              'KandaNews editorial team. We cover business, tech, campus & more.',
          stats: const [
            _Stat(value: 'Daily', label: 'Editions'),
            _Stat(value: '4+', label: 'Categories'),
            _Stat(value: 'Africa', label: 'Coverage'),
          ],
        ),

        const SizedBox(height: 24),

        // ── Submit actions ─────────────────────────────────────────────────
        _SectionLabel(label: 'Submit to Us'),
        const SizedBox(height: 10),

        _ActionCard(
          gradient: KnColors.primaryGradient,
          icon: Icons.send_outlined,
          title: 'Submit a News Tip',
          subtitle: 'Share a breaking story, press release or news lead with our editors',
          tag: 'news@kandanews.africa',
          tagIcon: Icons.email_outlined,
          isPrimary: true,
          onTap: () => onLaunch(
            'mailto:news@kandanews.africa?subject=News%20Tip%20Submission',
          ),
        ),

        const SizedBox(height: 12),

        _ActionCard(
          gradient: const LinearGradient(
            colors: [Color(0xFF7C3AED), Color(0xFF4F46E5)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          icon: Icons.auto_awesome_outlined,
          title: 'Request Special Edition Coverage',
          subtitle:
              'Want your event, product launch or organisation featured in a special edition?',
          tag: 'Special Editions',
          tagIcon: Icons.star_outline,
          onTap: () => onLaunch(
            'mailto:news@kandanews.africa'
            '?subject=Special%20Edition%20Coverage%20Request'
            '&body=Hi%20KandaNews%20Editorial%20Team%2C%0A%0A'
            'I%20would%20like%20to%20request%20special%20edition%20coverage%20for%3A%0A%0A'
            '[Describe%20your%20story%20or%20event%20here]',
          ),
        ),

        const SizedBox(height: 24),

        _SectionLabel(label: 'Contact the Desk'),
        const SizedBox(height: 10),

        _ContactTile(
          icon: Icons.chat_outlined,
          iconColor: const Color(0xFF25D366),
          label: 'WhatsApp Editorial Desk',
          subtitle: 'Fastest way to pitch a time-sensitive story',
          onTap: () => onLaunch(
            'https://wa.me/256200901370?text=Hi%20KandaNews%20Editorial%20Team%2C%20I%20have%20a%20story%20to%20share.',
          ),
        ),

        const SizedBox(height: 10),

        _ContactTile(
          icon: Icons.email_outlined,
          iconColor: KnColors.navy,
          label: 'news@kandanews.africa',
          subtitle: 'Send detailed pitches, press releases or story files',
          onTap: () => onLaunch(
            'mailto:news@kandanews.africa?subject=Editorial%20Enquiry',
          ),
        ),

        const SizedBox(height: 24),

        // ── What we cover ──────────────────────────────────────────────────
        _SectionLabel(label: 'What We Cover'),
        const SizedBox(height: 10),

        _InfoGrid(items: const [
          _InfoItem(
            icon: Icons.business_center_outlined,
            title: 'Business & Finance',
            body: 'Entrepreneurship, investment, market news and economic analysis.',
          ),
          _InfoItem(
            icon: Icons.school_outlined,
            title: 'Campus & Youth',
            body: 'Student achievements, university news and youth leadership stories.',
          ),
          _InfoItem(
            icon: Icons.lightbulb_outlined,
            title: 'Tech & Innovation',
            body: 'Startups, digital transformation and emerging African tech.',
          ),
          _InfoItem(
            icon: Icons.groups_outlined,
            title: 'Society & Culture',
            body: 'Lifestyle, arts, sports and community stories across the continent.',
          ),
        ]),

        const SizedBox(height: 16),

        // ── Editorial guidelines note ──────────────────────────────────────
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: KnColors.navy.withAlpha(8),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: KnColors.border),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(Icons.info_outline, size: 16, color: KnColors.textMuted),
              const SizedBox(width: 10),
              const Expanded(
                child: Text(
                  'All submissions go through editorial review. We publish stories '
                  'that are accurate, original and relevant to our African professional readership. '
                  'We do not guarantee publication of all submissions.',
                  style: TextStyle(
                    fontSize: 12,
                    color: KnColors.textSecondary,
                    height: 1.5,
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

// =============================================================================
// Shared Widgets
// =============================================================================

class _HeroCard extends StatelessWidget {
  final LinearGradient gradient;
  final IconData icon;
  final String eyebrow;
  final String title;
  final String subtitle;
  final List<_Stat> stats;

  const _HeroCard({
    required this.gradient,
    required this.icon,
    required this.eyebrow,
    required this.title,
    required this.subtitle,
    required this.stats,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        gradient: gradient,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: KnColors.orange, width: 1.5),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha(25),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Eyebrow + icon row
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.white.withAlpha(20),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: Colors.white, size: 24),
              ),
              const SizedBox(width: 12),
              Text(
                eyebrow,
                style: const TextStyle(
                  color: KnColors.orange,
                  fontSize: 10,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 1.5,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            title,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 22,
              fontWeight: FontWeight.w900,
              height: 1.2,
              letterSpacing: -0.3,
            ),
          ),
          const SizedBox(height: 10),
          Text(
            subtitle,
            style: TextStyle(
              color: Colors.white.withAlpha(204),
              fontSize: 13,
              height: 1.55,
            ),
          ),
          const SizedBox(height: 20),
          // Stats row
          Container(
            padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
            decoration: BoxDecoration(
              color: Colors.white.withAlpha(12),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: stats.map((s) => _buildStat(s)).toList(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStat(_Stat s) {
    return Column(
      children: [
        Text(
          s.value,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 18,
            fontWeight: FontWeight.w900,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          s.label,
          style: TextStyle(
            color: Colors.white.withAlpha(170),
            fontSize: 11,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    );
  }
}

class _Stat {
  final String value;
  final String label;
  const _Stat({required this.value, required this.label});
}

class _ActionCard extends StatelessWidget {
  final LinearGradient gradient;
  final IconData icon;
  final String title;
  final String subtitle;
  final String tag;
  final IconData tagIcon;
  final bool isPrimary;
  final VoidCallback onTap;

  const _ActionCard({
    required this.gradient,
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.tag,
    required this.tagIcon,
    this.isPrimary = false,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          gradient: gradient,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isPrimary ? KnColors.orange : Colors.white.withAlpha(30),
            width: isPrimary ? 1.5 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withAlpha(20),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        padding: const EdgeInsets.all(18),
        child: Row(
          children: [
            // Icon bubble
            Container(
              width: 50,
              height: 50,
              decoration: BoxDecoration(
                color: Colors.white.withAlpha(22),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(icon, color: Colors.white, size: 26),
            ),
            const SizedBox(width: 14),
            // Text
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 15,
                      fontWeight: FontWeight.w800,
                      height: 1.2,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    subtitle,
                    style: TextStyle(
                      color: Colors.white.withAlpha(178),
                      fontSize: 12,
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 8),
                  // Tag chip
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.white.withAlpha(25),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: Colors.white.withAlpha(60), width: 1),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(tagIcon, size: 10, color: Colors.white70),
                        const SizedBox(width: 4),
                        Text(
                          tag,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            Icon(Icons.arrow_forward_ios,
                size: 14, color: Colors.white.withAlpha(170)),
          ],
        ),
      ),
    );
  }
}

class _ContactTile extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String label;
  final String subtitle;
  final VoidCallback onTap;

  const _ContactTile({
    required this.icon,
    required this.iconColor,
    required this.label,
    required this.subtitle,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: KnColors.border),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withAlpha(6),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: iconColor.withAlpha(18),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: iconColor, size: 22),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    label,
                    style: const TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 14,
                      color: KnColors.navy,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: const TextStyle(
                      fontSize: 12,
                      color: KnColors.textSecondary,
                    ),
                  ),
                ],
              ),
            ),
            const Icon(Icons.chevron_right, color: KnColors.textMuted, size: 20),
          ],
        ),
      ),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  final String label;
  const _SectionLabel({required this.label});

  @override
  Widget build(BuildContext context) {
    return Text(
      label.toUpperCase(),
      style: const TextStyle(
        fontSize: 11,
        fontWeight: FontWeight.w800,
        color: KnColors.textMuted,
        letterSpacing: 1.3,
      ),
    );
  }
}

class _InfoGrid extends StatelessWidget {
  final List<_InfoItem> items;
  const _InfoGrid({required this.items});

  @override
  Widget build(BuildContext context) {
    return GridView.count(
      crossAxisCount: 2,
      crossAxisSpacing: 10,
      mainAxisSpacing: 10,
      childAspectRatio: 1.35,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      children: items.map((item) => _InfoItemCard(item: item)).toList(),
    );
  }
}

class _InfoItem {
  final IconData icon;
  final String title;
  final String body;
  const _InfoItem({required this.icon, required this.title, required this.body});
}

class _InfoItemCard extends StatelessWidget {
  final _InfoItem item;
  const _InfoItemCard({required this.item});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: KnColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha(6),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: KnColors.orange.withAlpha(18),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(item.icon, size: 18, color: KnColors.orange),
          ),
          const SizedBox(height: 8),
          Text(
            item.title,
            style: const TextStyle(
              fontWeight: FontWeight.w700,
              fontSize: 12,
              color: KnColors.navy,
            ),
          ),
          const SizedBox(height: 4),
          Expanded(
            child: Text(
              item.body,
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                fontSize: 11,
                color: KnColors.textSecondary,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

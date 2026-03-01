import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../theme/kn_theme.dart';

class SupportScreen extends StatelessWidget {
  const SupportScreen({super.key});

  static const _email = 'support@kandanews.africa';
  static const _phone = '+256 700 000 000';
  static const _whatsapp = '+256700000000';
  static const _website = 'https://kandanews.africa';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Help & Support')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                gradient: KnColors.primaryGradient,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                children: [
                  const Icon(Icons.support_agent, color: KnColors.orange, size: 48),
                  const SizedBox(height: 12),
                  const Text(
                    'How can we help?',
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Reach out to us through any of the channels below',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: Colors.white.withAlpha(179),
                      fontSize: 14,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            const Text(
              'Contact Us',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w700,
                color: KnColors.navy,
              ),
            ),
            const SizedBox(height: 12),

            _ContactTile(
              icon: Icons.email_outlined,
              label: 'Email',
              value: _email,
              color: const Color(0xFF3B82F6),
              onTap: () => _launchUrl('mailto:$_email'),
              onLongPress: () => _copy(context, _email),
            ),
            _ContactTile(
              icon: Icons.phone_outlined,
              label: 'Phone',
              value: _phone,
              color: const Color(0xFF10B981),
              onTap: () => _launchUrl('tel:$_phone'),
              onLongPress: () => _copy(context, _phone),
            ),
            _ContactTile(
              icon: Icons.chat_outlined,
              label: 'WhatsApp',
              value: 'Chat with us',
              color: const Color(0xFF25D366),
              onTap: () => _launchUrl('https://wa.me/$_whatsapp'),
            ),
            _ContactTile(
              icon: Icons.language,
              label: 'Website',
              value: 'kandanews.africa',
              color: KnColors.orange,
              onTap: () => _launchUrl(_website),
            ),

            const SizedBox(height: 24),

            const Text(
              'FAQs',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w700,
                color: KnColors.navy,
              ),
            ),
            const SizedBox(height: 12),

            _FaqTile(
              question: 'How do I subscribe?',
              answer:
                  'Tap "Subscribe" on the dashboard and choose a plan that suits you. '
                  'Payment is processed securely via mobile money or card.',
            ),
            _FaqTile(
              question: 'Can I read editions offline?',
              answer:
                  'Offline reading is coming soon. For now, editions are available '
                  'when you have an internet connection.',
            ),
            _FaqTile(
              question: 'How do I advertise with KandaNews?',
              answer:
                  'Tap "Advertise" on the dashboard or email ads@kandanews.africa '
                  'for rates and available placements.',
            ),
            _FaqTile(
              question: 'How do I change my account details?',
              answer:
                  'Go to My Account from the sidebar to view your profile. '
                  'Contact support for any changes you need.',
            ),

            const SizedBox(height: 32),

            // App info footer
            Center(
              child: Column(
                children: [
                  Text(
                    'KandaNews Africa v1.0.0',
                    style: TextStyle(
                      color: KnColors.textMuted,
                      fontSize: 13,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '\u00A9 2025 KandaNews Africa. All rights reserved.',
                    style: TextStyle(
                      color: KnColors.textMuted,
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }

  Future<void> _launchUrl(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  void _copy(BuildContext context, String text) {
    Clipboard.setData(ClipboardData(text: text));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Copied: $text')),
    );
  }
}

class _ContactTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final Color color;
  final VoidCallback onTap;
  final VoidCallback? onLongPress;

  const _ContactTile({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
    required this.onTap,
    this.onLongPress,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          onTap: onTap,
          onLongPress: onLongPress,
          borderRadius: BorderRadius.circular(14),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: color.withAlpha(50), width: 1.5),
            ),
            child: Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: color.withAlpha(25),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(icon, color: color, size: 22),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        label,
                        style: const TextStyle(
                          fontSize: 12,
                          color: KnColors.textMuted,
                        ),
                      ),
                      Text(
                        value,
                        style: const TextStyle(
                          fontWeight: FontWeight.w600,
                          color: KnColors.navy,
                          fontSize: 15,
                        ),
                      ),
                    ],
                  ),
                ),
                Icon(Icons.arrow_forward_ios, color: color.withAlpha(120), size: 16),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _FaqTile extends StatefulWidget {
  final String question;
  final String answer;

  const _FaqTile({required this.question, required this.answer});

  @override
  State<_FaqTile> createState() => _FaqTileState();
}

class _FaqTileState extends State<_FaqTile> {
  bool _expanded = false;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        child: InkWell(
          onTap: () => setState(() => _expanded = !_expanded),
          borderRadius: BorderRadius.circular(12),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        widget.question,
                        style: const TextStyle(
                          fontWeight: FontWeight.w600,
                          color: KnColors.navy,
                          fontSize: 15,
                        ),
                      ),
                    ),
                    Icon(
                      _expanded ? Icons.expand_less : Icons.expand_more,
                      color: KnColors.textMuted,
                    ),
                  ],
                ),
                if (_expanded) ...[
                  const SizedBox(height: 10),
                  Text(
                    widget.answer,
                    style: const TextStyle(
                      color: KnColors.textSecondary,
                      fontSize: 14,
                      height: 1.5,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}

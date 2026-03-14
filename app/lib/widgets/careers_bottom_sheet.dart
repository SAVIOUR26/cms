import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../theme/kn_theme.dart';
import '../screens/webview/kn_webview_screen.dart';

/// Careers / Join Our Team bottom sheet.
/// Call [CareersBottomSheet.show] from anywhere in the app.
class CareersBottomSheet extends StatelessWidget {
  const CareersBottomSheet({super.key});

  static void show(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => const CareersBottomSheet(),
    );
  }

  Future<void> _launch(BuildContext context, String url) async {
    final uri = Uri.parse(url);
    if (uri.scheme == 'mailto' || uri.scheme == 'tel') {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      if (context.mounted) {
        Navigator.pop(context); // close the bottom sheet first
        KnWebViewScreen.push(context, url, title: 'KandaNews Careers');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding:
          EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Handle
          const SizedBox(height: 12),
          Container(
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: KnColors.border,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: 20),

          // Hero card
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.all(22),
              decoration: BoxDecoration(
                gradient: KnColors.primaryGradient,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: KnColors.orange, width: 1.5),
              ),
              child: Row(
                children: [
                  Container(
                    width: 54,
                    height: 54,
                    decoration: BoxDecoration(
                      color: Colors.white.withAlpha(20),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: const Icon(
                      Icons.work_outline_rounded,
                      color: Colors.white,
                      size: 28,
                    ),
                  ),
                  const SizedBox(width: 14),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'JOIN OUR TEAM',
                          style: TextStyle(
                            color: KnColors.orange,
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                            letterSpacing: 1.5,
                          ),
                        ),
                        SizedBox(height: 4),
                        Text(
                          'Careers at KandaNews',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 17,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        SizedBox(height: 4),
                        Text(
                          'Africa\'s Future of News',
                          style: TextStyle(
                            color: Colors.white60,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 20),

          // Equal opportunity statement
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: KnColors.orange.withAlpha(10),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: KnColors.orange.withAlpha(50)),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.diversity_3_outlined,
                      size: 18, color: KnColors.orange),
                  const SizedBox(width: 10),
                  const Expanded(
                    child: Text(
                      'KandaNews Africa is an equal opportunity employer. '
                      'We believe in the power of diverse voices, backgrounds and '
                      'perspectives in building the future of African media.',
                      style: TextStyle(
                        fontSize: 12,
                        color: KnColors.navy,
                        height: 1.55,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 16),

          // Action tiles
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Column(
              children: [
                // Careers portal
                _CareerActionTile(
                  icon: Icons.open_in_browser_outlined,
                  iconColor: KnColors.navy,
                  title: 'View Open Positions',
                  subtitle: 'Explore current vacancies on our careers portal',
                  tag: 'kandanews.africa/careers',
                  onTap: () =>
                      _launch(context, 'https://kandanews.africa/careers'),
                ),
                const SizedBox(height: 10),

                // Email
                _CareerActionTile(
                  icon: Icons.email_outlined,
                  iconColor: KnColors.orange,
                  title: 'Send Your Application',
                  subtitle: 'Email your CV and cover letter directly to our HR team',
                  tag: 'careers@kandanews.africa',
                  onTap: () => _launch(
                    context,
                    'mailto:careers@kandanews.africa'
                    '?subject=Job%20Application%20-%20KandaNews%20Africa'
                    '&body=Hi%20KandaNews%20Team%2C%0A%0A'
                    'I%20would%20like%20to%20apply%20for%20a%20position%20at%20KandaNews%20Africa.%0A%0A'
                    '[Your%20name%2C%20role%20of%20interest%20and%20brief%20intro%20here]',
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // Close
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: SizedBox(
              width: double.infinity,
              child: TextButton(
                onPressed: () => Navigator.pop(context),
                style: TextButton.styleFrom(
                    foregroundColor: KnColors.textMuted),
                child: const Text('Close'),
              ),
            ),
          ),

          const SizedBox(height: 24),
        ],
      ),
    );
  }
}

class _CareerActionTile extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String title;
  final String subtitle;
  final String tag;
  final VoidCallback onTap;

  const _CareerActionTile({
    required this.icon,
    required this.iconColor,
    required this.title,
    required this.subtitle,
    required this.tag,
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
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: iconColor.withAlpha(15),
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
                    title,
                    style: const TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 14,
                      color: KnColors.navy,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    subtitle,
                    style: const TextStyle(
                      fontSize: 12,
                      color: KnColors.textSecondary,
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 5),
                  Row(
                    children: [
                      Icon(Icons.link, size: 10, color: KnColors.textMuted),
                      const SizedBox(width: 4),
                      Text(
                        tag,
                        style: const TextStyle(
                          fontSize: 10,
                          color: KnColors.textMuted,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            const Icon(Icons.chevron_right,
                color: KnColors.textMuted, size: 20),
          ],
        ),
      ),
    );
  }
}

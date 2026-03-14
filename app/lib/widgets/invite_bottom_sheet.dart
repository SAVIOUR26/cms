import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';
import '../theme/kn_theme.dart';

/// Reusable invite/referral bottom sheet.
/// Call [InviteBottomSheet.show] from anywhere in the app.
class InviteBottomSheet extends StatelessWidget {
  const InviteBottomSheet({super.key});

  static const _appUrl =
      'https://play.google.com/store/apps/details?id=africa.kandanews';
  static const _inviteText =
      'Stay informed with KandaNews Africa — The Future of News for '
      "Africa's professionals, entrepreneurs & students. Download the app: $_appUrl";

  static void show(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => const InviteBottomSheet(),
    );
  }

  Future<void> _shareWhatsApp(BuildContext context) async {
    Navigator.pop(context);
    final encoded = Uri.encodeComponent(_inviteText);
    final uri = Uri.parse('https://wa.me/?text=$encoded');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  void _copyLink(BuildContext context) {
    Clipboard.setData(const ClipboardData(text: _inviteText));
    Navigator.pop(context);
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Invite message copied to clipboard!'),
        duration: Duration(seconds: 2),
      ),
    );
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

          // Header card
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: KnColors.primaryGradient,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Row(
                children: [
                  Container(
                    width: 52,
                    height: 52,
                    decoration: BoxDecoration(
                      color: Colors.white.withAlpha(30),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.card_giftcard_outlined,
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
                          'Invite Friends',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        SizedBox(height: 4),
                        Text(
                          'Share KandaNews with your network',
                          style: TextStyle(
                            color: Colors.white70,
                            fontSize: 13,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 16),

          // Message preview
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: KnColors.navy.withAlpha(8),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: KnColors.border),
              ),
              child: const Text(
                _inviteText,
                style: TextStyle(
                  fontSize: 13,
                  color: KnColors.navy,
                  height: 1.5,
                ),
              ),
            ),
          ),

          const SizedBox(height: 16),

          // Action buttons
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Column(
              children: [
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () => _copyLink(context),
                    icon: const Icon(Icons.copy_outlined, size: 18),
                    label: const Text('Copy Invite Message'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: KnColors.navy,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () => _shareWhatsApp(context),
                    icon: const Icon(Icons.chat_outlined, size: 18),
                    label: const Text('Share via WhatsApp'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF25D366),
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                SizedBox(
                  width: double.infinity,
                  child: TextButton(
                    onPressed: () => Navigator.pop(context),
                    style: TextButton.styleFrom(
                      foregroundColor: KnColors.textMuted,
                    ),
                    child: const Text('Close'),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),
        ],
      ),
    );
  }
}

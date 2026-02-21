import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/auth_provider.dart';
import '../../providers/edition_provider.dart';
import '../../providers/subscription_provider.dart';
import '../../theme/kn_theme.dart';
import '../../widgets/dashboard_tile.dart';
import '../../widgets/kn_drawer.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authProvider);
    final user = authState.user;
    final country = user?.country ?? 'ug';
    final quote = ref.watch(quoteProvider);
    final todayEdition = ref.watch(todayEditionProvider(country));
    final subscription = ref.watch(subscriptionStatusProvider);

    final firstName = user?.firstName ?? 'Reader';
    final isSubscribed = subscription.whenOrNull(
          data: (sub) => sub?.isActive ?? false,
        ) ??
        false;

    return Scaffold(
      drawer: const KnDrawer(),
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                gradient: KnColors.orangeGradient,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.newspaper, size: 20, color: Colors.white),
            ),
            const SizedBox(width: 12),
            const Text('KandaNews'),
          ],
        ),
        actions: [
          // Avatar / Profile
          GestureDetector(
            onTap: () => context.push('/profile'),
            child: Container(
              margin: const EdgeInsets.only(right: 16),
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: KnColors.orange.withAlpha(51),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Center(
                child: Text(
                  user?.initials ?? '?',
                  style: const TextStyle(
                    fontWeight: FontWeight.w800,
                    color: KnColors.orange,
                    fontSize: 14,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(todayEditionProvider(country));
          ref.invalidate(quoteProvider);
          ref.invalidate(subscriptionStatusProvider);
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Welcome
              Text(
                'Welcome, $firstName!',
                style: const TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.w800,
                  color: KnColors.navy,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                isSubscribed ? 'Your subscription is active' : 'Subscribe to read all editions',
                style: const TextStyle(color: KnColors.textSecondary, fontSize: 14),
              ),
              const SizedBox(height: 24),

              // Dashboard Tiles Grid
              GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                mainAxisSpacing: 16,
                crossAxisSpacing: 16,
                childAspectRatio: 1.1,
                children: [
                  DashboardTile(
                    icon: Icons.today,
                    label: "Today's\nEdition",
                    color: KnColors.orange,
                    badge: todayEdition.whenOrNull(data: (e) => e != null ? 'NEW' : null),
                    onTap: () {
                      final edition = todayEdition.valueOrNull;
                      if (edition != null && edition.htmlUrl != null) {
                        context.push('/reader', extra: {
                          'url': edition.htmlUrl,
                          'title': edition.title,
                        });
                      } else {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('No edition available today')),
                        );
                      }
                    },
                  ),
                  DashboardTile(
                    icon: Icons.library_books,
                    label: 'Archives',
                    color: const Color(0xFF3B82F6),
                    onTap: () => context.push('/archives'),
                  ),
                  DashboardTile(
                    icon: Icons.format_quote,
                    label: 'Quote of\nthe Day',
                    color: const Color(0xFF8B5CF6),
                    onTap: () => _showQuote(context, quote),
                  ),
                  DashboardTile(
                    icon: Icons.star,
                    label: 'Subscribe',
                    color: const Color(0xFF10B981),
                    badge: isSubscribed ? 'ACTIVE' : null,
                    onTap: () => context.push('/subscribe'),
                  ),
                  DashboardTile(
                    icon: Icons.auto_awesome,
                    label: 'Special\nEditions',
                    color: const Color(0xFFF59E0B),
                    onTap: () => context.push('/archives', extra: {'type': 'special'}),
                  ),
                  DashboardTile(
                    icon: Icons.campaign,
                    label: 'Advertise',
                    color: const Color(0xFFEF4444),
                    onTap: () => _showAdvertise(context),
                  ),
                ],
              ),

              const SizedBox(height: 24),

              // Quote card (if available)
              quote.when(
                data: (q) {
                  if (q == null) return const SizedBox.shrink();
                  return Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      gradient: KnColors.primaryGradient,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(Icons.format_quote, color: KnColors.orange, size: 32),
                        const SizedBox(height: 8),
                        Text(
                          q['quote'] ?? '',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontStyle: FontStyle.italic,
                            height: 1.5,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          '— ${q['author'] ?? 'Unknown'}',
                          style: TextStyle(
                            color: Colors.white.withAlpha(179),
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  );
                },
                loading: () => const SizedBox.shrink(),
                error: (_, __) => const SizedBox.shrink(),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showQuote(BuildContext context, AsyncValue<Map<String, dynamic>?> quote) {
    final q = quote.valueOrNull;
    if (q == null) return;
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Quote of the Day'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.format_quote, color: KnColors.orange, size: 40),
            const SizedBox(height: 16),
            Text(
              q['quote'] ?? '',
              style: const TextStyle(
                fontSize: 16,
                fontStyle: FontStyle.italic,
                height: 1.5,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 12),
            Text(
              '— ${q['author'] ?? 'Unknown'}',
              style: const TextStyle(fontWeight: FontWeight.w600),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }

  void _showAdvertise(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Advertise with Us'),
        content: const Text(
          'Reach thousands of readers across Africa.\n\n'
          'Contact: ads@kandanews.africa\n'
          'WhatsApp: +256 XXX XXX XXX',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }
}

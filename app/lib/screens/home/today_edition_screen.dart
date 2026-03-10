import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:intl/intl.dart';
import '../../models/edition.dart';
import '../../providers/auth_provider.dart';
import '../../providers/edition_provider.dart';
import '../../providers/subscription_provider.dart';
import '../../theme/kn_theme.dart';

class TodayEditionScreen extends ConsumerWidget {
  const TodayEditionScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final country = ref.watch(authProvider).user?.country ?? 'ug';
    final todayAsync = ref.watch(todayEditionProvider(country));
    final subscription = ref.watch(subscriptionStatusProvider);
    final isSubscribed = subscription.whenOrNull(
          data: (sub) => sub?.isActive ?? false,
        ) ??
        false;

    final today = DateTime.now();
    final dateLabel = DateFormat('EEEE, MMMM d, yyyy').format(today);

    return Scaffold(
      backgroundColor: const Color(0xFFF5F6FA),
      appBar: AppBar(
        title: const Text("Today's Edition"),
        backgroundColor: KnColors.orange,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: todayAsync.when(
        loading: () => const Center(
          child: CircularProgressIndicator(color: KnColors.orange),
        ),
        error: (e, _) => _ErrorState(onRetry: () => ref.invalidate(todayEditionProvider(country))),
        data: (edition) => edition == null
            ? _NoEditionState(dateLabel: dateLabel, country: country, ref: ref)
            : _EditionTile(
                edition: edition,
                dateLabel: dateLabel,
                isSubscribed: isSubscribed,
              ),
      ),
    );
  }
}

// ─── Main edition tile ─────────────────────────────────────────────────────

class _EditionTile extends StatelessWidget {
  final Edition edition;
  final String dateLabel;
  final bool isSubscribed;

  const _EditionTile({
    required this.edition,
    required this.dateLabel,
    required this.isSubscribed,
  });

  @override
  Widget build(BuildContext context) {
    final canRead = edition.accessible || edition.isFree || isSubscribed;

    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Orange header banner with date
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [KnColors.orange, Color(0xFFFF7A3D)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.calendar_today, color: Colors.white70, size: 14),
                    const SizedBox(width: 8),
                    Text(
                      dateLabel,
                      style: const TextStyle(
                        color: Colors.white70,
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                const Text(
                  "Today's Edition",
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 22,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  edition.title,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 14,
                    height: 1.4,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),

          // Cover thumbnail tile
          Padding(
            padding: const EdgeInsets.all(20),
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: KnColors.navy.withAlpha(30),
                    blurRadius: 24,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(20),
                child: edition.coverImage != null
                    ? CachedNetworkImage(
                        imageUrl: edition.coverImage!,
                        width: double.infinity,
                        fit: BoxFit.cover,
                        placeholder: (_, __) => _CoverPlaceholder(title: edition.title),
                        errorWidget: (_, __, ___) => _CoverPlaceholder(title: edition.title),
                      )
                    : _CoverPlaceholder(title: edition.title),
              ),
            ),
          ),

          // Edition meta info
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Row(
              children: [
                if (edition.isFree)
                  _MetaChip(
                    label: 'FREE ACCESS',
                    color: KnColors.success,
                    icon: Icons.lock_open,
                  )
                else if (canRead)
                  _MetaChip(
                    label: 'SUBSCRIBED',
                    color: KnColors.success,
                    icon: Icons.check_circle,
                  )
                else
                  _MetaChip(
                    label: 'SUBSCRIBE TO READ',
                    color: KnColors.orange,
                    icon: Icons.star,
                  ),
                const SizedBox(width: 8),
                if (edition.pageCount > 0)
                  _MetaChip(
                    label: '${edition.pageCount} pages',
                    color: KnColors.navy,
                    icon: Icons.auto_stories,
                  ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // Action buttons
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: canRead
                ? SizedBox(
                    height: 58,
                    child: ElevatedButton.icon(
                      onPressed: edition.htmlUrl != null
                          ? () => context.push('/reader', extra: {
                                'url': edition.htmlUrl,
                                'title': edition.title,
                              })
                          : null,
                      icon: const Icon(Icons.menu_book, size: 22),
                      label: const Text(
                        'Read Now',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: KnColors.orange,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                        elevation: 4,
                        shadowColor: KnColors.orange.withAlpha(80),
                      ),
                    ),
                  )
                : Column(
                    children: [
                      SizedBox(
                        height: 58,
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: () => context.push('/subscribe'),
                          icon: const Icon(Icons.star, size: 22),
                          label: const Text(
                            'Subscribe to Read',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
                          ),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: KnColors.orange,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(14),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
          ),

          if (!canRead && edition.htmlUrl != null) ...[
            const SizedBox(height: 12),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: OutlinedButton.icon(
                onPressed: () => context.push('/reader', extra: {
                  'url': edition.htmlUrl,
                  'title': edition.title,
                }),
                icon: const Icon(Icons.preview, size: 18),
                label: const Text('Preview Edition'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: KnColors.navy,
                  side: const BorderSide(color: KnColors.border),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                ),
              ),
            ),
          ],

          const SizedBox(height: 32),
        ],
      ),
    );
  }
}

class _MetaChip extends StatelessWidget {
  final String label;
  final Color color;
  final IconData icon;

  const _MetaChip({required this.label, required this.color, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color.withAlpha(20),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withAlpha(60)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: color),
          const SizedBox(width: 5),
          Text(
            label,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w700,
              color: color,
              letterSpacing: 0.3,
            ),
          ),
        ],
      ),
    );
  }
}

class _CoverPlaceholder extends StatelessWidget {
  final String title;
  const _CoverPlaceholder({required this.title});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 400,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [KnColors.navy, Color(0xFF2A3F5F)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.newspaper, size: 72, color: Colors.white24),
          const SizedBox(height: 16),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: Colors.white60,
                fontSize: 16,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ─── No edition state ──────────────────────────────────────────────────────

class _NoEditionState extends StatelessWidget {
  final String dateLabel;
  final String country;
  final WidgetRef ref;

  const _NoEditionState({
    required this.dateLabel,
    required this.country,
    required this.ref,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 96,
              height: 96,
              decoration: BoxDecoration(
                color: KnColors.orange.withAlpha(20),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.newspaper_outlined, size: 52, color: KnColors.orange),
            ),
            const SizedBox(height: 24),
            const Text(
              'No Edition Today',
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w800,
                color: KnColors.navy,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'No edition has been published for $dateLabel.\nCheck back later or browse the archives.',
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: KnColors.textSecondary,
                fontSize: 14,
                height: 1.6,
              ),
            ),
            const SizedBox(height: 28),
            ElevatedButton.icon(
              onPressed: () => context.push('/archives'),
              icon: const Icon(Icons.library_books),
              label: const Text('Browse Archives'),
              style: ElevatedButton.styleFrom(
                backgroundColor: KnColors.navy,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
              ),
            ),
            const SizedBox(height: 12),
            TextButton(
              onPressed: () => ref.invalidate(todayEditionProvider(country)),
              child: const Text('Refresh', style: TextStyle(color: KnColors.orange)),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Error state ──────────────────────────────────────────────────────────

class _ErrorState extends StatelessWidget {
  final VoidCallback onRetry;
  const _ErrorState({required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.wifi_off, size: 64, color: KnColors.textMuted),
            const SizedBox(height: 16),
            const Text(
              'Unable to load edition',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w700,
                color: KnColors.navy,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Check your internet connection and try again.',
              textAlign: TextAlign.center,
              style: TextStyle(color: KnColors.textSecondary, fontSize: 14),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh),
              label: const Text('Try Again'),
              style: ElevatedButton.styleFrom(
                backgroundColor: KnColors.orange,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

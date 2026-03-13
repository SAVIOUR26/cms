import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../models/kanda_event.dart';
import '../../models/poll.dart';
import '../../providers/auth_provider.dart';
import '../../providers/content_provider.dart';
import '../../theme/kn_theme.dart';

class PollsEventsScreen extends ConsumerStatefulWidget {
  const PollsEventsScreen({super.key});

  @override
  ConsumerState<PollsEventsScreen> createState() => _PollsEventsScreenState();
}

class _PollsEventsScreenState extends ConsumerState<PollsEventsScreen>
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

  String _flag(String code) {
    const flags = {
      'ug': '\u{1F1FA}\u{1F1EC}',
      'ke': '\u{1F1F0}\u{1F1EA}',
      'ng': '\u{1F1F3}\u{1F1EC}',
      'za': '\u{1F1FF}\u{1F1E6}',
    };
    return flags[code.toLowerCase()] ?? '\u{1F30D}';
  }

  @override
  Widget build(BuildContext context) {
    final country = ref.watch(authProvider).user?.country ?? 'ug';
    final flag    = _flag(country);

    return Scaffold(
      backgroundColor: const Color(0xFFF0F2F5),
      appBar: AppBar(
        title: Text('$flag Polls & Events'),
        backgroundColor: KnColors.orange,
        foregroundColor: Colors.white,
        elevation: 0,
        bottom: TabBar(
          controller: _tabs,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white60,
          labelStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
          tabs: const [
            Tab(icon: Icon(Icons.how_to_vote_outlined, size: 18), text: 'Polls'),
            Tab(icon: Icon(Icons.event, size: 18), text: 'Events'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabs,
        children: [
          _PollsTab(country: country),
          _EventsTab(country: country),
        ],
      ),
    );
  }
}

// =============================================================================
// TAB 1 — POLLS
// =============================================================================

class _PollsTab extends ConsumerWidget {
  final String country;
  const _PollsTab({required this.country});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final pollsState = ref.watch(pollsProvider(country));

    return pollsState.when(
      loading: () => const Center(
        child: CircularProgressIndicator(color: KnColors.orange),
      ),
      error: (_, __) => _RetryState(
        icon: Icons.how_to_vote_outlined,
        message: 'Could not load polls.\nCheck your connection.',
        onRetry: () => ref.read(pollsProvider(country).notifier).refresh(),
      ),
      data: (polls) {
        if (polls.isEmpty) {
          return const _EmptyState(
            icon: Icons.how_to_vote_outlined,
            title: 'No Active Polls',
            subtitle: 'Voting campaigns will appear here.\nCheck back soon.',
          );
        }
        return RefreshIndicator(
          color: KnColors.orange,
          onRefresh: () => ref.read(pollsProvider(country).notifier).refresh(),
          child: ListView.separated(
            padding: const EdgeInsets.fromLTRB(16, 20, 16, 32),
            itemCount: polls.length,
            separatorBuilder: (_, __) => const SizedBox(height: 16),
            itemBuilder: (ctx, i) => _PollCard(
              poll: polls[i],
              onVote: (optionId) async {
                try {
                  await ref
                      .read(pollsProvider(country).notifier)
                      .vote(pollId: polls[i].id, optionId: optionId);
                } catch (e) {
                  if (ctx.mounted) {
                    ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(
                      content: Text(
                          e.toString().replaceFirst('Exception: ', '')),
                      backgroundColor: KnColors.error,
                    ));
                  }
                }
              },
            ),
          ),
        );
      },
    );
  }
}

class _PollCard extends StatelessWidget {
  final Poll poll;
  final Future<void> Function(int optionId) onVote;

  const _PollCard({required this.poll, required this.onVote});

  @override
  Widget build(BuildContext context) {
    final hasVoted = poll.userHasVoted;
    final maxVotes = poll.options.isNotEmpty
        ? poll.options.map((o) => o.votes).reduce((a, b) => a > b ? a : b)
        : 0;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withAlpha(10),
              blurRadius: 12,
              offset: const Offset(0, 4)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Optional banner / cover image
          if (poll.coverImageUrl != null)
            ClipRRect(
              borderRadius:
                  const BorderRadius.vertical(top: Radius.circular(16)),
              child: CachedNetworkImage(
                imageUrl: poll.coverImageUrl!,
                height: 140,
                fit: BoxFit.cover,
                errorWidget: (_, __, ___) => const SizedBox.shrink(),
              ),
            ),

          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Status + vote count
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: (poll.status == 'closed'
                                ? KnColors.textMuted
                                : KnColors.orange)
                            .withAlpha(20),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        poll.status == 'closed' ? 'CLOSED' : 'ACTIVE POLL',
                        style: TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.w800,
                          color: poll.status == 'closed'
                              ? KnColors.textMuted
                              : KnColors.orange,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ),
                    const Spacer(),
                    Text('${poll.totalVotes} votes',
                        style: const TextStyle(
                            fontSize: 11, color: KnColors.textMuted)),
                  ],
                ),

                const SizedBox(height: 10),
                Text(poll.question,
                    style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: KnColors.navy,
                        height: 1.4)),

                if (poll.description != null &&
                    poll.description!.isNotEmpty) ...[
                  const SizedBox(height: 6),
                  Text(poll.description!,
                      style: const TextStyle(
                          fontSize: 12,
                          color: KnColors.textSecondary,
                          height: 1.4)),
                ],

                if (poll.endsAt != null) ...[
                  const SizedBox(height: 8),
                  _CountdownChip(endsAt: poll.endsAt!),
                ],

                const SizedBox(height: 14),

                // Options
                ...poll.options.map((opt) => _PollOptionRow(
                      option: opt,
                      totalVotes: poll.totalVotes,
                      hasVoted: hasVoted,
                      isSelected: poll.userVoteOptionId == opt.id,
                      isWinner: hasVoted && opt.votes == maxVotes && opt.votes > 0,
                      canVote: !hasVoted && poll.status == 'active',
                      onTap: (!hasVoted && poll.status == 'active')
                          ? () => onVote(opt.id)
                          : null,
                    )),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _PollOptionRow extends StatelessWidget {
  final PollOption option;
  final int totalVotes;
  final bool hasVoted;
  final bool isSelected;
  final bool isWinner;
  final bool canVote;
  final VoidCallback? onTap;

  const _PollOptionRow({
    required this.option,
    required this.totalVotes,
    required this.hasVoted,
    required this.isSelected,
    required this.isWinner,
    required this.canVote,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final pct = totalVotes > 0 ? option.votes / totalVotes : 0.0;

    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: GestureDetector(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 300),
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: BoxDecoration(
            color: isSelected
                ? KnColors.orange.withAlpha(15)
                : Colors.grey.withAlpha(10),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: isSelected
                  ? KnColors.orange
                  : isWinner
                      ? KnColors.success.withAlpha(80)
                      : Colors.grey.withAlpha(30),
              width: isSelected || isWinner ? 1.5 : 1,
            ),
          ),
          child: Stack(
            children: [
              // Progress bar fill
              if (hasVoted)
                Positioned.fill(
                  child: FractionallySizedBox(
                    alignment: Alignment.centerLeft,
                    widthFactor: pct,
                    child: Container(
                      decoration: BoxDecoration(
                        color: isWinner
                            ? KnColors.success.withAlpha(20)
                            : isSelected
                                ? KnColors.orange.withAlpha(20)
                                : Colors.grey.withAlpha(15),
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                  ),
                ),
              Row(
                children: [
                  // Profile pic (candidate photo)
                  if (option.imageUrl != null) ...[
                    ClipOval(
                      child: CachedNetworkImage(
                        imageUrl: option.imageUrl!,
                        width: 34,
                        height: 34,
                        fit: BoxFit.cover,
                        errorWidget: (_, __, ___) => Container(
                          width: 34,
                          height: 34,
                          decoration: BoxDecoration(
                            color: KnColors.navy.withAlpha(20),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(Icons.person,
                              size: 18, color: KnColors.navy),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                  ],
                  Expanded(
                    child: Text(
                      option.text,
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight:
                            isSelected ? FontWeight.w700 : FontWeight.w500,
                        color:
                            isSelected ? KnColors.orange : KnColors.navy,
                      ),
                    ),
                  ),
                  if (hasVoted) ...[
                    if (isWinner)
                      const Padding(
                        padding: EdgeInsets.only(right: 4),
                        child: Icon(Icons.emoji_events,
                            size: 14, color: KnColors.success),
                      ),
                    Text(
                      '${option.percentage.toStringAsFixed(0)}%',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: isWinner
                            ? KnColors.success
                            : isSelected
                                ? KnColors.orange
                                : KnColors.textMuted,
                      ),
                    ),
                  ] else if (canVote)
                    Container(
                      width: 18,
                      height: 18,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border:
                            Border.all(color: Colors.grey.withAlpha(60)),
                      ),
                    ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _CountdownChip extends StatelessWidget {
  final String endsAt;
  const _CountdownChip({required this.endsAt});

  @override
  Widget build(BuildContext context) {
    try {
      final diff = DateTime.parse(endsAt).difference(DateTime.now());
      if (diff.isNegative) return const SizedBox.shrink();
      final label = diff.inDays > 0
          ? 'Ends in ${diff.inDays}d ${diff.inHours.remainder(24)}h'
          : diff.inHours > 0
              ? 'Ends in ${diff.inHours}h ${diff.inMinutes.remainder(60)}m'
              : 'Ends in ${diff.inMinutes}m';
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(
          color: KnColors.navy.withAlpha(12),
          borderRadius: BorderRadius.circular(6),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.timer_outlined,
                size: 12, color: KnColors.navy),
            const SizedBox(width: 4),
            Text(label,
                style: const TextStyle(
                    fontSize: 11,
                    color: KnColors.navy,
                    fontWeight: FontWeight.w600)),
          ],
        ),
      );
    } catch (_) {
      return const SizedBox.shrink();
    }
  }
}

// =============================================================================
// TAB 2 — EVENTS
// =============================================================================

class _EventsTab extends ConsumerWidget {
  final String country;
  const _EventsTab({required this.country});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final eventsAsync = ref.watch(eventsProvider(country));

    return eventsAsync.when(
      loading: () => const Center(
          child: CircularProgressIndicator(color: KnColors.orange)),
      error: (_, __) => _RetryState(
        icon: Icons.event_busy,
        message: 'Could not load events.\nCheck your connection.',
        onRetry: () => ref.invalidate(eventsProvider(country)),
      ),
      data: (events) {
        if (events.isEmpty) {
          return const _EmptyState(
            icon: Icons.event_outlined,
            title: 'No Upcoming Events',
            subtitle: 'Events & conferences will appear here.\nStay tuned.',
          );
        }
        return RefreshIndicator(
          color: KnColors.orange,
          onRefresh: () async => ref.invalidate(eventsProvider(country)),
          child: ListView.separated(
            padding: const EdgeInsets.fromLTRB(16, 20, 16, 32),
            itemCount: events.length,
            separatorBuilder: (_, __) => const SizedBox(height: 16),
            itemBuilder: (_, i) => _EventCard(event: events[i]),
          ),
        );
      },
    );
  }
}

class _EventCard extends StatelessWidget {
  final KandaEvent event;
  const _EventCard({required this.event});

  @override
  Widget build(BuildContext context) {
    String formattedDate = event.eventDate;
    try {
      formattedDate =
          DateFormat('EEE, MMM d · h:mm a').format(DateTime.parse(event.eventDate));
    } catch (_) {}

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withAlpha(10),
              blurRadius: 12,
              offset: const Offset(0, 4)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Cover banner
          ClipRRect(
            borderRadius:
                const BorderRadius.vertical(top: Radius.circular(16)),
            child: event.coverImageUrl != null
                ? CachedNetworkImage(
                    imageUrl: event.coverImageUrl!,
                    height: 160,
                    fit: BoxFit.cover,
                    errorWidget: (_, __, ___) =>
                        _EventBannerFallback(category: event.categoryLabel),
                  )
                : _EventBannerFallback(category: event.categoryLabel),
          ),

          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Badges row
                Wrap(
                  spacing: 6,
                  children: [
                    _Badge(
                        label: event.categoryLabel.toUpperCase(),
                        color: KnColors.navy),
                    if (event.isFree)
                      const _Badge(label: 'FREE', color: KnColors.success),
                    if (event.isOnline)
                      const _Badge(label: 'ONLINE', color: KnColors.orange),
                    if (event.isPast)
                      const _Badge(
                          label: 'PAST EVENT', color: KnColors.textMuted),
                  ],
                ),

                const SizedBox(height: 10),
                Text(event.title,
                    style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                        color: KnColors.navy,
                        height: 1.3)),

                const SizedBox(height: 8),
                _IconRow(icon: Icons.calendar_today, text: formattedDate),

                if (event.location != null) ...[
                  const SizedBox(height: 4),
                  _IconRow(
                    icon: event.isOnline ? Icons.videocam : Icons.place,
                    text: event.location!,
                  ),
                ],

                if (event.description != null &&
                    event.description!.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Text(event.description!,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                          fontSize: 12,
                          color: KnColors.textSecondary,
                          height: 1.5)),
                ],

                if (event.registrationUrl != null && !event.isPast) ...[
                  const SizedBox(height: 14),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: () async {
                        final uri = Uri.tryParse(event.registrationUrl!);
                        if (uri != null && await canLaunchUrl(uri)) {
                          await launchUrl(uri,
                              mode: LaunchMode.externalApplication);
                        }
                      },
                      icon: const Icon(Icons.open_in_new, size: 16),
                      label: const Text('Register / Learn More',
                          style: TextStyle(
                              fontSize: 13, fontWeight: FontWeight.w700)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: KnColors.orange,
                        foregroundColor: Colors.white,
                        padding:
                            const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _EventBannerFallback extends StatelessWidget {
  final String category;
  const _EventBannerFallback({required this.category});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 100,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [KnColors.navy, Color(0xFF2A3F5F)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.event, size: 36, color: Colors.white30),
            const SizedBox(height: 6),
            Text(category,
                style: const TextStyle(
                    color: Colors.white38,
                    fontSize: 12,
                    fontWeight: FontWeight.w600)),
          ],
        ),
      ),
    );
  }
}

// =============================================================================
// Shared small widgets
// =============================================================================

class _Badge extends StatelessWidget {
  final String label;
  final Color color;
  const _Badge({required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color: color.withAlpha(20),
        borderRadius: BorderRadius.circular(5),
        border: Border.all(color: color.withAlpha(50)),
      ),
      child: Text(label,
          style: TextStyle(
              fontSize: 9,
              fontWeight: FontWeight.w800,
              color: color,
              letterSpacing: 0.4)),
    );
  }
}

class _IconRow extends StatelessWidget {
  final IconData icon;
  final String text;
  const _IconRow({required this.icon, required this.text});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 13, color: KnColors.orange),
        const SizedBox(width: 6),
        Expanded(
          child: Text(text,
              style: const TextStyle(
                  fontSize: 12,
                  color: KnColors.textSecondary,
                  fontWeight: FontWeight.w500)),
        ),
      ],
    );
  }
}

class _EmptyState extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  const _EmptyState(
      {required this.icon, required this.title, required this.subtitle});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                  color: KnColors.orange.withAlpha(20),
                  shape: BoxShape.circle),
              child: Icon(icon, size: 40, color: KnColors.orange),
            ),
            const SizedBox(height: 20),
            Text(title,
                style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w800,
                    color: KnColors.navy)),
            const SizedBox(height: 8),
            Text(subtitle,
                textAlign: TextAlign.center,
                style: const TextStyle(
                    fontSize: 13,
                    color: KnColors.textSecondary,
                    height: 1.6)),
          ],
        ),
      ),
    );
  }
}

class _RetryState extends StatelessWidget {
  final IconData icon;
  final String message;
  final VoidCallback onRetry;
  const _RetryState(
      {required this.icon, required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 52, color: KnColors.textMuted),
            const SizedBox(height: 16),
            Text(message,
                textAlign: TextAlign.center,
                style: const TextStyle(
                    fontSize: 14,
                    color: KnColors.textSecondary,
                    height: 1.5)),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh),
              label: const Text('Try Again'),
              style: ElevatedButton.styleFrom(
                  backgroundColor: KnColors.orange,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10))),
            ),
          ],
        ),
      ),
    );
  }
}

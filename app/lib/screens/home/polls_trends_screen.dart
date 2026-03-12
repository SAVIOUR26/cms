import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../providers/auth_provider.dart';
import '../../theme/kn_theme.dart';

class PollsTrendsScreen extends ConsumerWidget {
  const PollsTrendsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final country = ref.watch(authProvider).user?.country ?? 'ug';
    final flag = _countryFlag(country);
    final countryName = _countryName(country);

    return Scaffold(
      backgroundColor: const Color(0xFFF0F2F5),
      appBar: AppBar(
        title: Text('$flag Polls & Events'),
        backgroundColor: KnColors.orange,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Upper section: Trends
          Expanded(
            flex: 1,
            child: _TrendsSection(countryName: countryName, flag: flag),
          ),
          // Lower section: Ongoing Polls
          Expanded(
            flex: 1,
            child: _PollsSection(countryName: countryName),
          ),
        ],
      ),
    );
  }

  String _countryName(String code) {
    const names = {
      'ug': 'Uganda',
      'ke': 'Kenya',
      'ng': 'Nigeria',
      'za': 'South Africa',
    };
    return names[code.toLowerCase()] ?? 'Africa';
  }

  String _countryFlag(String code) {
    const flags = {
      'ug': '\u{1F1FA}\u{1F1EC}',
      'ke': '\u{1F1F0}\u{1F1EA}',
      'ng': '\u{1F1F3}\u{1F1EC}',
      'za': '\u{1F1FF}\u{1F1E6}',
    };
    return flags[code.toLowerCase()] ?? '\u{1F30D}';
  }
}

// ─── Trends section (upper, orange-navy gradient) ─────────────────────────

class _TrendsSection extends StatelessWidget {
  final String countryName;
  final String flag;

  const _TrendsSection({required this.countryName, required this.flag});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [KnColors.navy, Color(0xFF2A3F5F)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Section label bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
            decoration: BoxDecoration(
              color: Colors.white.withAlpha(10),
              border: const Border(
                bottom: BorderSide(color: Colors.white12),
              ),
            ),
            child: Row(
              children: [
                Container(
                  width: 4,
                  height: 22,
                  decoration: BoxDecoration(
                    color: KnColors.orange,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
                const SizedBox(width: 12),
                const Text(
                  'TRENDS',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 1.2,
                  ),
                ),
                const Spacer(),
                Text(
                  '$flag $countryName',
                  style: const TextStyle(
                    color: Colors.white60,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),

          // Trends content
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              children: const [
                _TrendItem(rank: 1, topic: 'University Elections 2025', count: '12.4K discussions'),
                _TrendItem(rank: 2, topic: 'African Business Summit', count: '8.2K discussions'),
                _TrendItem(rank: 3, topic: 'Campus Innovation Week', count: '6.7K discussions'),
                _TrendItem(rank: 4, topic: 'Graduate Employment Forum', count: '4.1K discussions'),
                _TrendItem(rank: 5, topic: 'Entrepreneurship Awards', count: '3.8K discussions'),
              ],
            ),
          ),

          // Coming soon banner
          Container(
            margin: const EdgeInsets.fromLTRB(16, 0, 16, 12),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            decoration: BoxDecoration(
              color: KnColors.orange.withAlpha(30),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: KnColors.orange.withAlpha(60)),
            ),
            child: const Row(
              children: [
                Icon(Icons.update, color: KnColors.orange, size: 14),
                SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Live trending topics — coming soon with real-time updates',
                    style: TextStyle(
                      color: Colors.white70,
                      fontSize: 11,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _TrendItem extends StatelessWidget {
  final int rank;
  final String topic;
  final String count;

  const _TrendItem({required this.rank, required this.topic, required this.count});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        children: [
          SizedBox(
            width: 32,
            child: Text(
              '#$rank',
              style: TextStyle(
                color: rank == 1 ? KnColors.orange : Colors.white38,
                fontSize: 13,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  topic,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                Text(
                  count,
                  style: const TextStyle(
                    color: Colors.white38,
                    fontSize: 11,
                  ),
                ),
              ],
            ),
          ),
          Icon(
            Icons.trending_up,
            color: rank <= 2 ? KnColors.orange : Colors.white24,
            size: 16,
          ),
        ],
      ),
    );
  }
}

// ─── Polls section (lower, light background) ─────────────────────────────

class _PollsSection extends StatefulWidget {
  final String countryName;
  const _PollsSection({required this.countryName});

  @override
  State<_PollsSection> createState() => _PollsSectionState();
}

class _PollsSectionState extends State<_PollsSection> {
  // Track voted options per poll
  final Map<String, int?> _votes = {};

  @override
  Widget build(BuildContext context) {
    return Container(
      color: const Color(0xFFF0F4FF),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Section label bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
            decoration: const BoxDecoration(
              color: Colors.white,
              border: Border(
                bottom: BorderSide(color: Color(0xFFE5E7EB)),
              ),
            ),
            child: Row(
              children: [
                Container(
                  width: 4,
                  height: 22,
                  decoration: BoxDecoration(
                    color: const Color(0xFF3B82F6),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
                const SizedBox(width: 12),
                const Text(
                  'ONGOING POLLS',
                  style: TextStyle(
                    color: KnColors.navy,
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 1.2,
                  ),
                ),
                const Spacer(),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFF3B82F6).withAlpha(20),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Text(
                    'Vote now',
                    style: TextStyle(
                      color: Color(0xFF3B82F6),
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Polls list
          Expanded(
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                _PollCard(
                  pollId: 'poll_1',
                  question: 'Who should be the Face of Campus Edition 2025?',
                  options: const ['Aisha M. (Makerere)', 'Brian K. (KYU)', 'Diana N. (MUBS)', 'Samuel O. (Gulu U)'],
                  votes: const [342, 198, 267, 145],
                  votedIndex: _votes['poll_1'],
                  onVote: (i) => setState(() => _votes['poll_1'] = i),
                ),
                const SizedBox(height: 12),
                _PollCard(
                  pollId: 'poll_2',
                  question: 'Best Corporate Innovator of the Quarter?',
                  options: const ['Tech Startup A', 'Corporate B', 'SME Champion C'],
                  votes: const [520, 310, 180],
                  votedIndex: _votes['poll_2'],
                  onVote: (i) => setState(() => _votes['poll_2'] = i),
                ),
              ],
            ),
          ),

          // Coming soon banner
          Container(
            margin: const EdgeInsets.fromLTRB(16, 0, 16, 12),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            decoration: BoxDecoration(
              color: const Color(0xFF3B82F6).withAlpha(15),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: const Color(0xFF3B82F6).withAlpha(40)),
            ),
            child: const Row(
              children: [
                Icon(Icons.how_to_vote_outlined, color: Color(0xFF3B82F6), size: 14),
                SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'University & corporate voting for edition faces — expanded features coming soon',
                    style: TextStyle(
                      color: Color(0xFF4B5563),
                      fontSize: 11,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _PollCard extends StatelessWidget {
  final String pollId;
  final String question;
  final List<String> options;
  final List<int> votes;
  final int? votedIndex;
  final void Function(int) onVote;

  const _PollCard({
    required this.pollId,
    required this.question,
    required this.options,
    required this.votes,
    required this.votedIndex,
    required this.onVote,
  });

  @override
  Widget build(BuildContext context) {
    final totalVotes = votes.fold(0, (a, b) => a + b);
    final hasVoted = votedIndex != null;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha(8),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: KnColors.orange.withAlpha(20),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: const Text(
                  'ACTIVE POLL',
                  style: TextStyle(
                    fontSize: 9,
                    fontWeight: FontWeight.w800,
                    color: KnColors.orange,
                    letterSpacing: 0.5,
                  ),
                ),
              ),
              const Spacer(),
              Text(
                '$totalVotes votes',
                style: const TextStyle(
                  fontSize: 11,
                  color: KnColors.textMuted,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            question,
            style: const TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w700,
              color: KnColors.navy,
              height: 1.4,
            ),
          ),
          const SizedBox(height: 12),
          ...List.generate(options.length, (i) {
            final pct = totalVotes > 0 ? votes[i] / totalVotes : 0.0;
            final isWinner = hasVoted && votes[i] == votes.reduce((a, b) => a > b ? a : b);
            final isSelected = votedIndex == i;

            return Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: GestureDetector(
                onTap: hasVoted ? null : () => onVote(i),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 300),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  decoration: BoxDecoration(
                    color: isSelected
                        ? KnColors.orange.withAlpha(15)
                        : Colors.grey.withAlpha(10),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: isSelected ? KnColors.orange : Colors.grey.withAlpha(30),
                      width: isSelected ? 1.5 : 1,
                    ),
                  ),
                  child: Stack(
                    children: [
                      // Progress bar background
                      if (hasVoted)
                        Positioned.fill(
                          child: FractionallySizedBox(
                            alignment: Alignment.centerLeft,
                            widthFactor: pct,
                            child: Container(
                              decoration: BoxDecoration(
                                color: isWinner
                                    ? KnColors.orange.withAlpha(20)
                                    : Colors.grey.withAlpha(15),
                                borderRadius: BorderRadius.circular(6),
                              ),
                            ),
                          ),
                        ),
                      // Option label and percentage
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              options[i],
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                                color: isSelected ? KnColors.orange : KnColors.navy,
                              ),
                            ),
                          ),
                          if (hasVoted) ...[
                            Text(
                              '${(pct * 100).toStringAsFixed(0)}%',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: isWinner ? KnColors.orange : KnColors.textMuted,
                              ),
                            ),
                          ] else
                            Container(
                              width: 18,
                              height: 18,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                border: Border.all(color: Colors.grey.withAlpha(60)),
                              ),
                            ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            );
          }),
        ],
      ),
    );
  }
}

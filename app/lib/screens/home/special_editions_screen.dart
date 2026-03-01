import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../theme/kn_theme.dart';

class SpecialEditionsScreen extends ConsumerWidget {
  const SpecialEditionsScreen({super.key});

  static const _categories = [
    _Category(
      label: 'University',
      icon: Icons.school,
      color: Color(0xFF3B82F6),
      filter: 'university',
    ),
    _Category(
      label: 'Corporate',
      icon: Icons.business,
      color: Color(0xFF1E2B42),
      filter: 'corporate',
    ),
    _Category(
      label: 'Entrepreneurship',
      icon: Icons.rocket_launch,
      color: Color(0xFFF05A1A),
      filter: 'entrepreneurship',
    ),
    _Category(
      label: 'Campaigns',
      icon: Icons.campaign,
      color: Color(0xFFEF4444),
      filter: 'campaigns',
    ),
    _Category(
      label: 'Jobs & Careers',
      icon: Icons.work,
      color: Color(0xFF10B981),
      filter: 'jobs_careers',
    ),
    _Category(
      label: 'Podcasts',
      icon: Icons.podcasts,
      color: Color(0xFF8B5CF6),
      filter: 'podcasts',
    ),
    _Category(
      label: 'Episodes',
      icon: Icons.play_circle_filled,
      color: Color(0xFFF59E0B),
      filter: 'episodes',
    ),
  ];

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Special Editions'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Browse by Category',
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w800,
                color: KnColors.navy,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Explore special editions across topics',
              style: TextStyle(
                color: KnColors.textSecondary,
                fontSize: 14,
              ),
            ),
            const SizedBox(height: 24),
            ..._categories.map((cat) => _CategoryTile(
                  category: cat,
                  onTap: () => context.push('/archives', extra: {
                    'type': 'special',
                    'category': cat.filter,
                    'title': cat.label,
                  }),
                )),
          ],
        ),
      ),
    );
  }
}

class _Category {
  final String label;
  final IconData icon;
  final Color color;
  final String filter;

  const _Category({
    required this.label,
    required this.icon,
    required this.color,
    required this.filter,
  });
}

class _CategoryTile extends StatelessWidget {
  final _Category category;
  final VoidCallback onTap;

  const _CategoryTile({
    required this.category,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: category.color.withAlpha(60),
                width: 2,
              ),
              boxShadow: [
                BoxShadow(
                  color: category.color.withAlpha(30),
                  blurRadius: 12,
                  offset: const Offset(0, 4),
                ),
                BoxShadow(
                  color: category.color.withAlpha(12),
                  blurRadius: 20,
                  spreadRadius: 1,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                // Icon
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: category.color.withAlpha(25),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Icon(
                    category.icon,
                    color: category.color,
                    size: 24,
                  ),
                ),
                const SizedBox(width: 16),
                // Label
                Expanded(
                  child: Text(
                    category.label,
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 17,
                      color: KnColors.navy,
                    ),
                  ),
                ),
                // Arrow
                Icon(
                  Icons.arrow_forward_ios,
                  color: category.color.withAlpha(150),
                  size: 18,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

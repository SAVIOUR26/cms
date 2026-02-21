import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../models/edition.dart';
import '../../providers/auth_provider.dart';
import '../../providers/edition_provider.dart';
import '../../theme/kn_theme.dart';

class ArchivesScreen extends ConsumerStatefulWidget {
  final String? filterType;

  const ArchivesScreen({super.key, this.filterType});

  @override
  ConsumerState<ArchivesScreen> createState() => _ArchivesScreenState();
}

class _ArchivesScreenState extends ConsumerState<ArchivesScreen> {
  String _typeFilter = 'all';

  @override
  void initState() {
    super.initState();
    if (widget.filterType != null) _typeFilter = widget.filterType!;
  }

  @override
  Widget build(BuildContext context) {
    final country = ref.watch(authProvider).user?.country ?? 'ug';
    final editionsAsync = ref.watch(editionsProvider({
      'country': country,
      'page': 1,
      'per_page': 50,
    }));

    return Scaffold(
      appBar: AppBar(
        title: Text(_typeFilter == 'special' ? 'Special Editions' : 'Archives'),
      ),
      body: Column(
        children: [
          // Filter chips
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _FilterChip(
                    label: 'All',
                    selected: _typeFilter == 'all',
                    onTap: () => setState(() => _typeFilter = 'all'),
                  ),
                  _FilterChip(
                    label: 'Daily',
                    selected: _typeFilter == 'daily',
                    onTap: () => setState(() => _typeFilter = 'daily'),
                  ),
                  _FilterChip(
                    label: 'Special',
                    selected: _typeFilter == 'special',
                    onTap: () => setState(() => _typeFilter = 'special'),
                  ),
                  _FilterChip(
                    label: 'Rate Card',
                    selected: _typeFilter == 'rate_card',
                    onTap: () => setState(() => _typeFilter = 'rate_card'),
                  ),
                ],
              ),
            ),
          ),

          // Editions grid
          Expanded(
            child: editionsAsync.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text('Error loading editions')),
              data: (data) {
                final editions = (data['editions'] as List<Edition>).where((e) {
                  if (_typeFilter == 'all') return true;
                  return e.editionType == _typeFilter;
                }).toList();

                if (editions.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.library_books_outlined,
                            size: 64, color: KnColors.textMuted),
                        const SizedBox(height: 16),
                        const Text(
                          'No editions found',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w600,
                            color: KnColors.textSecondary,
                          ),
                        ),
                      ],
                    ),
                  );
                }

                return GridView.builder(
                  padding: const EdgeInsets.all(16),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    childAspectRatio: 0.7,
                    mainAxisSpacing: 16,
                    crossAxisSpacing: 16,
                  ),
                  itemCount: editions.length,
                  itemBuilder: (ctx, i) => _EditionCard(edition: editions[i]),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _FilterChip extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _FilterChip({
    required this.label,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label),
        selected: selected,
        onSelected: (_) => onTap(),
        selectedColor: KnColors.orange.withAlpha(51),
        checkmarkColor: KnColors.orange,
        labelStyle: TextStyle(
          fontWeight: FontWeight.w600,
          color: selected ? KnColors.orange : KnColors.textSecondary,
        ),
        side: BorderSide(
          color: selected ? KnColors.orange : KnColors.border,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10),
        ),
      ),
    );
  }
}

class _EditionCard extends StatelessWidget {
  final Edition edition;

  const _EditionCard({required this.edition});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(14),
      elevation: 2,
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: () {
          if (edition.htmlUrl != null) {
            context.push('/reader', extra: {
              'url': edition.htmlUrl,
              'title': edition.title,
            });
          }
        },
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Cover image
            Expanded(
              flex: 3,
              child: ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(14)),
                child: edition.coverImage != null
                    ? CachedNetworkImage(
                        imageUrl: edition.coverImage!,
                        fit: BoxFit.cover,
                        placeholder: (_, __) => Container(
                          color: KnColors.navy.withAlpha(25),
                          child: const Icon(Icons.newspaper, size: 40, color: KnColors.navy),
                        ),
                        errorWidget: (_, __, ___) => _placeholderCover(),
                      )
                    : _placeholderCover(),
              ),
            ),

            // Info
            Expanded(
              flex: 2,
              child: Padding(
                padding: const EdgeInsets.all(10),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Type badge
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: KnColors.orange.withAlpha(25),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        edition.typeLabel,
                        style: const TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w700,
                          color: KnColors.orange,
                        ),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Flexible(
                      child: Text(
                        edition.title,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontWeight: FontWeight.w700,
                          fontSize: 13,
                          color: KnColors.navy,
                        ),
                      ),
                    ),
                    Text(
                      edition.editionDate,
                      style: const TextStyle(
                        fontSize: 11,
                        color: KnColors.textMuted,
                      ),
                    ),
                    if (edition.isFree)
                      const Text(
                        'FREE',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w800,
                          color: KnColors.success,
                        ),
                      ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _placeholderCover() {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [KnColors.navy, KnColors.navy.withAlpha(179)],
        ),
      ),
      child: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(edition.typeIcon, style: const TextStyle(fontSize: 32)),
            const SizedBox(height: 8),
            const Text(
              'KandaNews',
              style: TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w700,
                fontSize: 12,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

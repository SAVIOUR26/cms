import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/auth_provider.dart';
import '../../providers/content_provider.dart';
import '../../theme/kn_theme.dart';

class SpecialEditionsScreen extends ConsumerWidget {
  const SpecialEditionsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final country = ref.watch(authProvider).user?.country ?? 'ug';
    final catsAsync = ref.watch(editionCategoriesProvider(country));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Special Editions'),
      ),
      body: catsAsync.when(
        loading: () =>
            const Center(child: CircularProgressIndicator(color: KnColors.orange)),
        error: (_, __) => _ErrorState(
          onRetry: () => ref.invalidate(editionCategoriesProvider(country)),
        ),
        data: (categories) {
          if (categories.isEmpty) {
            return const Center(
              child: Text(
                'No categories available yet.',
                style: TextStyle(color: KnColors.textSecondary),
              ),
            );
          }
          return ListView(
            padding: const EdgeInsets.all(20),
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
              ...categories.map((cat) => Padding(
                    padding: const EdgeInsets.only(bottom: 14),
                    child: Material(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      child: InkWell(
                        onTap: () => context.push('/archives', extra: {
                          'type': cat.editionType,
                          'category': cat.slug,
                          'title': cat.label,
                        }),
                        borderRadius: BorderRadius.circular(16),
                        child: Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(
                              horizontal: 20, vertical: 18),
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: cat.color.withAlpha(60),
                              width: 2,
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: cat.color.withAlpha(30),
                                blurRadius: 12,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: Row(
                            children: [
                              Container(
                                width: 48,
                                height: 48,
                                decoration: BoxDecoration(
                                  color: cat.color.withAlpha(25),
                                  borderRadius: BorderRadius.circular(14),
                                ),
                                child: Icon(cat.icon,
                                    color: cat.color, size: 24),
                              ),
                              const SizedBox(width: 16),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment:
                                      CrossAxisAlignment.start,
                                  children: [
                                    Text(cat.label,
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w700,
                                          fontSize: 17,
                                          color: KnColors.navy,
                                        )),
                                    if (cat.description != null)
                                      Text(cat.description!,
                                          style: const TextStyle(
                                            fontSize: 12,
                                            color: KnColors.textSecondary,
                                          )),
                                  ],
                                ),
                              ),
                              Icon(Icons.arrow_forward_ios,
                                  color: cat.color.withAlpha(150),
                                  size: 18),
                            ],
                          ),
                        ),
                      ),
                    ),
                  )),
            ],
          );
        },
      ),
    );
  }
}

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
            const Icon(Icons.wifi_off, size: 52, color: KnColors.textMuted),
            const SizedBox(height: 16),
            const Text('Could not load categories',
                style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    color: KnColors.navy)),
            const SizedBox(height: 8),
            const Text('Check your connection and try again.',
                textAlign: TextAlign.center,
                style:
                    TextStyle(fontSize: 13, color: KnColors.textSecondary)),
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

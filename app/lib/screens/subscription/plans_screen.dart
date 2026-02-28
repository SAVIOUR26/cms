import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../models/subscription.dart';
import '../../providers/auth_provider.dart';
import '../../providers/subscription_provider.dart';
import '../../services/subscription_service.dart';
import '../../theme/kn_theme.dart';

class PlansScreen extends ConsumerStatefulWidget {
  const PlansScreen({super.key});

  @override
  ConsumerState<PlansScreen> createState() => _PlansScreenState();
}

class _PlansScreenState extends ConsumerState<PlansScreen> {
  String? _selectedPlan;
  bool _loading = false;

  static const _planColors = {
    'daily': Color(0xFF3B82F6),
    'weekly': Color(0xFFF05A1A),
    'monthly': Color(0xFF10B981),
  };

  static const _planIcons = {
    'daily': Icons.today,
    'weekly': Icons.date_range,
    'monthly': Icons.calendar_month,
  };

  @override
  Widget build(BuildContext context) {
    final country = ref.watch(authProvider).user?.country ?? 'ug';
    final plansAsync = ref.watch(plansProvider(country));
    final statusAsync = ref.watch(subscriptionStatusProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Subscribe')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Current status
            statusAsync.when(
              data: (sub) {
                if (sub != null && sub.isActive) {
                  return Container(
                    padding: const EdgeInsets.all(16),
                    margin: const EdgeInsets.only(bottom: 20),
                    decoration: BoxDecoration(
                      color: KnColors.success.withAlpha(25),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: KnColors.success.withAlpha(76)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.check_circle, color: KnColors.success),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '${sub.planLabel} Plan Active',
                                style: const TextStyle(
                                  fontWeight: FontWeight.w700,
                                  color: KnColors.navy,
                                ),
                              ),
                              Text(
                                'Expires: ${sub.expiresAt}',
                                style: const TextStyle(
                                  fontSize: 13,
                                  color: KnColors.textSecondary,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  );
                }
                return const SizedBox.shrink();
              },
              loading: () => const SizedBox.shrink(),
              error: (_, __) => const SizedBox.shrink(),
            ),

            const Text(
              'Choose Your Plan',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.w800,
                color: KnColors.navy,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Unlock unlimited access to all editions',
              style: TextStyle(color: KnColors.textSecondary),
            ),
            const SizedBox(height: 24),

            // Plans — Daily on top, Weekly middle, Monthly bottom
            plansAsync.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => const Text('Failed to load plans'),
              data: (plans) => Column(
                children: plans.map((plan) {
                  final color = _planColors[plan.plan] ?? KnColors.orange;
                  final icon = _planIcons[plan.plan] ?? Icons.payment;
                  return _PlanCard(
                    plan: plan,
                    color: color,
                    iconData: icon,
                    selected: _selectedPlan == plan.plan,
                    recommended: plan.plan == 'monthly',
                    onTap: () => setState(() => _selectedPlan = plan.plan),
                  );
                }).toList(),
              ),
            ),

            const SizedBox(height: 32),

            // Pay button
            SizedBox(
              height: 56,
              child: ElevatedButton(
                onPressed: _selectedPlan != null && !_loading ? _onSubscribe : null,
                child: _loading
                    ? const SizedBox(
                        width: 24,
                        height: 24,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : Text(_selectedPlan != null ? 'Pay Now' : 'Select a plan'),
              ),
            ),

            const SizedBox(height: 16),
            const Text(
              'Payment via Mobile Money or Card\nCancel anytime',
              textAlign: TextAlign.center,
              style: TextStyle(color: KnColors.textMuted, fontSize: 13),
            ),
          ],
        ),
      ),
    );
  }

  void _onSubscribe() async {
    setState(() => _loading = true);
    try {
      final service = ref.read(subscriptionServiceProvider);
      final result = await service.initiate(
        plan: _selectedPlan!,
        provider: 'flutterwave',
        phone: ref.read(authProvider).user?.phone,
      );

      if (result['ok'] == true && mounted) {
        final paymentData = result['data'];
        if (paymentData['link'] != null) {
          context.push('/payment', extra: {
            'url': paymentData['link'],
            'reference': paymentData['reference'],
          });
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Payment failed: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }
}

class _PlanCard extends StatelessWidget {
  final SubscriptionPlan plan;
  final Color color;
  final IconData iconData;
  final bool selected;
  final bool recommended;
  final VoidCallback onTap;

  const _PlanCard({
    required this.plan,
    required this.color,
    required this.iconData,
    required this.selected,
    required this.recommended,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: Material(
        color: selected ? color.withAlpha(13) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        elevation: selected ? 4 : 1,
        shadowColor: selected ? color.withAlpha(60) : Colors.black12,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: selected ? color : color.withAlpha(50),
                width: selected ? 2 : 1.5,
              ),
              boxShadow: selected
                  ? [
                      BoxShadow(
                        color: color.withAlpha(30),
                        blurRadius: 12,
                        offset: const Offset(0, 4),
                      ),
                    ]
                  : null,
            ),
            child: Row(
              children: [
                // Plan icon with colored background
                Container(
                  width: 52,
                  height: 52,
                  decoration: BoxDecoration(
                    color: color.withAlpha(25),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Icon(iconData, color: color, size: 26),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Text(
                            plan.label,
                            style: TextStyle(
                              fontWeight: FontWeight.w700,
                              fontSize: 16,
                              color: selected ? color : KnColors.navy,
                            ),
                          ),
                          if (recommended) ...[
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: color,
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Text(
                                'BEST VALUE',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 9,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        plan.formattedPrice,
                        style: const TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.w800,
                          color: KnColors.navy,
                        ),
                      ),
                    ],
                  ),
                ),
                if (selected)
                  Icon(Icons.check_circle, color: color, size: 28),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

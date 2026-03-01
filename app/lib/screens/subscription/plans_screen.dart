import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../models/subscription.dart';
import '../../providers/auth_provider.dart';
import '../../providers/subscription_provider.dart';
import '../../theme/kn_theme.dart';

class PlansScreen extends ConsumerStatefulWidget {
  const PlansScreen({super.key});

  @override
  ConsumerState<PlansScreen> createState() => _PlansScreenState();
}

class _PlansScreenState extends ConsumerState<PlansScreen> {
  String? _selectedPlan;
  String _provider = 'flutterwave';
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

            // Plans
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
                    onTap: () => setState(() => _selectedPlan = plan.plan),
                  );
                }).toList(),
              ),
            ),

            const SizedBox(height: 24),

            // Payment method selection
            const Text(
              'Payment Method',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w700,
                color: KnColors.navy,
              ),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: _ProviderChip(
                    label: 'Flutterwave',
                    subtitle: 'Mobile Money & Card',
                    icon: Icons.account_balance_wallet,
                    selected: _provider == 'flutterwave',
                    onTap: () => setState(() => _provider = 'flutterwave'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _ProviderChip(
                    label: 'DPO',
                    subtitle: 'Card Payment',
                    icon: Icons.credit_card,
                    selected: _provider == 'dpo',
                    onTap: () => setState(() => _provider = 'dpo'),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 28),

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
                    : Text(
                        _selectedPlan != null ? 'Pay Now' : 'Select a plan',
                        style: const TextStyle(fontSize: 16),
                      ),
              ),
            ),

            const SizedBox(height: 16),
            Text(
              _provider == 'flutterwave'
                  ? 'Secure payment via Mobile Money or Card'
                  : 'Secure card payment via DPO',
              textAlign: TextAlign.center,
              style: const TextStyle(color: KnColors.textMuted, fontSize: 13),
            ),
            const SizedBox(height: 4),
            const Text(
              'Cancel anytime',
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
        provider: _provider,
        phone: ref.read(authProvider).user?.phone,
      );

      if (result['ok'] == true && mounted) {
        final data = result['data'];
        final link = data['link'];
        final paymentRef = data['payment_ref'] ?? '';

        if (link != null) {
          context.push('/subscribe/pay', extra: {
            'url': link,
            'reference': paymentRef,
            'provider': _provider,
          });
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Payment link unavailable. Please try again.'),
            ),
          );
        }
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['error'] ?? 'Failed to initiate payment')),
        );
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
  final VoidCallback onTap;

  const _PlanCard({
    required this.plan,
    required this.color,
    required this.iconData,
    required this.selected,
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
                // Plan icon
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
                          if (plan.popular) ...[
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: color,
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Text(
                                'POPULAR',
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
                      const SizedBox(height: 2),
                      Text(
                        plan.duration,
                        style: const TextStyle(
                          fontSize: 12,
                          color: KnColors.textSecondary,
                        ),
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

class _ProviderChip extends StatelessWidget {
  final String label;
  final String subtitle;
  final IconData icon;
  final bool selected;
  final VoidCallback onTap;

  const _ProviderChip({
    required this.label,
    required this.subtitle,
    required this.icon,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final color = selected ? KnColors.orange : KnColors.textMuted;
    return Material(
      color: selected ? KnColors.orange.withAlpha(13) : Colors.white,
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: selected ? KnColors.orange : KnColors.border,
              width: selected ? 2 : 1.5,
            ),
          ),
          child: Column(
            children: [
              Icon(icon, color: color, size: 28),
              const SizedBox(height: 6),
              Text(
                label,
                style: TextStyle(
                  fontWeight: FontWeight.w700,
                  fontSize: 14,
                  color: selected ? KnColors.navy : KnColors.textSecondary,
                ),
              ),
              Text(
                subtitle,
                style: const TextStyle(fontSize: 11, color: KnColors.textMuted),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

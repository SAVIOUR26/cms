import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/auth_provider.dart';
import '../../providers/subscription_provider.dart';
import '../../theme/kn_theme.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider).user;
    final subscription = ref.watch(subscriptionStatusProvider);

    if (user == null) return const SizedBox.shrink();

    return Scaffold(
      appBar: AppBar(title: const Text('My Account')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // Avatar
            Container(
              width: 88,
              height: 88,
              decoration: BoxDecoration(
                gradient: KnColors.orangeGradient,
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: KnColors.orange.withAlpha(76),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Center(
                child: Text(
                  user.initials,
                  style: const TextStyle(
                    fontSize: 32,
                    fontWeight: FontWeight.w800,
                    color: Colors.white,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              user.displayName,
              style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w800,
                color: KnColors.navy,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              user.phone,
              style: const TextStyle(color: KnColors.textSecondary),
            ),
            if (user.roleLabel.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(
                user.roleLabel,
                style: const TextStyle(color: KnColors.textSecondary),
              ),
            ],
            const SizedBox(height: 32),

            // Info cards
            _InfoCard(
              icon: Icons.person_outline,
              label: 'Full Name',
              value: user.fullName ?? 'Not set',
            ),
            _InfoCard(
              icon: Icons.phone_outlined,
              label: 'Phone',
              value: user.phone,
            ),
            if (user.email != null)
              _InfoCard(
                icon: Icons.email_outlined,
                label: 'Email',
                value: user.email!,
              ),
            _InfoCard(
              icon: Icons.cake_outlined,
              label: 'Age',
              value: user.age?.toString() ?? 'Not set',
            ),
            if (user.roleDetail != null)
              _InfoCard(
                icon: Icons.business_outlined,
                label: user.role == 'student' ? 'University' : 'Company',
                value: user.roleDetail!,
              ),
            _InfoCard(
              icon: Icons.flag_outlined,
              label: 'Country',
              value: user.country.toUpperCase(),
            ),

            const SizedBox(height: 16),

            // Subscription status
            subscription.when(
              data: (sub) => Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: sub?.isActive == true
                      ? KnColors.success.withAlpha(25)
                      : KnColors.warning.withAlpha(25),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: sub?.isActive == true
                        ? KnColors.success.withAlpha(76)
                        : KnColors.warning.withAlpha(76),
                  ),
                ),
                child: Column(
                  children: [
                    Icon(
                      sub?.isActive == true ? Icons.check_circle : Icons.info_outline,
                      color: sub?.isActive == true ? KnColors.success : KnColors.warning,
                      size: 32,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      sub?.isActive == true
                          ? '${sub!.planLabel} Plan Active'
                          : 'No Active Subscription',
                      style: const TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 16,
                        color: KnColors.navy,
                      ),
                    ),
                    if (sub?.isActive == true)
                      Text(
                        'Expires: ${sub!.expiresAt}',
                        style: const TextStyle(color: KnColors.textSecondary, fontSize: 13),
                      ),
                    if (sub?.isActive != true) ...[
                      const SizedBox(height: 12),
                      ElevatedButton(
                        onPressed: () => context.push('/subscribe'),
                        child: const Text('Subscribe Now'),
                      ),
                    ],
                  ],
                ),
              ),
              loading: () => const SizedBox.shrink(),
              error: (_, __) => const SizedBox.shrink(),
            ),

            const SizedBox(height: 32),

            // Logout button
            SizedBox(
              width: double.infinity,
              height: 48,
              child: OutlinedButton.icon(
                onPressed: () async {
                  await ref.read(authProvider.notifier).logout();
                  if (context.mounted) context.go('/login');
                },
                icon: const Icon(Icons.logout, color: KnColors.error),
                label: const Text('Logout', style: TextStyle(color: KnColors.error)),
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: KnColors.error),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _InfoCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _InfoCard({required this.icon, required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          Icon(icon, color: KnColors.textMuted, size: 22),
          const SizedBox(width: 14),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: const TextStyle(fontSize: 12, color: KnColors.textMuted),
              ),
              Text(
                value,
                style: const TextStyle(
                  fontWeight: FontWeight.w600,
                  color: KnColors.navy,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

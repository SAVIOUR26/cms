import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import 'screens/splash_screen.dart';
import 'screens/auth/phone_screen.dart';
import 'screens/auth/otp_screen.dart';
import 'screens/auth/register_screen.dart';
import 'screens/home/home_screen.dart';
import 'screens/home/archives_screen.dart';
import 'screens/home/special_editions_screen.dart';
import 'screens/reader/reader_screen.dart';
import 'screens/subscription/plans_screen.dart';
import 'screens/subscription/payment_screen.dart';
import 'screens/profile/profile_screen.dart';
import 'theme/kn_theme.dart';

void main() {
  runApp(const ProviderScope(child: KandaNewsApp()));
}

final _router = GoRouter(
  initialLocation: '/',
  routes: [
    GoRoute(path: '/', builder: (_, __) => const SplashScreen()),
    GoRoute(path: '/login', builder: (_, __) => const PhoneScreen()),
    GoRoute(
      path: '/otp',
      builder: (_, state) {
        final extra = state.extra as Map<String, dynamic>? ?? {};
        return OtpScreen(
          phone: extra['phone'] ?? '',
          country: extra['country'] ?? 'ug',
        );
      },
    ),
    GoRoute(path: '/register', builder: (_, __) => const RegisterScreen()),
    GoRoute(path: '/dashboard', builder: (_, __) => const HomeScreen()),
    GoRoute(
      path: '/archives',
      builder: (_, state) {
        final extra = state.extra as Map<String, dynamic>?;
        return ArchivesScreen(filterType: extra?['type']);
      },
    ),
    GoRoute(
      path: '/reader',
      builder: (_, state) {
        final extra = state.extra as Map<String, dynamic>? ?? {};
        return ReaderScreen(
          url: extra['url'] ?? '',
          title: extra['title'] ?? 'Edition',
        );
      },
    ),
    GoRoute(path: '/special-editions', builder: (_, __) => const SpecialEditionsScreen()),
    GoRoute(path: '/subscribe', builder: (_, __) => const PlansScreen()),
    GoRoute(
      path: '/subscribe/pay',
      builder: (_, state) {
        final extra = state.extra as Map<String, dynamic>? ?? {};
        return PaymentScreen(
          url: extra['url'] ?? '',
          reference: extra['reference'] ?? '',
        );
      },
    ),
    GoRoute(path: '/profile', builder: (_, __) => const ProfileScreen()),
    // Onboarding redirects to login for now
    GoRoute(path: '/onboarding', builder: (_, __) => const PhoneScreen()),
  ],
);

class KandaNewsApp extends StatelessWidget {
  const KandaNewsApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'KandaNews Africa',
      debugShowCheckedModeBanner: false,
      theme: KnTheme.light,
      darkTheme: KnTheme.dark,
      themeMode: ThemeMode.light,
      routerConfig: _router,
    );
  }
}

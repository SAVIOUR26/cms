import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../config/constants.dart';
import '../../providers/auth_provider.dart';
import '../../theme/kn_theme.dart';

class PhoneScreen extends ConsumerStatefulWidget {
  const PhoneScreen({super.key});

  @override
  ConsumerState<PhoneScreen> createState() => _PhoneScreenState();
}

class _PhoneScreenState extends ConsumerState<PhoneScreen> {
  final _phoneController = TextEditingController();
  String _selectedCountry = 'ug';
  String _dialCode = '+256';

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final screenWidth = MediaQuery.of(context).size.width;
    final isDesktop = screenWidth > 800;

    final loginForm = SingleChildScrollView(
      padding: const EdgeInsets.all(32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const SizedBox(height: 40),

          // Logo image in rectangle with rounded corners and 3D shadow
          Center(
            child: Container(
              width: 160,
              height: 80,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: KnColors.orange.withAlpha(80),
                    blurRadius: 30,
                    offset: const Offset(0, 10),
                    spreadRadius: 2,
                  ),
                  BoxShadow(
                    color: Colors.black.withAlpha(40),
                    blurRadius: 15,
                    offset: const Offset(0, 6),
                  ),
                  BoxShadow(
                    color: Colors.white.withAlpha(200),
                    blurRadius: 1,
                    offset: const Offset(0, -1),
                  ),
                ],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(20),
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Image.asset(
                    'assets/images/logo.png',
                    fit: BoxFit.contain,
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(height: 36),

          // Title
          const Text(
            'Welcome to\nKandaNews Africa',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.w800,
              color: KnColors.navy,
              height: 1.2,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Enter your phone number to get started',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 16,
              color: KnColors.textSecondary,
            ),
          ),
          const SizedBox(height: 40),

          // Country selector
          const Text(
            'Country',
            style: TextStyle(
              fontWeight: FontWeight.w700,
              color: KnColors.navy,
              fontSize: 14,
            ),
          ),
          const SizedBox(height: 8),
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: KnColors.border, width: 2),
            ),
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                value: _selectedCountry,
                isExpanded: true,
                items: AppConstants.countries.map((c) {
                  return DropdownMenuItem(
                    value: c['code'],
                    child: Text(
                      '${c['flag']} ${c['name']} (${c['dial']})',
                      style: const TextStyle(fontSize: 16),
                    ),
                  );
                }).toList(),
                onChanged: (val) {
                  if (val != null) {
                    setState(() {
                      _selectedCountry = val;
                      _dialCode = AppConstants.countries
                          .firstWhere((c) => c['code'] == val)['dial']!;
                    });
                  }
                },
              ),
            ),
          ),
          const SizedBox(height: 20),

          // Phone input
          const Text(
            'Phone Number',
            style: TextStyle(
              fontWeight: FontWeight.w700,
              color: KnColors.navy,
              fontSize: 14,
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 18),
                decoration: BoxDecoration(
                  color: KnColors.navy,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  _dialCode,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w700,
                    fontSize: 16,
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: TextField(
                  controller: _phoneController,
                  keyboardType: TextInputType.phone,
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                  decoration: const InputDecoration(
                    hintText: '7XX XXX XXX',
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 32),

          // Continue button
          SizedBox(
            height: 56,
            child: ElevatedButton(
              onPressed: authState.loading ? null : _onContinue,
              child: authState.loading
                  ? const SizedBox(
                      width: 24,
                      height: 24,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : const Text('Continue'),
            ),
          ),

          if (authState.error != null) ...[
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: KnColors.error.withAlpha(25),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                authState.error!,
                style: const TextStyle(color: KnColors.error, fontSize: 14),
              ),
            ),
          ],

          const SizedBox(height: 24),
          Text(
            'We\'ll send you a verification code via SMS',
            textAlign: TextAlign.center,
            style: TextStyle(color: KnColors.textMuted, fontSize: 13),
          ),
        ],
      ),
    );

    // Desktop: centered card with decorative background
    if (isDesktop) {
      return Scaffold(
        body: Container(
          decoration: const BoxDecoration(gradient: KnColors.primaryGradient),
          child: Stack(
            children: [
              // Grid pattern background
              CustomPaint(
                size: MediaQuery.of(context).size,
                painter: _GridPatternPainter(
                  lineColor: KnColors.orange.withAlpha(15),
                ),
              ),
              Center(
                child: Container(
                  width: 460,
                  margin: const EdgeInsets.symmetric(vertical: 40),
                  decoration: BoxDecoration(
                    color: KnColors.background,
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withAlpha(80),
                        blurRadius: 60,
                        offset: const Offset(0, 20),
                      ),
                    ],
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(24),
                    child: loginForm,
                  ),
                ),
              ),
            ],
          ),
        ),
      );
    }

    // Mobile: standard layout
    return Scaffold(
      body: SafeArea(child: loginForm),
    );
  }

  void _onContinue() async {
    final phone = '$_dialCode${_phoneController.text.replaceAll(RegExp(r'\s'), '')}';
    if (_phoneController.text.length < 7) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter a valid phone number')),
      );
      return;
    }

    final success = await ref.read(authProvider.notifier).requestOtp(phone, _selectedCountry);
    if (success && mounted) {
      context.push('/otp', extra: {'phone': phone, 'country': _selectedCountry});
    }
  }
}

/// Paints a subtle cross-grid pattern for the desktop background
class _GridPatternPainter extends CustomPainter {
  final Color lineColor;

  _GridPatternPainter({required this.lineColor});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = lineColor
      ..strokeWidth = 1;

    const spacing = 40.0;
    for (double x = 0; x < size.width; x += spacing) {
      canvas.drawLine(Offset(x, 0), Offset(x, size.height), paint);
    }
    for (double y = 0; y < size.height; y += spacing) {
      canvas.drawLine(Offset(0, y), Offset(size.width, y), paint);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

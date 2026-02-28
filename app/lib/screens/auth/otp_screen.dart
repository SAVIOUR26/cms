import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:pin_code_fields/pin_code_fields.dart';
import '../../config/constants.dart';
import '../../providers/auth_provider.dart';
import '../../theme/kn_theme.dart';

class OtpScreen extends ConsumerStatefulWidget {
  final String phone;
  final String country;

  const OtpScreen({super.key, required this.phone, required this.country});

  @override
  ConsumerState<OtpScreen> createState() => _OtpScreenState();
}

class _OtpScreenState extends ConsumerState<OtpScreen> {
  final _otpController = TextEditingController();
  int _resendSeconds = AppConstants.otpResendSeconds;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _startTimer();
  }

  void _startTimer() {
    _resendSeconds = AppConstants.otpResendSeconds;
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 1), (t) {
      if (_resendSeconds > 0) {
        setState(() => _resendSeconds--);
      } else {
        t.cancel();
      }
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    _otpController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final screenWidth = MediaQuery.of(context).size.width;
    final isDesktop = screenWidth > 800;

    // Mask phone: +256****789
    final masked = widget.phone.length > 6
        ? '${widget.phone.substring(0, 4)}****${widget.phone.substring(widget.phone.length - 3)}'
        : widget.phone;

    final otpForm = SingleChildScrollView(
      padding: const EdgeInsets.all(32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const SizedBox(height: 20),
          const Text(
            'Verification Code',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.w800,
              color: KnColors.navy,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Enter the 6-digit code sent to\n$masked',
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 16,
              color: KnColors.textSecondary,
              height: 1.5,
            ),
          ),
          const SizedBox(height: 40),

          // OTP input — constrain width so it doesn't stretch on desktop
          Center(
            child: SizedBox(
              width: isDesktop ? 340 : double.infinity,
              child: PinCodeTextField(
                appContext: context,
                length: AppConstants.otpLength,
                controller: _otpController,
                keyboardType: TextInputType.number,
                animationType: AnimationType.fade,
                textStyle: const TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.w700,
                  color: KnColors.navy,
                ),
                pinTheme: PinTheme(
                  shape: PinCodeFieldShape.box,
                  borderRadius: BorderRadius.circular(12),
                  fieldHeight: 56,
                  fieldWidth: 48,
                  activeFillColor: Colors.white,
                  inactiveFillColor: Colors.white,
                  selectedFillColor: KnColors.orange.withAlpha(25),
                  activeColor: KnColors.orange,
                  inactiveColor: KnColors.border,
                  selectedColor: KnColors.orange,
                ),
                enableActiveFill: true,
                onCompleted: (_) => _onVerify(),
                onChanged: (_) {},
              ),
            ),
          ),
          const SizedBox(height: 24),

          // Verify button
          SizedBox(
            height: 56,
            child: ElevatedButton(
              onPressed: authState.loading ? null : _onVerify,
              child: authState.loading
                  ? const SizedBox(
                      width: 24,
                      height: 24,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : const Text('Verify'),
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
                style: const TextStyle(color: KnColors.error),
              ),
            ),
          ],

          const SizedBox(height: 24),

          // Resend
          Center(
            child: _resendSeconds > 0
                ? Text(
                    'Resend code in ${_resendSeconds}s',
                    style: const TextStyle(color: KnColors.textMuted),
                  )
                : TextButton(
                    onPressed: _onResend,
                    child: const Text(
                      'Resend Code',
                      style: TextStyle(
                        color: KnColors.orange,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
          ),
        ],
      ),
    );

    // Desktop: centered card on navy background
    if (isDesktop) {
      return Scaffold(
        body: Container(
          decoration: const BoxDecoration(gradient: KnColors.primaryGradient),
          child: Stack(
            children: [
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
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Align(
                        alignment: Alignment.topLeft,
                        child: Padding(
                          padding: const EdgeInsets.only(left: 8, top: 8),
                          child: IconButton(
                            icon: const Icon(Icons.arrow_back, color: KnColors.navy),
                            onPressed: () => context.pop(),
                          ),
                        ),
                      ),
                      otpForm,
                    ],
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
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: KnColors.navy),
          onPressed: () => context.pop(),
        ),
      ),
      body: SafeArea(child: otpForm),
    );
  }

  void _onVerify() async {
    if (_otpController.text.length != AppConstants.otpLength) return;

    final result = await ref.read(authProvider.notifier).verifyOtp(
          widget.phone,
          _otpController.text,
          widget.country,
        );

    if (result != null && mounted) {
      final isNew = result['data']['is_new'] == true;
      if (isNew) {
        context.go('/register');
      } else {
        context.go('/dashboard');
      }
    }
  }

  void _onResend() async {
    await ref.read(authProvider.notifier).requestOtp(widget.phone, widget.country);
    _startTimer();
  }
}

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

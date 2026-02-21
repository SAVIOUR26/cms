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

    return Scaffold(
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 60),

              // Logo
              Container(
                width: 100,
                height: 100,
                decoration: BoxDecoration(
                  gradient: KnColors.orangeGradient,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(
                      color: KnColors.orange.withAlpha(77),
                      blurRadius: 30,
                      offset: const Offset(0, 10),
                    ),
                  ],
                ),
                child: const Icon(Icons.newspaper, size: 48, color: Colors.white),
              ),
              const SizedBox(height: 32),

              // Title
              const Text(
                'Welcome to\nKandaNews Africa',
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
        ),
      ),
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

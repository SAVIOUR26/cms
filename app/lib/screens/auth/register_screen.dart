import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../config/constants.dart';
import '../../providers/auth_provider.dart';
import '../../theme/kn_theme.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _firstNameCtrl = TextEditingController();
  final _surnameCtrl = TextEditingController();
  final _ageCtrl = TextEditingController();
  final _roleDetailCtrl = TextEditingController();
  String? _selectedRole;

  String get _roleDetailLabel {
    final match = AppConstants.userRoles.where((r) => r['value'] == _selectedRole);
    return match.isNotEmpty ? match.first['detail_label']! : 'Details';
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final screenWidth = MediaQuery.of(context).size.width;
    final isDesktop = screenWidth > 800;

    final registerForm = SingleChildScrollView(
      padding: const EdgeInsets.all(28),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const SizedBox(height: 20),
            const Text(
              'Complete Your Profile',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 28,
                fontWeight: FontWeight.w800,
                color: KnColors.navy,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Tell us a bit about yourself',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 16, color: KnColors.textSecondary),
            ),
            const SizedBox(height: 32),

            // First Name
            TextFormField(
              controller: _firstNameCtrl,
              textCapitalization: TextCapitalization.words,
              decoration: const InputDecoration(
                labelText: 'First Name',
                prefixIcon: Icon(Icons.person_outline),
              ),
              validator: (v) =>
                  v == null || v.trim().length < 2 ? 'Enter your first name' : null,
            ),
            const SizedBox(height: 16),

            // Surname
            TextFormField(
              controller: _surnameCtrl,
              textCapitalization: TextCapitalization.words,
              decoration: const InputDecoration(
                labelText: 'Surname',
                prefixIcon: Icon(Icons.person_outline),
              ),
              validator: (v) =>
                  v == null || v.trim().length < 2 ? 'Enter your surname' : null,
            ),
            const SizedBox(height: 16),

            // Age
            TextFormField(
              controller: _ageCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Age',
                prefixIcon: Icon(Icons.cake_outlined),
              ),
              validator: (v) {
                final age = int.tryParse(v ?? '');
                if (age == null || age < 13 || age > 120) return 'Enter a valid age (13+)';
                return null;
              },
            ),
            const SizedBox(height: 24),

            // Role selection
            const Text(
              'I am a...',
              style: TextStyle(
                fontWeight: FontWeight.w700,
                fontSize: 16,
                color: KnColors.navy,
              ),
            ),
            const SizedBox(height: 12),
            ...AppConstants.userRoles.map((role) => Padding(
                  padding: const EdgeInsets.only(bottom: 10),
                  child: InkWell(
                    onTap: () => setState(() => _selectedRole = role['value']),
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: _selectedRole == role['value']
                            ? KnColors.orange.withAlpha(25)
                            : Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: _selectedRole == role['value']
                              ? KnColors.orange
                              : KnColors.border,
                          width: 2,
                        ),
                      ),
                      child: Row(
                        children: [
                          Text(role['icon']!, style: const TextStyle(fontSize: 28)),
                          const SizedBox(width: 16),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                role['label']!,
                                style: TextStyle(
                                  fontWeight: FontWeight.w700,
                                  fontSize: 16,
                                  color: _selectedRole == role['value']
                                      ? KnColors.orange
                                      : KnColors.navy,
                                ),
                              ),
                              Text(
                                role['detail_label']!,
                                style: const TextStyle(
                                  fontSize: 13,
                                  color: KnColors.textMuted,
                                ),
                              ),
                            ],
                          ),
                          const Spacer(),
                          if (_selectedRole == role['value'])
                            const Icon(Icons.check_circle, color: KnColors.orange),
                        ],
                      ),
                    ),
                  ),
                )),

            // Role detail (university/company/business)
            if (_selectedRole != null) ...[
              const SizedBox(height: 8),
              TextFormField(
                controller: _roleDetailCtrl,
                textCapitalization: TextCapitalization.words,
                decoration: InputDecoration(
                  labelText: _roleDetailLabel,
                  prefixIcon: const Icon(Icons.business_outlined),
                ),
                validator: (v) =>
                    v == null || v.trim().length < 2 ? 'Enter your $_roleDetailLabel' : null,
              ),
            ],

            const SizedBox(height: 32),

            // Submit
            SizedBox(
              height: 56,
              child: ElevatedButton(
                onPressed: authState.loading ? null : _onSubmit,
                child: authState.loading
                    ? const SizedBox(
                        width: 24,
                        height: 24,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Text('Get Started'),
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
          ],
        ),
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
                  width: 500,
                  margin: const EdgeInsets.symmetric(vertical: 24),
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
                    child: registerForm,
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
      body: SafeArea(child: registerForm),
    );
  }

  void _onSubmit() async {
    if (_selectedRole == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select your role')),
      );
      return;
    }
    if (!_formKey.currentState!.validate()) return;

    final success = await ref.read(authProvider.notifier).register(
          firstName: _firstNameCtrl.text.trim(),
          surname: _surnameCtrl.text.trim(),
          age: int.parse(_ageCtrl.text.trim()),
          role: _selectedRole!,
          roleDetail: _roleDetailCtrl.text.trim(),
        );

    if (success && mounted) {
      context.go('/dashboard');
    }
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

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:go_router/go_router.dart';
import '../../providers/subscription_provider.dart';
import '../../services/subscription_service.dart';
import '../../theme/kn_theme.dart';

/// Payment WebView screen for Flutterwave / DPO checkout
class PaymentScreen extends ConsumerStatefulWidget {
  final String url;
  final String reference;

  const PaymentScreen({super.key, required this.url, required this.reference});

  @override
  ConsumerState<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends ConsumerState<PaymentScreen> {
  bool _verifying = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Complete Payment'),
        backgroundColor: KnColors.navy,
        leading: IconButton(
          icon: const Icon(Icons.close),
          onPressed: () => _onClose(context),
        ),
      ),
      body: _verifying
          ? const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(color: KnColors.orange),
                  SizedBox(height: 16),
                  Text(
                    'Verifying payment...',
                    style: TextStyle(
                      fontWeight: FontWeight.w600,
                      color: KnColors.navy,
                    ),
                  ),
                ],
              ),
            )
          : InAppWebView(
              initialUrlRequest: URLRequest(url: WebUri(widget.url)),
              initialSettings: InAppWebViewSettings(
                javaScriptEnabled: true,
                useShouldOverrideUrlLoading: true,
              ),
              shouldOverrideUrlLoading: (controller, action) async {
                final url = action.request.url?.toString() ?? '';
                // Detect payment completion callbacks
                if (url.contains('status=successful') ||
                    url.contains('status=completed') ||
                    url.contains('tx_ref=')) {
                  _verifyPayment();
                  return NavigationActionPolicy.CANCEL;
                }
                return NavigationActionPolicy.ALLOW;
              },
            ),
    );
  }

  void _verifyPayment() async {
    setState(() => _verifying = true);
    try {
      final service = ref.read(subscriptionServiceProvider);
      final result = await service.verify(
        provider: 'flutterwave',
        reference: widget.reference,
      );

      if (result['ok'] == true && mounted) {
        ref.invalidate(subscriptionStatusProvider);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Subscription activated!'),
            backgroundColor: KnColors.success,
          ),
        );
        context.go('/dashboard');
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Payment verification failed')),
          );
          context.pop();
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
        context.pop();
      }
    }
  }

  void _onClose(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancel Payment?'),
        content: const Text('Are you sure you want to cancel this payment?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Continue Payment'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              context.pop();
            },
            child: const Text('Cancel', style: TextStyle(color: KnColors.error)),
          ),
        ],
      ),
    );
  }
}

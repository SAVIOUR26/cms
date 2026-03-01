import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:go_router/go_router.dart';
import '../../providers/subscription_provider.dart';
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
  bool _handled = false;

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
                  SizedBox(height: 8),
                  Text(
                    'Please wait while we confirm your payment',
                    style: TextStyle(
                      color: KnColors.textSecondary,
                      fontSize: 13,
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
                domStorageEnabled: true,
              ),
              shouldOverrideUrlLoading: (controller, action) async {
                final url = action.request.url?.toString() ?? '';
                return _checkPaymentCallback(url);
              },
              onLoadStop: (controller, url) {
                // Also check on page load in case redirect happens without interception
                if (url != null) {
                  _checkPaymentCallback(url.toString());
                }
              },
            ),
    );
  }

  /// Check if the current URL indicates payment completion.
  /// Works for both Flutterwave and DPO callback patterns.
  NavigationActionPolicy _checkPaymentCallback(String url) {
    if (_handled) return NavigationActionPolicy.CANCEL;

    final uri = Uri.tryParse(url);
    if (uri == null) return NavigationActionPolicy.ALLOW;

    final params = uri.queryParameters;

    // Flutterwave callback: ?status=successful&tx_ref=...&transaction_id=...
    if (params.containsKey('status') && params.containsKey('tx_ref')) {
      final status = params['status'] ?? '';
      final txId = params['transaction_id'] ?? '';

      if (status == 'successful' || status == 'completed') {
        _handlePaymentSuccess(txId);
        return NavigationActionPolicy.CANCEL;
      } else if (status == 'cancelled') {
        _handlePaymentCancelled();
        return NavigationActionPolicy.CANCEL;
      }
    }

    // DPO callback: ?ref=...&TransactionToken=...
    if (params.containsKey('TransactionToken') ||
        (url.contains('subscribe/callback') && params['provider'] == 'dpo')) {
      final transToken = params['TransactionToken'] ?? '';
      final cancelled = params['cancelled'] == '1';

      if (cancelled) {
        _handlePaymentCancelled();
        return NavigationActionPolicy.CANCEL;
      }
      if (transToken.isNotEmpty) {
        _handlePaymentSuccess(transToken);
        return NavigationActionPolicy.CANCEL;
      }
    }

    // Generic: any callback URL with our ref
    if (url.contains('subscribe/callback') && params.containsKey('ref')) {
      final txId = params['transaction_id'] ?? params['TransactionToken'] ?? '';
      _handlePaymentSuccess(txId);
      return NavigationActionPolicy.CANCEL;
    }

    return NavigationActionPolicy.ALLOW;
  }

  void _handlePaymentSuccess(String transactionId) {
    if (_handled) return;
    _handled = true;
    _verifyPayment(transactionId);
  }

  void _handlePaymentCancelled() {
    if (_handled) return;
    _handled = true;
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Payment was cancelled')),
      );
      context.pop();
    }
  }

  void _verifyPayment(String transactionId) async {
    setState(() => _verifying = true);
    try {
      final service = ref.read(subscriptionServiceProvider);
      final result = await service.verify(
        paymentRef: widget.reference,
        transactionId: transactionId.isNotEmpty ? transactionId : null,
      );

      if (result['ok'] == true && mounted) {
        final status = result['data']?['status'] ?? '';
        if (status == 'active' || status == 'already_active') {
          ref.invalidate(subscriptionStatusProvider);
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Subscription activated!'),
              backgroundColor: KnColors.success,
            ),
          );
          context.go('/dashboard');
        } else {
          // Payment pending — may be confirmed by webhook later
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Payment is being processed. You will be notified once confirmed.'),
            ),
          );
          context.go('/dashboard');
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Payment verification failed. Contact support if charged.')),
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

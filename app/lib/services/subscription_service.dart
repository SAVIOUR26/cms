import '../config/api.dart';
import '../models/subscription.dart';
import 'api_service.dart';

/// Service for managing subscriptions and payments
class SubscriptionService {
  final _api = ApiService();

  /// Get available plans for a country
  Future<List<SubscriptionPlan>> getPlans(String country) async {
    try {
      final response = await _api.get(ApiConfig.subscribePlans, query: {
        'country': country,
      });
      if (response['ok'] == true) {
        final data = response['data'];
        final currency = data['currency'] ?? '';
        final plans = data['plans'] as List? ?? [];
        return plans
            .map((p) => SubscriptionPlan.fromJson(p, currency))
            .toList();
      }
    } catch (_) {}
    return [];
  }

  /// Get current subscription status
  Future<Subscription?> getStatus() async {
    try {
      final response = await _api.get(ApiConfig.subscribeStatus);
      if (response['ok'] == true && response['data']['subscription'] != null) {
        return Subscription.fromJson(response['data']['subscription']);
      }
    } catch (_) {}
    return null;
  }

  /// Initiate a payment — returns payment link and reference
  Future<Map<String, dynamic>> initiate({
    required String plan,
    required String provider,
    String? phone,
  }) async {
    return await _api.post(ApiConfig.subscribeInitiate, data: {
      'plan': plan,
      'provider': provider,
      if (phone != null) 'phone': phone,
    });
  }

  /// Verify a completed payment
  Future<Map<String, dynamic>> verify({
    required String paymentRef,
    String? transactionId,
  }) async {
    return await _api.post(ApiConfig.subscribeVerify, data: {
      'payment_ref': paymentRef,
      if (transactionId != null) 'transaction_id': transactionId,
    });
  }
}

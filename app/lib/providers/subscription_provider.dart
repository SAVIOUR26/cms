import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/subscription.dart';
import '../services/subscription_service.dart';

final subscriptionServiceProvider = Provider((_) => SubscriptionService());

/// Current subscription status
final subscriptionStatusProvider = FutureProvider<Subscription?>((ref) async {
  final service = ref.read(subscriptionServiceProvider);
  return await service.getStatus();
});

/// Available plans for a country
final plansProvider = FutureProvider.family<List<SubscriptionPlan>, String>((ref, country) async {
  final service = ref.read(subscriptionServiceProvider);
  return await service.getPlans(country);
});

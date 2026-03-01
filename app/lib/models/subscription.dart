/// Subscription model
class Subscription {
  final String plan;
  final String status;
  final String startsAt;
  final String expiresAt;

  const Subscription({
    required this.plan,
    required this.status,
    required this.startsAt,
    required this.expiresAt,
  });

  bool get isActive => status == 'active';

  String get planLabel {
    switch (plan) {
      case 'daily':
        return 'Daily';
      case 'weekly':
        return 'Weekly';
      case 'monthly':
        return 'Monthly';
      default:
        return plan;
    }
  }

  factory Subscription.fromJson(Map<String, dynamic> json) {
    return Subscription(
      plan: json['plan'] ?? '',
      status: json['status'] ?? '',
      startsAt: json['starts_at'] ?? '',
      expiresAt: json['expires_at'] ?? '',
    );
  }
}

/// Subscription plan with pricing
class SubscriptionPlan {
  final String plan;
  final String planLabel;
  final String currency;
  final double amount;
  final int days;
  final String duration;
  final bool popular;

  const SubscriptionPlan({
    required this.plan,
    required this.planLabel,
    required this.currency,
    required this.amount,
    this.days = 1,
    this.duration = '',
    this.popular = false,
  });

  String get label => planLabel;

  String get formattedPrice => '$currency ${amount.toStringAsFixed(0)}';

  /// Parse from the /subscribe/plans API response.
  /// [planJson] is one element of the plans array, [currency] comes from the top-level field.
  factory SubscriptionPlan.fromJson(Map<String, dynamic> planJson, String currency) {
    return SubscriptionPlan(
      plan: planJson['id'] ?? '',
      planLabel: planJson['label'] ?? '',
      currency: currency,
      amount: (planJson['price'] is num) ? planJson['price'].toDouble() : 0.0,
      days: planJson['days'] ?? 1,
      duration: planJson['duration'] ?? '',
      popular: planJson['popular'] == true,
    );
  }
}

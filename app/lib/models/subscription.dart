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
  final String currency;
  final double amount;

  const SubscriptionPlan({
    required this.plan,
    required this.currency,
    required this.amount,
  });

  String get label {
    switch (plan) {
      case 'daily':
        return 'Daily Pass';
      case 'weekly':
        return 'Weekly';
      case 'monthly':
        return 'Monthly';
      default:
        return plan;
    }
  }

  String get icon {
    switch (plan) {
      case 'daily':
        return '📅';
      case 'weekly':
        return '📆';
      case 'monthly':
        return '🗓️';
      default:
        return '💳';
    }
  }

  String get formattedPrice => '$currency ${amount.toStringAsFixed(0)}';

  factory SubscriptionPlan.fromJson(String plan, Map<String, dynamic> json) {
    return SubscriptionPlan(
      plan: plan,
      currency: json['currency'] ?? '',
      amount: (json[plan] is num) ? json[plan].toDouble() : 0.0,
    );
  }
}

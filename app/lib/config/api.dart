/// KandaNews API configuration
class ApiConfig {
  static const String baseUrl = 'https://api.kandanews.africa';
  static const String editionsUrl = 'https://cms.kandanews.africa/output';

  static const Duration connectTimeout = Duration(seconds: 15);
  static const Duration receiveTimeout = Duration(seconds: 30);

  // Auth
  static const String requestOtp = '/auth/request-otp';
  static const String verifyOtp = '/auth/verify-otp';
  static const String register = '/auth/register';
  static const String refreshToken = '/auth/refresh';

  // User
  static const String userProfile = '/user/profile';

  // Editions
  static const String editions = '/editions';
  static const String editionsToday = '/editions/today';
  static const String editionsLatest = '/editions/latest';

  // Subscriptions
  static const String subscribePlans = '/subscribe/plans';
  static const String subscribeStatus = '/subscribe/status';
  static const String subscribeInitiate = '/subscribe/initiate';
  static const String subscribeVerify = '/subscribe/verify';

  // Misc
  static const String quoteOfDay = '/misc/quote';
}

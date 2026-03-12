/// App-wide constants
class AppConstants {
  static const String appName = 'KandaNews Africa';
  static const String appTagline = 'The Future of News';
  static const String appVersion = '1.0.0';

  // Storage keys
  static const String keyAccessToken = 'access_token';
  static const String keyRefreshToken = 'refresh_token';
  static const String keyUserId = 'user_id';
  static const String keyUserPhone = 'user_phone';
  static const String keyUserCountry = 'user_country';
  static const String keyIsNewUser = 'is_new_user';
  static const String keyOnboardingDone = 'onboarding_done';
  static const String keyUserCache = 'user_cache';

  // Supported countries
  static const List<Map<String, String>> countries = [
    {'code': 'ug', 'name': 'Uganda', 'dial': '+256', 'flag': '🇺🇬'},
    {'code': 'ke', 'name': 'Kenya', 'dial': '+254', 'flag': '🇰🇪'},
    {'code': 'ng', 'name': 'Nigeria', 'dial': '+234', 'flag': '🇳🇬'},
    {'code': 'za', 'name': 'South Africa', 'dial': '+27', 'flag': '🇿🇦'},
  ];

  // User roles
  static const List<Map<String, String>> userRoles = [
    {'value': 'student', 'label': 'Student', 'icon': '🎓', 'detail_label': 'University Name'},
    {'value': 'professional', 'label': 'Professional', 'icon': '💼', 'detail_label': 'Company Name'},
    {'value': 'entrepreneur', 'label': 'Entrepreneur', 'icon': '🚀', 'detail_label': 'Business Name'},
  ];

  // OTP
  static const int otpLength = 6;
  static const int otpResendSeconds = 60;
}

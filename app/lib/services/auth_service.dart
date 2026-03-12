import '../config/api.dart';
import '../models/user.dart';
import 'api_service.dart';
import 'storage_service.dart';

/// Authentication service — OTP flow + registration
class AuthService {
  final _api = ApiService();

  /// Request OTP code to be sent via SMS
  Future<Map<String, dynamic>> requestOtp(String phone, String country) async {
    final response = await _api.post(ApiConfig.requestOtp, data: {
      'phone': phone,
      'country': country,
    });
    return response;
  }

  /// Verify OTP code and get JWT tokens
  /// Returns { user, tokens, is_new }
  Future<Map<String, dynamic>> verifyOtp(String phone, String code, String country) async {
    final response = await _api.post(ApiConfig.verifyOtp, data: {
      'phone': phone,
      'code': code,
      'country': country,
    });

    if (response['ok'] == true) {
      final data = response['data'];
      final tokens = data['tokens'];

      // Save tokens
      await StorageService.saveTokens(
        tokens['access_token'],
        tokens['refresh_token'],
      );

      // Save user info
      final userData = data['user'];
      await StorageService.setUserId(userData['id']);
      await StorageService.setUserCountry(userData['country'] ?? country);
      await StorageService.setIsNewUser(data['is_new'] == true);

      // Cache user profile so checkAuth() works without a network call
      await StorageService.saveUser(User.fromJson(userData));

      // Mark onboarding done so the app never sends the user back to login on reopen
      await StorageService.setOnboardingDone();
    }

    return response;
  }

  /// Complete first-time registration
  Future<Map<String, dynamic>> register({
    required String firstName,
    required String surname,
    required int age,
    required String role,
    required String roleDetail,
  }) async {
    final response = await _api.post(ApiConfig.register, data: {
      'first_name': firstName,
      'surname': surname,
      'age': age,
      'role': role,
      'role_detail': roleDetail,
    });

    if (response['ok'] == true) {
      await StorageService.setIsNewUser(false);
      // Refresh cached user with completed profile
      await StorageService.saveUser(User.fromJson(response['data']['user']));
    }

    return response;
  }

  /// Get current user profile
  Future<User?> getProfile() async {
    try {
      final response = await _api.get(ApiConfig.userProfile);
      if (response['ok'] == true) {
        return User.fromJson(response['data']['user']);
      }
    } catch (_) {}
    return null;
  }

  /// Update user profile
  Future<Map<String, dynamic>> updateProfile(Map<String, dynamic> fields) async {
    return await _api.put(ApiConfig.userProfile, data: fields);
  }

  /// Check if user is logged in (has valid tokens)
  Future<bool> isLoggedIn() async {
    final token = await StorageService.getAccessToken();
    return token != null;
  }

  /// Logout — clear all stored data
  Future<void> logout() async {
    await StorageService.clearAll();
  }
}

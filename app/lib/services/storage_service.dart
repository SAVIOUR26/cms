import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../config/constants.dart';

/// Secure token + preferences storage
class StorageService {
  static final _secure = const FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );

  // ── Tokens (secure) ──

  static Future<void> saveTokens(String accessToken, String refreshToken) async {
    await _secure.write(key: AppConstants.keyAccessToken, value: accessToken);
    await _secure.write(key: AppConstants.keyRefreshToken, value: refreshToken);
  }

  static Future<String?> getAccessToken() async {
    return await _secure.read(key: AppConstants.keyAccessToken);
  }

  static Future<String?> getRefreshToken() async {
    return await _secure.read(key: AppConstants.keyRefreshToken);
  }

  static Future<void> clearTokens() async {
    await _secure.delete(key: AppConstants.keyAccessToken);
    await _secure.delete(key: AppConstants.keyRefreshToken);
  }

  // ── Preferences ──

  static Future<void> setUserId(int id) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(AppConstants.keyUserId, id);
  }

  static Future<int?> getUserId() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getInt(AppConstants.keyUserId);
  }

  static Future<void> setUserCountry(String country) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(AppConstants.keyUserCountry, country);
  }

  static Future<String> getUserCountry() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(AppConstants.keyUserCountry) ?? 'ug';
  }

  static Future<void> setIsNewUser(bool isNew) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(AppConstants.keyIsNewUser, isNew);
  }

  static Future<bool> getIsNewUser() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool(AppConstants.keyIsNewUser) ?? false;
  }

  static Future<void> setOnboardingDone() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(AppConstants.keyOnboardingDone, true);
  }

  static Future<bool> isOnboardingDone() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool(AppConstants.keyOnboardingDone) ?? false;
  }

  // ── Clear all ──

  static Future<void> clearAll() async {
    await clearTokens();
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
  }
}

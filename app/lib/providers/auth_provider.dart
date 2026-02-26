import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../services/storage_service.dart';

/// Auth state
enum AuthStatus { initial, authenticated, unauthenticated, newUser }

class AuthState {
  final AuthStatus status;
  final User? user;
  final String? error;
  final bool loading;

  const AuthState({
    this.status = AuthStatus.initial,
    this.user,
    this.error,
    this.loading = false,
  });

  AuthState copyWith({AuthStatus? status, User? user, String? error, bool? loading}) {
    return AuthState(
      status: status ?? this.status,
      user: user ?? this.user,
      error: error,
      loading: loading ?? this.loading,
    );
  }
}

class AuthNotifier extends StateNotifier<AuthState> {
  final AuthService _authService;

  AuthNotifier(this._authService) : super(const AuthState());

  /// Check if user is already logged in
  Future<void> checkAuth() async {
    final loggedIn = await _authService.isLoggedIn();
    if (loggedIn) {
      final user = await _authService.getProfile();
      if (user != null) {
        final isNew = await StorageService.getIsNewUser();
        state = AuthState(
          status: isNew ? AuthStatus.newUser : AuthStatus.authenticated,
          user: user,
        );
        return;
      }
    }
    state = const AuthState(status: AuthStatus.unauthenticated);
  }

  /// Request OTP
  Future<bool> requestOtp(String phone, String country) async {
    state = state.copyWith(loading: true, error: null);
    try {
      final result = await _authService.requestOtp(phone, country);
      state = state.copyWith(loading: false);
      return result['ok'] == true;
    } catch (e) {
      state = state.copyWith(loading: false, error: ApiService.friendlyError(e));
      return false;
    }
  }

  /// Verify OTP
  Future<Map<String, dynamic>?> verifyOtp(String phone, String code, String country) async {
    state = state.copyWith(loading: true, error: null);
    try {
      final result = await _authService.verifyOtp(phone, code, country);
      if (result['ok'] == true) {
        final userData = result['data']['user'];
        final user = User.fromJson(userData);
        final isNew = result['data']['is_new'] == true;

        state = AuthState(
          status: isNew ? AuthStatus.newUser : AuthStatus.authenticated,
          user: user,
        );
        return result;
      }
      state = state.copyWith(loading: false, error: result['error']);
      return null;
    } catch (e) {
      state = state.copyWith(loading: false, error: ApiService.friendlyError(e));
      return null;
    }
  }

  /// Complete registration
  Future<bool> register({
    required String firstName,
    required String surname,
    required int age,
    required String role,
    required String roleDetail,
  }) async {
    state = state.copyWith(loading: true, error: null);
    try {
      final result = await _authService.register(
        firstName: firstName,
        surname: surname,
        age: age,
        role: role,
        roleDetail: roleDetail,
      );
      if (result['ok'] == true) {
        final user = User.fromJson(result['data']['user']);
        state = AuthState(status: AuthStatus.authenticated, user: user);
        return true;
      }
      state = state.copyWith(loading: false, error: result['error']);
      return false;
    } catch (e) {
      state = state.copyWith(loading: false, error: ApiService.friendlyError(e));
      return false;
    }
  }

  /// Update user in state
  void updateUser(User user) {
    state = state.copyWith(user: user);
  }

  /// Logout
  Future<void> logout() async {
    await _authService.logout();
    state = const AuthState(status: AuthStatus.unauthenticated);
  }
}

// Providers
final authServiceProvider = Provider((_) => AuthService());

final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier(ref.read(authServiceProvider));
});

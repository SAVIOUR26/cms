import 'package:dio/dio.dart';
import '../config/api.dart';
import 'storage_service.dart';

/// HTTP client with JWT token injection, auto-refresh, and friendly error handling
class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;

  late final Dio _dio;

  ApiService._internal() {
    _dio = Dio(BaseOptions(
      baseUrl: ApiConfig.baseUrl,
      connectTimeout: ApiConfig.connectTimeout,
      receiveTimeout: ApiConfig.receiveTimeout,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ));

    // Request interceptor — inject JWT
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await StorageService.getAccessToken();
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        handler.next(options);
      },
      onError: (error, handler) async {
        // Auto-refresh on 401
        if (error.response?.statusCode == 401) {
          final refreshed = await _refreshToken();
          if (refreshed) {
            // Retry the original request
            final token = await StorageService.getAccessToken();
            error.requestOptions.headers['Authorization'] = 'Bearer $token';
            final response = await _dio.fetch(error.requestOptions);
            return handler.resolve(response);
          }
        }
        handler.next(error);
      },
    ));
  }

  Future<bool> _refreshToken() async {
    try {
      final refreshToken = await StorageService.getRefreshToken();
      if (refreshToken == null) return false;

      final response = await Dio(BaseOptions(
        baseUrl: ApiConfig.baseUrl,
        headers: {'Content-Type': 'application/json'},
      )).post(ApiConfig.refreshToken, data: {
        'refresh_token': refreshToken,
      });

      if (response.data['ok'] == true) {
        final tokens = response.data['data']['tokens'];
        await StorageService.saveTokens(
          tokens['access_token'],
          tokens['refresh_token'],
        );
        return true;
      }
    } catch (_) {}
    return false;
  }

  /// Converts Dio errors into user-friendly messages
  static String friendlyError(dynamic error) {
    if (error is DioException) {
      switch (error.type) {
        case DioExceptionType.connectionError:
        case DioExceptionType.unknown:
          // DNS failure, socket error, no route to host
          return 'No internet connection. Please check your network and try again.';
        case DioExceptionType.connectionTimeout:
          return 'Connection timed out. Please check your internet and try again.';
        case DioExceptionType.sendTimeout:
        case DioExceptionType.receiveTimeout:
          return 'The server is taking too long to respond. Please try again.';
        case DioExceptionType.badResponse:
          final statusCode = error.response?.statusCode;
          final serverMsg = error.response?.data is Map
              ? error.response?.data['error']
              : null;
          if (serverMsg != null) return serverMsg;
          if (statusCode == 429) return 'Too many attempts. Please wait a moment and try again.';
          if (statusCode == 500) return 'Server error. Please try again later.';
          if (statusCode == 503) return 'Service temporarily unavailable. Please try again later.';
          return 'Something went wrong (error $statusCode). Please try again.';
        case DioExceptionType.cancel:
          return 'Request was cancelled. Please try again.';
        case DioExceptionType.badCertificate:
          return 'Secure connection failed. Please check your network.';
      }
    }
    // Catch generic connectivity strings from the OS
    final msg = error.toString().toLowerCase();
    if (msg.contains('socketexception') ||
        msg.contains('failed host lookup') ||
        msg.contains('network is unreachable') ||
        msg.contains('connection refused')) {
      return 'No internet connection. Please check your network and try again.';
    }
    return 'Something went wrong. Please try again.';
  }

  // ── Generic methods ──

  Future<Map<String, dynamic>> get(String path, {Map<String, dynamic>? query}) async {
    final response = await _dio.get(path, queryParameters: query);
    return response.data;
  }

  Future<Map<String, dynamic>> post(String path, {Map<String, dynamic>? data}) async {
    final response = await _dio.post(path, data: data);
    return response.data;
  }

  Future<Map<String, dynamic>> put(String path, {Map<String, dynamic>? data}) async {
    final response = await _dio.put(path, data: data);
    return response.data;
  }
}

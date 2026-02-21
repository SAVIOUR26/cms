import 'package:dio/dio.dart';
import '../config/api.dart';
import 'storage_service.dart';

/// HTTP client with JWT token injection and auto-refresh
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

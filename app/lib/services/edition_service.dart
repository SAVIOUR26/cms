import '../config/api.dart';
import '../models/edition.dart';
import 'api_service.dart';

/// Service for fetching newspaper editions
class EditionService {
  final _api = ApiService();

  /// Get paginated list of editions
  Future<Map<String, dynamic>> getEditions({
    String country = 'ug',
    int page = 1,
    int perPage = 20,
  }) async {
    final response = await _api.get(ApiConfig.editions, query: {
      'country': country,
      'page': page,
      'per_page': perPage,
    });

    if (response['ok'] == true) {
      final data = response['data'];
      final editions = (data['editions'] as List)
          .map((e) => Edition.fromJson(e))
          .toList();
      return {
        'editions': editions,
        'pagination': data['pagination'],
      };
    }

    return {'editions': <Edition>[], 'pagination': {}};
  }

  /// Get today's edition
  Future<Edition?> getTodayEdition({String country = 'ug'}) async {
    try {
      final response = await _api.get(ApiConfig.editionsToday, query: {
        'country': country,
      });
      if (response['ok'] == true) {
        return Edition.fromJson(response['data']['edition']);
      }
    } catch (_) {}
    return null;
  }

  /// Get latest published edition
  Future<Edition?> getLatestEdition({String country = 'ug'}) async {
    try {
      final response = await _api.get(ApiConfig.editionsLatest, query: {
        'country': country,
      });
      if (response['ok'] == true) {
        return Edition.fromJson(response['data']['edition']);
      }
    } catch (_) {}
    return null;
  }

  /// Get full edition detail with pages
  Future<Map<String, dynamic>?> getEditionDetail(int id) async {
    try {
      final response = await _api.get('${ApiConfig.editions}/$id');
      if (response['ok'] == true) {
        return response['data'];
      }
    } catch (_) {}
    return null;
  }

  /// Get quote of the day
  Future<Map<String, dynamic>?> getQuoteOfDay() async {
    try {
      final response = await _api.get(ApiConfig.quoteOfDay);
      if (response['ok'] == true) {
        return response['data'];
      }
    } catch (_) {}
    return null;
  }
}

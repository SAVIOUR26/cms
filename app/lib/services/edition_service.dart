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
    String? type,
    String? category,
  }) async {
    final query = <String, dynamic>{
      'country': country,
      'page': page,
      'per_page': perPage,
    };
    if (type != null) query['type'] = type;
    if (category != null) query['category'] = category;
    final response = await _api.get(ApiConfig.editions, query: query);

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

  /// Get all dates in a month that have a published daily edition.
  /// Returns a Set of DateTime (date-only, time = midnight UTC).
  Future<Set<DateTime>> getAvailableDates({
    required String country,
    required String month, // 'YYYY-MM'
  }) async {
    try {
      final response = await _api.get(
        ApiConfig.editionsAvailableDates,
        query: {'country': country, 'month': month},
      );
      if (response['ok'] == true) {
        final dates = response['data']['dates'] as List? ?? [];
        return dates
            .map((d) => DateTime.parse(d as String))
            .toSet();
      }
    } catch (_) {}
    return {};
  }

  /// Get the daily edition for an exact date (YYYY-MM-DD).
  /// Returns null if no edition exists on that date.
  Future<Edition?> getEditionByDate({
    required String country,
    required String date, // 'YYYY-MM-DD'
  }) async {
    try {
      final response = await _api.get(
        '${ApiConfig.editions}/$date',
        query: {'country': country},
      );
      if (response['ok'] == true && response['data']['found'] == true) {
        return Edition.fromJson(response['data']['edition']);
      }
    } catch (_) {}
    return null;
  }
}

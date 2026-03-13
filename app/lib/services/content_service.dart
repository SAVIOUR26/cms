import '../config/api.dart';
import '../models/edition_category.dart';
import '../models/home_banner.dart';
import '../models/kanda_event.dart';
import '../models/poll.dart';
import 'api_service.dart';

/// Service for all SDUI content: categories, polls, events, banners.
class ContentService {
  final _api = ApiService();

  // ── Edition Categories ────────────────────────────────────────────────────

  Future<List<EditionCategory>> getCategories({String country = 'ug'}) async {
    try {
      final response = await _api.get(
        ApiConfig.editionCategories,
        query: {'country': country},
      );
      if (response['ok'] == true) {
        return (response['data']['categories'] as List)
            .map((c) => EditionCategory.fromJson(c as Map<String, dynamic>))
            .toList();
      }
    } catch (_) {}
    return [];
  }

  // ── Polls ─────────────────────────────────────────────────────────────────

  Future<List<Poll>> getPolls({
    String country = 'ug',
    String status = 'active',
  }) async {
    try {
      final response = await _api.get(
        ApiConfig.polls,
        query: {'country': country, 'status': status},
      );
      if (response['ok'] == true) {
        return (response['data']['polls'] as List)
            .map((p) => Poll.fromJson(p as Map<String, dynamic>))
            .toList();
      }
    } catch (_) {}
    return [];
  }

  /// Cast a vote. Returns the updated Poll on success, or null on failure.
  Future<Poll?> castVote({
    required int pollId,
    required int optionId,
  }) async {
    try {
      final response = await _api.post(
        '${ApiConfig.polls}/$pollId/vote',
        data: {'option_id': optionId},
      );
      if (response['ok'] == true) {
        return null; // caller will use withVoteResult()
      }
      // Extract API error message
      throw Exception(response['error'] ?? 'Vote failed');
    } catch (e) {
      rethrow;
    }
  }

  /// Cast a vote and return the raw result map (options + totals).
  Future<Map<String, dynamic>> castVoteRaw({
    required int pollId,
    required int optionId,
  }) async {
    final response = await _api.post(
      '${ApiConfig.polls}/$pollId/vote',
      data: {'option_id': optionId},
    );
    if (response['ok'] == true) {
      return response['data'] as Map<String, dynamic>;
    }
    throw Exception(response['error'] ?? 'Vote failed');
  }

  // ── Events ────────────────────────────────────────────────────────────────

  Future<List<KandaEvent>> getEvents({
    String country = 'ug',
    String status = 'published',
    int limit = 20,
  }) async {
    try {
      final response = await _api.get(
        ApiConfig.events,
        query: {'country': country, 'status': status, 'limit': limit},
      );
      if (response['ok'] == true) {
        return (response['data']['events'] as List)
            .map((e) => KandaEvent.fromJson(e as Map<String, dynamic>))
            .toList();
      }
    } catch (_) {}
    return [];
  }

  Future<KandaEvent?> getEventDetail(int id) async {
    try {
      final response = await _api.get('${ApiConfig.events}/$id');
      if (response['ok'] == true) {
        return KandaEvent.fromJson(
            response['data']['event'] as Map<String, dynamic>);
      }
    } catch (_) {}
    return null;
  }

  // ── Home Banners ──────────────────────────────────────────────────────────

  Future<List<HomeBanner>> getBanners({String country = 'ug'}) async {
    try {
      final response = await _api.get(
        ApiConfig.homeBanners,
        query: {'country': country},
      );
      if (response['ok'] == true) {
        return (response['data']['banners'] as List)
            .map((b) => HomeBanner.fromJson(b as Map<String, dynamic>))
            .toList();
      }
    } catch (_) {}
    return [];
  }

  /// Fire-and-forget — increments impression_count on the server.
  Future<void> trackBannerImpression(int id) async {
    try {
      await _api.post(ApiConfig.bannerImpression, data: {'id': id});
    } catch (_) {}
  }

  /// Fire-and-forget — increments click_count on the server.
  Future<void> trackBannerClick(int id) async {
    try {
      await _api.post(ApiConfig.bannerClick, data: {'id': id});
    } catch (_) {}
  }
}

import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/edition_category.dart';
import '../models/home_banner.dart';
import '../models/kanda_event.dart';
import '../models/poll.dart';
import '../services/content_service.dart';

final contentServiceProvider = Provider((_) => ContentService());

// ── Edition Categories ────────────────────────────────────────────────────────

final editionCategoriesProvider =
    FutureProvider.family<List<EditionCategory>, String>((ref, country) async {
  return ref.read(contentServiceProvider).getCategories(country: country);
});

// ── Polls ─────────────────────────────────────────────────────────────────────

/// Polls list — backed by a StateNotifier so votes can be applied optimistically
/// without a full network re-fetch.
class PollsNotifier extends StateNotifier<AsyncValue<List<Poll>>> {
  final ContentService _service;
  final String country;

  PollsNotifier(this._service, this.country)
      : super(const AsyncValue.loading()) {
    _load();
  }

  Future<void> _load() async {
    state = const AsyncValue.loading();
    try {
      final polls = await _service.getPolls(country: country);
      state = AsyncValue.data(polls);
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }

  Future<void> refresh() => _load();

  /// Submit a vote and optimistically update the in-memory state.
  /// Throws a user-friendly [Exception] if the vote fails.
  Future<void> vote({required int pollId, required int optionId}) async {
    final result = await _service.castVoteRaw(
      pollId: pollId,
      optionId: optionId,
    );
    // Patch the matching poll in current state
    if (state is AsyncData<List<Poll>>) {
      final current = (state as AsyncData<List<Poll>>).value;
      state = AsyncValue.data([
        for (final poll in current)
          if (poll.id == pollId) poll.withVoteResult(result) else poll,
      ]);
    }
  }
}

final pollsProvider = StateNotifierProvider.family<PollsNotifier,
    AsyncValue<List<Poll>>, String>(
  (ref, country) => PollsNotifier(ref.read(contentServiceProvider), country),
);

// ── Events ────────────────────────────────────────────────────────────────────

final eventsProvider =
    FutureProvider.family<List<KandaEvent>, String>((ref, country) async {
  return ref.read(contentServiceProvider).getEvents(country: country);
});

// ── Home Banners ──────────────────────────────────────────────────────────────

final homeBannersProvider =
    FutureProvider.family<List<HomeBanner>, String>((ref, country) async {
  return ref.read(contentServiceProvider).getBanners(country: country);
});

import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/edition.dart';
import '../services/edition_service.dart';

final editionServiceProvider = Provider((_) => EditionService());

/// Today's edition
final todayEditionProvider = FutureProvider.family<Edition?, String>((ref, country) async {
  final service = ref.read(editionServiceProvider);
  return await service.getTodayEdition(country: country);
});

/// Editions list (paginated, with optional type/category filters)
final editionsProvider =
    FutureProvider.family<Map<String, dynamic>, Map<String, dynamic>>((ref, params) async {
  final service = ref.read(editionServiceProvider);
  return await service.getEditions(
    country: params['country'] ?? 'ug',
    page: params['page'] ?? 1,
    perPage: params['per_page'] ?? 20,
    type: params['type'],
    category: params['category'],
  );
});

/// Quote of the day
final quoteProvider = FutureProvider<Map<String, dynamic>?>((ref) async {
  final service = ref.read(editionServiceProvider);
  return await service.getQuoteOfDay();
});

/// Available edition dates for a given month (for the calendar picker)
/// Key: 'country:YYYY-MM'
final availableDatesProvider =
    FutureProvider.family<Set<DateTime>, Map<String, String>>((ref, params) async {
  final service = ref.read(editionServiceProvider);
  return await service.getAvailableDates(
    country: params['country'] ?? 'ug',
    month: params['month'] ?? '',
  );
});

/// Edition for a specific date
/// Key: { 'country': ..., 'date': 'YYYY-MM-DD' }
final editionByDateProvider =
    FutureProvider.family<Edition?, Map<String, String>>((ref, params) async {
  final service = ref.read(editionServiceProvider);
  return await service.getEditionByDate(
    country: params['country'] ?? 'ug',
    date: params['date'] ?? '',
  );
});

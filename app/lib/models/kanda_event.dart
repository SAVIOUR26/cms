/// A KandaNews event (named KandaEvent to avoid clashing with Flutter SDK types).
class KandaEvent {
  final int id;
  final String title;
  final String? description;
  final String eventDate;
  final String? endDate;
  final String? location;
  final bool isOnline;
  final String? registrationUrl;
  final String? coverImageUrl;
  final String country;
  final String category;
  final String status;
  final bool isFree;

  const KandaEvent({
    required this.id,
    required this.title,
    this.description,
    required this.eventDate,
    this.endDate,
    this.location,
    required this.isOnline,
    this.registrationUrl,
    this.coverImageUrl,
    required this.country,
    required this.category,
    required this.status,
    required this.isFree,
  });

  factory KandaEvent.fromJson(Map<String, dynamic> json) {
    return KandaEvent(
      id:              json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      title:           json['title'] ?? '',
      description:     json['description'],
      eventDate:       json['event_date'] ?? '',
      endDate:         json['end_date'],
      location:        json['location'],
      isOnline:        json['is_online'] == true || json['is_online'] == 1,
      registrationUrl: json['registration_url'],
      coverImageUrl:   json['cover_image_url'],
      country:         json['country'] ?? 'ug',
      category:        json['category'] ?? 'other',
      status:          json['status'] ?? 'published',
      isFree:          json['is_free'] == true || json['is_free'] == 1,
    );
  }

  bool get isPast {
    try {
      return DateTime.parse(eventDate).isBefore(DateTime.now());
    } catch (_) {
      return false;
    }
  }

  String get categoryLabel {
    const labels = {
      'conference':  'Conference',
      'webinar':     'Webinar',
      'workshop':    'Workshop',
      'networking':  'Networking',
      'launch':      'Launch',
      'other':       'Event',
    };
    return labels[category] ?? 'Event';
  }
}

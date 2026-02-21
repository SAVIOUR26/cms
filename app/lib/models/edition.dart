/// Edition model — represents a newspaper edition
class Edition {
  final int id;
  final String title;
  final String slug;
  final String country;
  final String editionDate;
  final String editionType;
  final String? coverImage;
  final String? htmlUrl;
  final String? zipUrl;
  final int pageCount;
  final bool isFree;
  final String? theme;
  final String? description;
  final bool accessible;

  const Edition({
    required this.id,
    required this.title,
    required this.slug,
    required this.country,
    required this.editionDate,
    this.editionType = 'daily',
    this.coverImage,
    this.htmlUrl,
    this.zipUrl,
    required this.pageCount,
    required this.isFree,
    this.theme,
    this.description,
    this.accessible = false,
  });

  String get typeLabel {
    switch (editionType) {
      case 'daily':
        return 'Daily Edition';
      case 'special':
        return 'Special Edition';
      case 'rate_card':
        return 'Rate Card';
      default:
        return editionType;
    }
  }

  String get typeIcon {
    switch (editionType) {
      case 'daily':
        return '📰';
      case 'special':
        return '⭐';
      case 'rate_card':
        return '💰';
      default:
        return '📄';
    }
  }

  factory Edition.fromJson(Map<String, dynamic> json) {
    return Edition(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      title: json['title'] ?? '',
      slug: json['slug'] ?? '',
      country: json['country'] ?? 'ug',
      editionDate: json['edition_date'] ?? '',
      editionType: json['edition_type'] ?? 'daily',
      coverImage: json['cover_image'],
      htmlUrl: json['html_url'],
      zipUrl: json['zip_url'],
      pageCount: json['page_count'] is int
          ? json['page_count']
          : int.tryParse(json['page_count']?.toString() ?? '0') ?? 0,
      isFree: json['is_free'] == true || json['is_free'] == 1,
      theme: json['theme'],
      description: json['description'],
      accessible: json['accessible'] == true,
    );
  }
}

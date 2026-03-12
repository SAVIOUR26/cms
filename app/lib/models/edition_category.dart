import 'package:flutter/material.dart';

/// A server-controlled edition category.
/// The server sends icon_name and color_hex; this model resolves them
/// to Flutter types locally so no Flutter code needs changing when the
/// server adds a new category with an existing icon.
class EditionCategory {
  final int id;
  final String slug;
  final String label;
  final String? description;
  final String iconName;
  final String colorHex;
  final String editionType;
  final int sortOrder;

  const EditionCategory({
    required this.id,
    required this.slug,
    required this.label,
    this.description,
    required this.iconName,
    required this.colorHex,
    required this.editionType,
    required this.sortOrder,
  });

  factory EditionCategory.fromJson(Map<String, dynamic> json) {
    return EditionCategory(
      id:          json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      slug:        json['slug'] ?? '',
      label:       json['label'] ?? '',
      description: json['description'],
      iconName:    json['icon_name'] ?? 'newspaper',
      colorHex:    json['color_hex'] ?? '#F05A1A',
      editionType: json['edition_type'] ?? 'special',
      sortOrder:   json['sort_order'] is int
                       ? json['sort_order']
                       : int.tryParse(json['sort_order']?.toString() ?? '0') ?? 0,
    );
  }

  /// Resolves the server-sent hex color to a Flutter Color.
  Color get color {
    final hex = colorHex.replaceFirst('#', '');
    return Color(int.parse('FF$hex', radix: 16));
  }

  /// Resolves the server-sent icon_name to a Flutter IconData.
  /// Add new entries here as needed — zero app update required for the server.
  IconData get icon => iconMap[iconName] ?? Icons.newspaper;

  static const Map<String, IconData> iconMap = {
    'school':          Icons.school,
    'business':        Icons.business,
    'rocket_launch':   Icons.rocket_launch,
    'campaign':        Icons.campaign,
    'work':            Icons.work,
    'podcasts':        Icons.podcasts,
    'play_circle':     Icons.play_circle_filled,
    'price_change':    Icons.price_change,
    'newspaper':       Icons.newspaper,
    'star':            Icons.star,
    'bolt':            Icons.bolt,
    'celebration':     Icons.celebration,
    'groups':          Icons.groups,
    'sports':          Icons.sports,
    'health':          Icons.health_and_safety,
    'science':         Icons.science,
    'music_note':      Icons.music_note,
    'movie':           Icons.movie,
    'local_offer':     Icons.local_offer,
    'public':          Icons.public,
    'volunteer':       Icons.volunteer_activism,
  };
}

import 'package:flutter/material.dart';
import 'edition_category.dart';

class HomeBanner {
  final int id;
  final String title;
  final String? subtitle;
  final String? actionUrl;
  final String? actionLabel;
  final String bgColorHex;
  final String? iconName;

  const HomeBanner({
    required this.id,
    required this.title,
    this.subtitle,
    this.actionUrl,
    this.actionLabel,
    required this.bgColorHex,
    this.iconName,
  });

  factory HomeBanner.fromJson(Map<String, dynamic> json) {
    return HomeBanner(
      id:          json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      title:       json['title'] ?? '',
      subtitle:    json['subtitle'],
      actionUrl:   json['action_url'],
      actionLabel: json['action_label'],
      bgColorHex:  json['bg_color_hex'] ?? '#F05A1A',
      iconName:    json['icon_name'],
    );
  }

  Color get bgColor {
    final hex = bgColorHex.replaceFirst('#', '');
    return Color(int.parse('FF$hex', radix: 16));
  }

  IconData get icon => EditionCategory.iconMap[iconName ?? ''] ?? Icons.campaign;
}

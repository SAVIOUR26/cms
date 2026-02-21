/// User model for KandaNews
class User {
  final int id;
  final String phone;
  final String? firstName;
  final String? surname;
  final String? fullName;
  final String? email;
  final int? age;
  final String? role;
  final String? roleDetail;
  final String country;
  final String? avatarUrl;

  const User({
    required this.id,
    required this.phone,
    this.firstName,
    this.surname,
    this.fullName,
    this.email,
    this.age,
    this.role,
    this.roleDetail,
    required this.country,
    this.avatarUrl,
  });

  bool get isProfileComplete => firstName != null && surname != null && role != null;

  String get displayName => fullName ?? firstName ?? 'User';

  String get initials {
    if (firstName != null && surname != null) {
      return '${firstName![0]}${surname![0]}'.toUpperCase();
    }
    return phone.substring(phone.length - 2);
  }

  String get roleLabel {
    switch (role) {
      case 'student':
        return '🎓 Student';
      case 'professional':
        return '💼 Professional';
      case 'entrepreneur':
        return '🚀 Entrepreneur';
      default:
        return '';
    }
  }

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      phone: json['phone'] ?? '',
      firstName: json['first_name'],
      surname: json['surname'],
      fullName: json['full_name'],
      email: json['email'],
      age: json['age'] != null ? int.tryParse(json['age'].toString()) : null,
      role: json['role'],
      roleDetail: json['role_detail'],
      country: json['country'] ?? 'ug',
      avatarUrl: json['avatar_url'],
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'phone': phone,
        'first_name': firstName,
        'surname': surname,
        'full_name': fullName,
        'email': email,
        'age': age,
        'role': role,
        'role_detail': roleDetail,
        'country': country,
        'avatar_url': avatarUrl,
      };

  User copyWith({
    String? firstName,
    String? surname,
    String? email,
    int? age,
    String? role,
    String? roleDetail,
    String? country,
    String? avatarUrl,
  }) {
    return User(
      id: id,
      phone: phone,
      firstName: firstName ?? this.firstName,
      surname: surname ?? this.surname,
      fullName: (firstName ?? this.firstName) != null && (surname ?? this.surname) != null
          ? '${firstName ?? this.firstName} ${surname ?? this.surname}'
          : fullName,
      email: email ?? this.email,
      age: age ?? this.age,
      role: role ?? this.role,
      roleDetail: roleDetail ?? this.roleDetail,
      country: country ?? this.country,
      avatarUrl: avatarUrl ?? this.avatarUrl,
    );
  }
}

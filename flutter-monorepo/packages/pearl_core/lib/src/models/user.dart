/// User model for authentication and profile
class User {
  final String id;
  final String email;
  /// The user's full name (maps to `full_name` in the Laravel API response).
  final String fullName;
  final String role; // 'customer', 'provider', 'admin', 'driver'
  final String? avatar;
  final bool isActive;
  final DateTime createdAt;
  final DateTime updatedAt;

  User({
    required this.id,
    required this.email,
    required this.fullName,
    required this.role,
    this.avatar,
    this.isActive = true,
    required this.createdAt,
    required this.updatedAt,
  });

  /// Convenience getter matching previous `name` field usages.
  String get name => fullName;

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as String,
      email: json['email'] as String,
      // Laravel returns `full_name`; fall back to `name` for compatibility.
      fullName: (json['full_name'] ?? json['name'] ?? '') as String,
      role: (json['role'] ?? 'customer') as String,
      avatar: json['avatar'] as String?,
      isActive: (json['is_active'] ?? true) as bool,
      // Laravel uses snake_case timestamps.
      createdAt: DateTime.parse((json['created_at'] ?? json['createdAt']) as String),
      updatedAt: DateTime.parse((json['updated_at'] ?? json['updatedAt']) as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'email': email,
      'full_name': fullName,
      'role': role,
      'avatar': avatar,
      'is_active': isActive,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  User copyWith({
    String? id,
    String? email,
    String? fullName,
    String? role,
    String? avatar,
    bool? isActive,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return User(
      id: id ?? this.id,
      email: email ?? this.email,
      fullName: fullName ?? this.fullName,
      role: role ?? this.role,
      avatar: avatar ?? this.avatar,
      isActive: isActive ?? this.isActive,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}

/// Auth session model
class AuthSession {
  final User? user;
  final String? token;
  final DateTime? expiresAt;

  AuthSession({
    this.user,
    this.token,
    this.expiresAt,
  });

  bool get isValid {
    if (user == null || token == null || expiresAt == null) return false;
    return DateTime.now().isBefore(expiresAt!);
  }

  factory AuthSession.fromJson(Map<String, dynamic> json) {
    return AuthSession(
      user: json['user'] != null ? User.fromJson(json['user'] as Map<String, dynamic>) : null,
      token: json['token'] as String?,
      expiresAt: json['expiresAt'] != null ? DateTime.parse(json['expiresAt'] as String) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'user': user?.toJson(),
      'token': token,
      'expiresAt': expiresAt?.toIso8601String(),
    };
  }
}

/// Location model
class Location {
  final double latitude;
  final double longitude;
  final String address;
  final String city;
  final String country;

  Location({
    required this.latitude,
    required this.longitude,
    required this.address,
    required this.city,
    required this.country,
  });

  factory Location.fromJson(Map<String, dynamic> json) {
    return Location(
      latitude: (json['latitude'] as num).toDouble(),
      longitude: (json['longitude'] as num).toDouble(),
      address: json['address'] as String,
      city: json['city'] as String,
      country: json['country'] as String,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'latitude': latitude,
      'longitude': longitude,
      'address': address,
      'city': city,
      'country': country,
    };
  }
}

/// Provider profile model
class ProviderProfile {
  final String id;
  final String userId;
  final String businessName;
  final String? description;
  final Location location;
  final double rating;
  final int reviewCount;
  final List<String> services;
  final bool isVerified;
  final DateTime createdAt;

  ProviderProfile({
    required this.id,
    required this.userId,
    required this.businessName,
    this.description,
    required this.location,
    required this.rating,
    required this.reviewCount,
    required this.services,
    required this.isVerified,
    required this.createdAt,
  });

  factory ProviderProfile.fromJson(Map<String, dynamic> json) {
    return ProviderProfile(
      id: json['id'] as String,
      userId: json['userId'] as String,
      businessName: json['businessName'] as String,
      description: json['description'] as String?,
      location: Location.fromJson(json['location'] as Map<String, dynamic>),
      rating: (json['rating'] as num).toDouble(),
      reviewCount: json['reviewCount'] as int,
      services: List<String>.from(json['services'] as List),
      isVerified: json['isVerified'] as bool? ?? false,
      createdAt: DateTime.parse(json['createdAt'] as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'userId': userId,
      'businessName': businessName,
      'description': description,
      'location': location.toJson(),
      'rating': rating,
      'reviewCount': reviewCount,
      'services': services,
      'isVerified': isVerified,
      'createdAt': createdAt.toIso8601String(),
    };
  }
}

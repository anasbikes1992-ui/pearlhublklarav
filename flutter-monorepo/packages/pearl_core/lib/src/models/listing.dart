import 'user.dart';

enum Platform {
  properties,
  stays,
  vehicles,
  events,
  experiences;

  String toJson() => name;
  static Platform fromJson(String json) => values.byName(json);
}

/// Listing model for marketplace items
class Listing {
  final String id;
  final String title;
  final String description;
  final Platform platform;
  final List<String> images;
  final double price;
  final String currency;
  final Location location;
  final ProviderProfile? provider;
  final List<String>? amenities;
  final double rating;
  final int reviewCount;
  final DateTime createdAt;
  final DateTime updatedAt;

  Listing({
    required this.id,
    required this.title,
    required this.description,
    required this.platform,
    required this.images,
    required this.price,
    required this.currency,
    required this.location,
    this.provider,
    this.amenities,
    required this.rating,
    required this.reviewCount,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Listing.fromJson(Map<String, dynamic> json) {
    return Listing(
      id: json['id'] as String,
      title: json['title'] as String,
      description: json['description'] as String,
      platform: Platform.fromJson(json['platform'] as String),
      images: List<String>.from(json['images'] as List),
      price: (json['price'] as num).toDouble(),
      currency: json['currency'] as String,
      location: Location.fromJson(json['location'] as Map<String, dynamic>),
      provider: json['provider'] != null
          ? ProviderProfile.fromJson(json['provider'] as Map<String, dynamic>)
          : null,
      amenities: json['amenities'] != null
          ? List<String>.from(json['amenities'] as List)
          : null,
      rating: (json['rating'] as num? ?? 0.0).toDouble(),
      reviewCount: json['reviewCount'] as int? ?? 0,
      createdAt: DateTime.parse(json['createdAt'] as String),
      updatedAt: DateTime.parse(json['updatedAt'] as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'platform': platform.toJson(),
      'images': images,
      'price': price,
      'currency': currency,
      'location': location.toJson(),
      'provider': provider?.toJson(),
      'amenities': amenities,
      'rating': rating,
      'reviewCount': reviewCount,
      'createdAt': createdAt.toIso8601String(),
      'updatedAt': updatedAt.toIso8601String(),
    };
  }

  Listing copyWith({
    String? id,
    String? title,
    String? description,
    Platform? platform,
    List<String>? images,
    double? price,
    String? currency,
    Location? location,
    ProviderProfile? provider,
    List<String>? amenities,
    double? rating,
    int? reviewCount,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Listing(
      id: id ?? this.id,
      title: title ?? this.title,
      description: description ?? this.description,
      platform: platform ?? this.platform,
      images: images ?? this.images,
      price: price ?? this.price,
      currency: currency ?? this.currency,
      location: location ?? this.location,
      provider: provider ?? this.provider,
      amenities: amenities ?? this.amenities,
      rating: rating ?? this.rating,
      reviewCount: reviewCount ?? this.reviewCount,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}

/// Review model
class Review {
  final String id;
  final String listingId;
  final User user;
  final double rating;
  final String comment;
  final DateTime createdAt;
  final DateTime updatedAt;

  Review({
    required this.id,
    required this.listingId,
    required this.user,
    required this.rating,
    required this.comment,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Review.fromJson(Map<String, dynamic> json) {
    return Review(
      id: json['id'] as String,
      listingId: json['listingId'] as String,
      user: User.fromJson(json['user'] as Map<String, dynamic>),
      rating: (json['rating'] as num).toDouble(),
      comment: json['comment'] as String,
      createdAt: DateTime.parse(json['createdAt'] as String),
      updatedAt: DateTime.parse(json['updatedAt'] as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'listingId': listingId,
      'user': user.toJson(),
      'rating': rating,
      'comment': comment,
      'createdAt': createdAt.toIso8601String(),
      'updatedAt': updatedAt.toIso8601String(),
    };
  }
}

/// Booking model
class Booking {
  final String id;
  final String listingId;
  final String userId;
  final DateTime checkIn;
  final DateTime checkOut;
  final double totalPrice;
  final String currency;
  final String status; // 'pending', 'confirmed', 'cancelled', 'completed'
  final DateTime createdAt;
  final DateTime updatedAt;

  Booking({
    required this.id,
    required this.listingId,
    required this.userId,
    required this.checkIn,
    required this.checkOut,
    required this.totalPrice,
    required this.currency,
    required this.status,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Booking.fromJson(Map<String, dynamic> json) {
    return Booking(
      id: json['id'] as String,
      listingId: json['listingId'] as String,
      userId: json['userId'] as String,
      checkIn: DateTime.parse(json['checkIn'] as String),
      checkOut: DateTime.parse(json['checkOut'] as String),
      totalPrice: (json['totalPrice'] as num).toDouble(),
      currency: json['currency'] as String,
      status: json['status'] as String,
      createdAt: DateTime.parse(json['createdAt'] as String),
      updatedAt: DateTime.parse(json['updatedAt'] as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'listingId': listingId,
      'userId': userId,
      'checkIn': checkIn.toIso8601String(),
      'checkOut': checkOut.toIso8601String(),
      'totalPrice': totalPrice,
      'currency': currency,
      'status': status,
      'createdAt': createdAt.toIso8601String(),
      'updatedAt': updatedAt.toIso8601String(),
    };
  }
}

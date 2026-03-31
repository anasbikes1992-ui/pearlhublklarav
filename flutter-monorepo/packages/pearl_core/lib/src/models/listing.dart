import 'user.dart';

// â”€â”€â”€ Value objects â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

/// Geographic coordinates attached to a listing.
class GeoPoint {
  final double latitude;
  final double longitude;

  const GeoPoint({required this.latitude, required this.longitude});

  factory GeoPoint.fromJson(Map<String, dynamic> json) => GeoPoint(
        latitude: (json['latitude'] as num).toDouble(),
        longitude: (json['longitude'] as num).toDouble(),
      );

  Map<String, dynamic> toJson() => {
        'latitude': latitude,
        'longitude': longitude,
      };
}

/// Slim provider info embedded in a listing response.
class ProviderInfo {
  final String id;
  final String fullName;
  final String email;

  const ProviderInfo({
    required this.id,
    required this.fullName,
    required this.email,
  });

  factory ProviderInfo.fromJson(Map<String, dynamic> json) => ProviderInfo(
        id: json['id'] as String,
        fullName: (json['full_name'] ?? json['name'] ?? '') as String,
        email: json['email'] as String,
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'full_name': fullName,
        'email': email,
      };
}

// â”€â”€â”€ Vertical enum â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

/// Maps to the `vertical` column in the Laravel listings table.
enum Vertical {
  property,
  stay,
  vehicle,
  taxi,
  event,
  sme;

  static Vertical fromString(String value) {
    return Vertical.values.firstWhere(
      (e) => e.name == value,
      orElse: () => Vertical.property,
    );
  }
}

// â”€â”€â”€ Core models â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

/// Listing model matching the Laravel API response structure.
class Listing {
  final String id;
  final String title;
  final String? description;
  final String slug;
  final Vertical vertical;
  final double price;
  final String currency;
  final String status;
  final bool isHidden;
  final GeoPoint? geo;
  final ProviderInfo? provider;
  final Map<String, dynamic>? metadata;
  final DateTime createdAt;
  final DateTime updatedAt;

  Listing({
    required this.id,
    required this.title,
    this.description,
    required this.slug,
    required this.vertical,
    required this.price,
    required this.currency,
    required this.status,
    required this.isHidden,
    this.geo,
    this.provider,
    this.metadata,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Listing.fromJson(Map<String, dynamic> json) {
    GeoPoint? geo;
    if (json['latitude'] != null && json['longitude'] != null) {
      geo = GeoPoint(
        latitude: (json['latitude'] as num).toDouble(),
        longitude: (json['longitude'] as num).toDouble(),
      );
    }

    return Listing(
      id: json['id'] as String,
      title: json['title'] as String,
      description: json['description'] as String?,
      slug: (json['slug'] ?? '') as String,
      vertical: Vertical.fromString((json['vertical'] ?? 'property') as String),
      price: (json['price'] as num).toDouble(),
      currency: (json['currency'] ?? 'LKR') as String,
      status: (json['status'] ?? 'pending_verification') as String,
      isHidden: (json['is_hidden'] ?? true) as bool,
      geo: geo,
      provider: json['provider'] != null
          ? ProviderInfo.fromJson(json['provider'] as Map<String, dynamic>)
          : null,
      metadata: json['metadata'] as Map<String, dynamic>?,
      createdAt: DateTime.parse((json['created_at'] ?? json['createdAt']) as String),
      updatedAt: DateTime.parse((json['updated_at'] ?? json['updatedAt']) as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'slug': slug,
      'vertical': vertical.name,
      'price': price,
      'currency': currency,
      'status': status,
      'is_hidden': isHidden,
      'latitude': geo?.latitude,
      'longitude': geo?.longitude,
      'provider': provider?.toJson(),
      'metadata': metadata,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  Listing copyWith({
    String? id,
    String? title,
    String? description,
    String? slug,
    Vertical? vertical,
    double? price,
    String? currency,
    String? status,
    bool? isHidden,
    GeoPoint? geo,
    ProviderInfo? provider,
    Map<String, dynamic>? metadata,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Listing(
      id: id ?? this.id,
      title: title ?? this.title,
      description: description ?? this.description,
      slug: slug ?? this.slug,
      vertical: vertical ?? this.vertical,
      price: price ?? this.price,
      currency: currency ?? this.currency,
      status: status ?? this.status,
      isHidden: isHidden ?? this.isHidden,
      geo: geo ?? this.geo,
      provider: provider ?? this.provider,
      metadata: metadata ?? this.metadata,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}

// â”€â”€â”€ Review â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

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
      listingId: (json['listing_id'] ?? json['listingId']) as String,
      user: User.fromJson(json['user'] as Map<String, dynamic>),
      rating: (json['rating'] as num).toDouble(),
      comment: (json['body'] ?? json['comment'] ?? '') as String,
      createdAt: DateTime.parse((json['created_at'] ?? json['createdAt']) as String),
      updatedAt: DateTime.parse((json['updated_at'] ?? json['updatedAt']) as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'listing_id': listingId,
      'user': user.toJson(),
      'rating': rating,
      'comment': comment,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
}

// â”€â”€â”€ Booking â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

class Booking {
  final String id;
  final String listingId;
  final String customerId;
  final DateTime? startAt;
  final DateTime? endAt;
  final double totalAmount;
  final String currency;
  final String status;
  final String paymentStatus;
  final DateTime createdAt;
  final DateTime updatedAt;

  Booking({
    required this.id,
    required this.listingId,
    required this.customerId,
    this.startAt,
    this.endAt,
    required this.totalAmount,
    required this.currency,
    required this.status,
    required this.paymentStatus,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Booking.fromJson(Map<String, dynamic> json) {
    return Booking(
      id: json['id'] as String,
      listingId: (json['listing_id'] ?? json['listingId']) as String,
      customerId: (json['customer_id'] ?? json['userId']) as String,
      startAt: json['start_at'] != null
          ? DateTime.parse(json['start_at'] as String)
          : null,
      endAt: json['end_at'] != null
          ? DateTime.parse(json['end_at'] as String)
          : null,
      totalAmount: (json['total_amount'] ?? json['totalPrice'] ?? 0).toDouble(),
      currency: (json['currency'] ?? 'LKR') as String,
      status: (json['status'] ?? 'pending') as String,
      paymentStatus: (json['payment_status'] ?? 'pending') as String,
      createdAt: DateTime.parse((json['created_at'] ?? json['createdAt']) as String),
      updatedAt: DateTime.parse((json['updated_at'] ?? json['updatedAt']) as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'listing_id': listingId,
      'customer_id': customerId,
      'start_at': startAt?.toIso8601String(),
      'end_at': endAt?.toIso8601String(),
      'total_amount': totalAmount,
      'currency': currency,
      'status': status,
      'payment_status': paymentStatus,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
}

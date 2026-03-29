// Core models for PearlHub Customer App

class AuthUser {
  final String id;
  final String email;
  final String name;
  final String? avatar;

  AuthUser({
    required this.id,
    required this.email,
    required this.name,
    this.avatar,
  });

  factory AuthUser.fromJson(Map<String, dynamic> json) {
    return AuthUser(
      id: json['id'] as String,
      email: json['email'] as String,
      name: json['name'] as String,
      avatar: json['avatar'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'email': email,
      'name': name,
      'avatar': avatar,
    };
  }
}

class Listing {
  final String id;
  final String title;
  final String description;
  final String type;
  final String city;
  final double price;
  final double rating;
  final int reviews;
  final List<String> images;
  final String slug;

  Listing({
    required this.id,
    required this.title,
    required this.description,
    required this.type,
    required this.city,
    required this.price,
    required this.rating,
    required this.reviews,
    required this.images,
    required this.slug,
  });

  factory Listing.fromJson(Map<String, dynamic> json) {
    return Listing(
      id: json['id'] as String,
      title: json['title'] as String,
      description: json['description'] as String,
      type: json['type'] as String,
      city: json['city'] as String,
      price: (json['price'] as num).toDouble(),
      rating: (json['rating'] as num?)?.toDouble() ?? 0.0,
      reviews: (json['reviews'] as num?)?.toInt() ?? 0,
      images: List<String>.from(json['images'] as List? ?? []),
      slug: json['slug'] as String,
    );
  }
}

class Booking {
  final String id;
  final String listingId;
  final String userId;
  final DateTime checkInDate;
  final DateTime checkOutDate;
  final int nights;
  final double totalPrice;
  final String status;
  final DateTime createdAt;
  final Listing? listing;

  Booking({
    required this.id,
    required this.listingId,
    required this.userId,
    required this.checkInDate,
    required this.checkOutDate,
    required this.nights,
    required this.totalPrice,
    required this.status,
    required this.createdAt,
    this.listing,
  });

  factory Booking.fromJson(Map<String, dynamic> json) {
    return Booking(
      id: json['id'] as String,
      listingId: json['listing_id'] as String,
      userId: json['user_id'] as String,
      checkInDate: DateTime.parse(json['check_in_date'] as String),
      checkOutDate: DateTime.parse(json['check_out_date'] as String),
      nights: json['nights'] as int,
      totalPrice: (json['total_price'] as num).toDouble(),
      status: json['status'] as String,
      createdAt: DateTime.parse(json['created_at'] as String),
      listing: json['listing'] != null 
        ? Listing.fromJson(json['listing'] as Map<String, dynamic>) 
        : null,
    );
  }
}

class User {
  final String id;
  final String email;
  final String name;
  final String? phone;
  final String? avatar;
  final bool isVerified;

  User({
    required this.id,
    required this.email,
    required this.name,
    this.phone,
    this.avatar,
    this.isVerified = false,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as String,
      email: json['email'] as String,
      name: json['name'] as String,
      phone: json['phone'] as String?,
      avatar: json['avatar'] as String?,
      isVerified: json['is_verified'] as bool? ?? false,
    );
  }
}

class BookingEstimate {
  final double pricePerNight;
  final int nights;
  final double subtotal;
  final double fees;
  final double taxes;
  final double total;

  BookingEstimate({
    required this.pricePerNight,
    required this.nights,
    required this.subtotal,
    required this.fees,
    required this.taxes,
    required this.total,
  });

  factory BookingEstimate.calculate(double price, int nights) {
    final subtotal = price * nights;
    final fees = subtotal * 0.1;
    final taxes = (subtotal + fees) * 0.08;
    final total = subtotal + fees + taxes;
    
    return BookingEstimate(
      pricePerNight: price,
      nights: nights,
      subtotal: subtotal,
      fees: fees,
      taxes: taxes,
      total: total,
    );
  }
}

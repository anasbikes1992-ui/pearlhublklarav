class ProviderListing {
  final String id;
  final String title;
  final String? slug;
  final String vertical;
  final String status;
  final double price;
  final String currency;
  final bool isHidden;
  final DateTime? verifiedAt;
  final DateTime createdAt;

  const ProviderListing({
    required this.id,
    required this.title,
    this.slug,
    required this.vertical,
    required this.status,
    required this.price,
    required this.currency,
    required this.isHidden,
    this.verifiedAt,
    required this.createdAt,
  });

  factory ProviderListing.fromJson(Map<String, dynamic> json) {
    return ProviderListing(
      id: json['id'] as String,
      title: json['title'] as String,
      slug: json['slug'] as String?,
      vertical: json['vertical'] as String,
      status: json['status'] as String,
      price: (json['price'] as num).toDouble(),
      currency: json['currency'] as String? ?? 'LKR',
      isHidden: json['is_hidden'] as bool? ?? false,
      verifiedAt: json['verified_at'] != null
          ? DateTime.parse(json['verified_at'] as String)
          : null,
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }
}

class ProviderBooking {
  final String id;
  final String listingTitle;
  final String customerName;
  final String status;
  final double totalAmount;
  final String currency;
  final DateTime startDate;
  final DateTime endDate;
  final DateTime createdAt;

  const ProviderBooking({
    required this.id,
    required this.listingTitle,
    required this.customerName,
    required this.status,
    required this.totalAmount,
    required this.currency,
    required this.startDate,
    required this.endDate,
    required this.createdAt,
  });

  factory ProviderBooking.fromJson(Map<String, dynamic> json) {
    return ProviderBooking(
      id: json['id'] as String,
      listingTitle: (json['listing'] as Map<String, dynamic>?)?['title'] as String? ?? '—',
      customerName: (json['customer'] as Map<String, dynamic>?)?['full_name'] as String? ?? '—',
      status: json['status'] as String,
      totalAmount: (json['total_amount'] as num).toDouble(),
      currency: json['currency'] as String? ?? 'LKR',
      startDate: DateTime.parse(json['start_date'] as String),
      endDate: DateTime.parse(json['end_date'] as String),
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }
}

class ProviderStats {
  final int activeListings;
  final int pendingBookings;
  final double totalRevenue;
  final double averageRating;

  const ProviderStats({
    required this.activeListings,
    required this.pendingBookings,
    required this.totalRevenue,
    required this.averageRating,
  });

  factory ProviderStats.fromJson(Map<String, dynamic> json) {
    return ProviderStats(
      activeListings: json['active_listings'] as int? ?? 0,
      pendingBookings: json['pending_bookings'] as int? ?? 0,
      totalRevenue: (json['total_revenue'] as num?)?.toDouble() ?? 0.0,
      averageRating: (json['average_rating'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

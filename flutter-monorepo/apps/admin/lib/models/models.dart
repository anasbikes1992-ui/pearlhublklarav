class AdminUser {
  final String id;
  final String fullName;
  final String email;
  final String role;
  final bool isActive;
  final DateTime createdAt;

  const AdminUser({
    required this.id,
    required this.fullName,
    required this.email,
    required this.role,
    required this.isActive,
    required this.createdAt,
  });

  factory AdminUser.fromJson(Map<String, dynamic> json) {
    return AdminUser(
      id: json['id'] as String,
      fullName: json['full_name'] as String,
      email: json['email'] as String,
      role: json['role'] as String,
      isActive: json['is_active'] as bool? ?? true,
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }
}

class VerificationListing {
  final String id;
  final String title;
  final String vertical;
  final String status;
  final String providerName;
  final String providerEmail;
  final DateTime createdAt;

  const VerificationListing({
    required this.id,
    required this.title,
    required this.vertical,
    required this.status,
    required this.providerName,
    required this.providerEmail,
    required this.createdAt,
  });

  factory VerificationListing.fromJson(Map<String, dynamic> json) {
    final provider = json['provider'] as Map<String, dynamic>?;
    return VerificationListing(
      id: json['id'] as String,
      title: json['title'] as String,
      vertical: json['vertical'] as String,
      status: json['status'] as String,
      providerName: provider?['full_name'] as String? ?? '—',
      providerEmail: provider?['email'] as String? ?? '—',
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }
}

class PlatformStats {
  final int totalUsers;
  final int totalListings;
  final int pendingVerifications;
  final double platformRevenue;

  const PlatformStats({
    required this.totalUsers,
    required this.totalListings,
    required this.pendingVerifications,
    required this.platformRevenue,
  });

  factory PlatformStats.fromJson(Map<String, dynamic> json) {
    return PlatformStats(
      totalUsers: json['total_users'] as int? ?? 0,
      totalListings: json['total_listings'] as int? ?? 0,
      pendingVerifications: json['pending_verifications'] as int? ?? 0,
      platformRevenue: (json['platform_revenue'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

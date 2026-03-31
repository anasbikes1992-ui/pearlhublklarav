class PromoCode {
  final String id;
  final String code;
  final String? listingId;
  final String issuedBy;
  final String type; // sale_confirmation, discount_fixed, discount_percent
  final double value;
  final String? vertical;
  final int maxUses;
  final int usedCount;
  final DateTime? expiresAt;
  final bool isActive;
  final DateTime createdAt;

  const PromoCode({
    required this.id,
    required this.code,
    this.listingId,
    required this.issuedBy,
    required this.type,
    required this.value,
    this.vertical,
    required this.maxUses,
    required this.usedCount,
    this.expiresAt,
    required this.isActive,
    required this.createdAt,
  });

  bool get isValid =>
      isActive &&
      usedCount < maxUses &&
      (expiresAt == null || expiresAt!.isAfter(DateTime.now()));

  factory PromoCode.fromJson(Map<String, dynamic> json) => PromoCode(
        id: json['id'] as String,
        code: json['code'] as String,
        listingId: json['listing_id'] as String?,
        issuedBy: json['issued_by'] as String,
        type: json['type'] as String,
        value: (json['value'] as num).toDouble(),
        vertical: json['vertical'] as String?,
        maxUses: json['max_uses'] as int,
        usedCount: json['used_count'] as int,
        expiresAt: json['expires_at'] != null
            ? DateTime.parse(json['expires_at'] as String)
            : null,
        isActive: json['is_active'] as bool,
        createdAt: DateTime.parse(json['created_at'] as String),
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'code': code,
        'listing_id': listingId,
        'issued_by': issuedBy,
        'type': type,
        'value': value,
        'vertical': vertical,
        'max_uses': maxUses,
        'used_count': usedCount,
        'expires_at': expiresAt?.toIso8601String(),
        'is_active': isActive,
        'created_at': createdAt.toIso8601String(),
      };
}

class CashbackRecord {
  final String id;
  final String bookingId;
  final String customerId;
  final String providerId;
  final double saleAmount;
  final double cashbackRate;
  final double cashbackAmount;
  final String currency;
  final String status; // pending, approved, credited, rejected
  final DateTime? confirmedAt;
  final DateTime? creditedAt;
  final DateTime createdAt;

  const CashbackRecord({
    required this.id,
    required this.bookingId,
    required this.customerId,
    required this.providerId,
    required this.saleAmount,
    required this.cashbackRate,
    required this.cashbackAmount,
    required this.currency,
    required this.status,
    this.confirmedAt,
    this.creditedAt,
    required this.createdAt,
  });

  factory CashbackRecord.fromJson(Map<String, dynamic> json) => CashbackRecord(
        id: json['id'] as String,
        bookingId: json['booking_id'] as String,
        customerId: json['customer_id'] as String,
        providerId: json['provider_id'] as String,
        saleAmount: (json['sale_amount'] as num).toDouble(),
        cashbackRate: (json['cashback_rate'] as num).toDouble(),
        cashbackAmount: (json['cashback_amount'] as num).toDouble(),
        currency: json['currency'] as String,
        status: json['status'] as String,
        confirmedAt: json['confirmed_at'] != null
            ? DateTime.parse(json['confirmed_at'] as String)
            : null,
        creditedAt: json['credited_at'] != null
            ? DateTime.parse(json['credited_at'] as String)
            : null,
        createdAt: DateTime.parse(json['created_at'] as String),
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'booking_id': bookingId,
        'customer_id': customerId,
        'provider_id': providerId,
        'sale_amount': saleAmount,
        'cashback_rate': cashbackRate,
        'cashback_amount': cashbackAmount,
        'currency': currency,
        'status': status,
        'confirmed_at': confirmedAt?.toIso8601String(),
        'credited_at': creditedAt?.toIso8601String(),
        'created_at': createdAt.toIso8601String(),
      };
}

class WalletBalance {
  final double balance;
  final String currency;
  final String status;

  const WalletBalance({
    required this.balance,
    required this.currency,
    required this.status,
  });

  factory WalletBalance.fromJson(Map<String, dynamic> json) => WalletBalance(
        balance: (json['balance'] as num).toDouble(),
        currency: json['currency'] as String,
        status: json['status'] as String,
      );
}

class FeeBreakdown {
  final double baseAmount;
  final double commission;
  final double vat;
  final double tourismTax;
  final double serviceCharge;
  final double total;
  final double listingFee;

  const FeeBreakdown({
    required this.baseAmount,
    required this.commission,
    required this.vat,
    required this.tourismTax,
    required this.serviceCharge,
    required this.total,
    required this.listingFee,
  });

  factory FeeBreakdown.fromJson(Map<String, dynamic> json) => FeeBreakdown(
        baseAmount: (json['base_amount'] as num).toDouble(),
        commission: (json['commission'] as num).toDouble(),
        vat: (json['vat'] as num).toDouble(),
        tourismTax: (json['tourism_tax'] as num).toDouble(),
        serviceCharge: (json['service_charge'] as num).toDouble(),
        total: (json['total'] as num).toDouble(),
        listingFee: (json['listing_fee'] as num).toDouble(),
      );
}

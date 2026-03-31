import '../network/shared_api_client.dart';
import '../models/payment_models.dart';

class PaymentApiService {
  final SharedApiClient _client;

  PaymentApiService(this._client);

  Future<Map<String, dynamic>> validatePromoCode(
      String code, String? vertical, double? amount) async {
    final response = await _client.post('/promo-codes/validate', data: {
      'code': code,
      'vertical': vertical,
      'amount': amount,
    });
    return response.data as Map<String, dynamic>;
  }

  Future<PromoCode> generatePromoCode(Map<String, dynamic> data) async {
    final response = await _client.post('/promo-codes', data: data);
    return PromoCode.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  Future<PromoCode> redeemPromoCode(String code) async {
    final response =
        await _client.post('/promo-codes/redeem', data: {'code': code});
    return PromoCode.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  Future<List<CashbackRecord>> getCashbackRecords() async {
    final response = await _client.get('/cashback');
    final list = response.data['data'] as List;
    return list
        .map((e) => CashbackRecord.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<CashbackRecord> confirmCashback(String recordId) async {
    final response = await _client.post('/cashback/$recordId/confirm');
    return CashbackRecord.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  Future<WalletBalance> getWalletBalance() async {
    final response = await _client.get('/wallet/balance');
    return WalletBalance.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  Future<FeeBreakdown> calculateFees(String vertical, double amount) async {
    final response = await _client.post('/fees/calculate', data: {
      'vertical': vertical,
      'amount': amount,
    });
    return FeeBreakdown.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }
}

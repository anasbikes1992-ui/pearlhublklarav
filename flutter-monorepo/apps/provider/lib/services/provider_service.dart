import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:pearl_core/pearl_core.dart';
import '../models/models.dart';

class ProviderAuthService {
  final SharedApiClient apiClient;
  final FlutterSecureStorage _storage;

  String? _token;
  Map<String, dynamic>? _currentUser;

  ProviderAuthService({required this.apiClient})
      : _storage = const FlutterSecureStorage();

  bool get isAuthenticated => _token != null;
  Map<String, dynamic>? get currentUser => _currentUser;

  Future<void> initialize() async {
    _token = await _storage.read(key: 'provider_auth_token');
    if (_token != null) {
      try {
        apiClient.setToken(_token!);
        final resp = await apiClient.get('/users/profile');
        final profPayload = (resp.data['data'] ?? resp.data) as Map<String, dynamic>;
        _currentUser = profPayload['user'] as Map<String, dynamic>? ?? profPayload;
      } catch (_) {
        await logout();
      }
    }
  }

  Future<void> login(String email, String password) async {
    final resp = await apiClient.post(
      '/auth/login',
      data: {'email': email, 'password': password},
    );

    final payload = (resp.data['data'] ?? resp.data) as Map<String, dynamic>;
    _token = payload['token'] as String?;
    _currentUser = payload['user'] as Map<String, dynamic>?;

    if (_token != null) {
      await _storage.write(key: 'provider_auth_token', value: _token!);
      apiClient.setToken(_token!);
    }
  }

  Future<void> logout() async {
    _token = null;
    _currentUser = null;
    await _storage.delete(key: 'provider_auth_token');
    apiClient.clearToken();
  }
}

class ProviderApiService {
  final SharedApiClient apiClient;

  ProviderApiService({required this.apiClient});

  Future<List<ProviderListing>> getMyListings() async {
    final resp = await apiClient.get('/listings/my');
    // /listings/my returns a Collection (non-paginated) wrapped in { success, data: [...] }
    final data = resp.data['data'] as List<dynamic>? ?? [];
    return data
        .map((e) => ProviderListing.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<ProviderListing> createListing(Map<String, dynamic> payload) async {
    final resp = await apiClient.post('/listings', data: payload);
    return ProviderListing.fromJson(resp.data['data'] as Map<String, dynamic>);
  }

  Future<ProviderListing> updateListing(
      String id, Map<String, dynamic> payload) async {
    final resp = await apiClient.put('/listings/$id', data: payload);
    return ProviderListing.fromJson(resp.data['data'] as Map<String, dynamic>);
  }

  Future<void> deleteListing(String id) async {
    await apiClient.delete('/listings/$id');
  }

  Future<List<ProviderBooking>> getMyBookings({String? status}) async {
    final params = <String, dynamic>{'scope': 'provider'};
    if (status != null) params['status'] = status;
    final resp = await apiClient.get('/bookings', queryParameters: params);
    final data = resp.data['data'] as List<dynamic>? ?? [];
    return data
        .map((e) => ProviderBooking.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<ProviderBooking> updateBookingStatus(
      String id, String status) async {
    final resp =
        await apiClient.put('/bookings/$id', data: {'status': status});
    return ProviderBooking.fromJson(resp.data['data'] as Map<String, dynamic>);
  }
}

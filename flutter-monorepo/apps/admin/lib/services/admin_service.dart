import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:pearl_core/pearl_core.dart';
import '../models/models.dart';

class AdminAuthService {
  final SharedApiClient apiClient;
  final FlutterSecureStorage _storage;

  String? _token;
  Map<String, dynamic>? _currentUser;

  AdminAuthService({required this.apiClient})
      : _storage = const FlutterSecureStorage();

  bool get isAuthenticated => _token != null;

  Future<void> initialize() async {
    _token = await _storage.read(key: 'admin_auth_token');
    if (_token != null) {
      try {
        apiClient.setToken(_token!);
        await apiClient.get('/users/profile');
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

    _token = resp.data['token'] as String?;
    if (_token != null) {
      await _storage.write(key: 'admin_auth_token', value: _token!);
      apiClient.setToken(_token!);
    }
  }

  Future<void> logout() async {
    _token = null;
    _currentUser = null;
    await _storage.delete(key: 'admin_auth_token');
    apiClient.clearToken();
  }
}

class AdminApiService {
  final SharedApiClient apiClient;

  AdminApiService({required this.apiClient});

  Future<PlatformStats> getStats() async {
    final resp = await apiClient.get('/admin/stats');
    return PlatformStats.fromJson(resp.data['data'] as Map<String, dynamic>);
  }

  Future<List<AdminUser>> getUsers({int page = 1, String? role}) async {
    final params = <String, dynamic>{'page': page};
    if (role != null) params['role'] = role;
    final resp = await apiClient.get('/admin/users', queryParameters: params);
    final data = resp.data['data'] as List<dynamic>? ?? [];
    return data
        .map((e) => AdminUser.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<void> toggleUserStatus(String userId, bool isActive) async {
    await apiClient.put('/admin/users/$userId', data: {'is_active': isActive});
  }

  Future<List<VerificationListing>> getPendingVerifications() async {
    final resp = await apiClient
        .get('/listings', queryParameters: {'status': 'pending_verification'});
    final data = resp.data['data'] as List<dynamic>? ?? [];
    return data
        .map((e) => VerificationListing.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<void> approveListing(String id) async {
    await apiClient.post('/listings/$id/verify', data: {
      'status': 'approved',
      'inspected_at': DateTime.now().toIso8601String(),
    });
  }

  Future<void> rejectListing(String id, String reason) async {
    await apiClient.post('/listings/$id/verify', data: {
      'status': 'rejected',
      'notes': reason,
      'inspected_at': DateTime.now().toIso8601String(),
    });
  }
}

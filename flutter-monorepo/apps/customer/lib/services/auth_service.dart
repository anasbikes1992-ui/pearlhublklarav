import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:pearl_core/pearl_core.dart';
import '../models/models.dart';

class AuthService {
  final SharedApiClient apiClient;
  final FlutterSecureStorage secureStorage;
  
  AuthUser? _currentUser;
  String? _token;

  AuthService({
    required this.apiClient,
    FlutterSecureStorage? secureStorage,
  }) : secureStorage = secureStorage ?? const FlutterSecureStorage();

  AuthUser? get currentUser => _currentUser;
  String? get token => _token;
  bool get isAuthenticated => _currentUser != null && _token != null;

  Future<void> initialize() async {
    _token = await secureStorage.read(key: 'auth_token');
    if (_token != null) {
      try {
        await _loadCurrentUser();
      } catch (e) {
        // Token invalid, clear it
        await logout();
      }
    }
  }

  Future<AuthUser> register(String name, String email, String password) async {
    try {
      final response = await apiClient.post(
        '/auth/register',
        data: {
          'name': name,
          'email': email,
          'password': password,
        },
      );

      _token = response.data['token'] as String?;
      if (_token != null) {
        await secureStorage.write(key: 'auth_token', value: _token!);
        apiClient.setToken(_token!);
      }

      _currentUser = AuthUser.fromJson(response.data['user'] as Map<String, dynamic>);
      return _currentUser!;
    } catch (e) {
      rethrow;
    }
  }

  Future<AuthUser> login(String email, String password) async {
    try {
      final response = await apiClient.post(
        '/auth/login',
        data: {
          'email': email,
          'password': password,
        },
      );

      _token = response.data['token'] as String?;
      if (_token != null) {
        await secureStorage.write(key: 'auth_token', value: _token!);
        apiClient.setToken(_token!);
      }

      _currentUser = AuthUser.fromJson(response.data['user'] as Map<String, dynamic>);
      return _currentUser!;
    } catch (e) {
      rethrow;
    }
  }

  Future<void> logout() async {
    try {
      await apiClient.post('/auth/logout', data: {});
    } catch (_) {
      // Ignore errors on logout
    } finally {
      _token = null;
      _currentUser = null;
      await secureStorage.delete(key: 'auth_token');
      apiClient.setToken(null);
    }
  }

  Future<void> _loadCurrentUser() async {
    try {
      final response = await apiClient.get('/users/me');
      _currentUser = AuthUser.fromJson(response.data as Map<String, dynamic>);
    } catch (e) {
      rethrow;
    }
  }
}

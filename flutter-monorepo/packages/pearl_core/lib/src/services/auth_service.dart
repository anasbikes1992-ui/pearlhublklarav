import 'dart:convert';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../models/user.dart';
import '../network/shared_api_client.dart';
import 'api_exceptions.dart';

/// Persists and manages authentication state for the PearlHub apps.
///
/// Usage:
/// ```dart
/// final client = SharedApiClient(baseUrl: 'https://api.pearlhub.lk/api/v1');
/// final authService = AuthService(client: client);
/// await authService.init();          // restore token on cold start
/// await authService.login('...', '...');
/// ```
class AuthService {
  AuthService({
    required SharedApiClient client,
    FlutterSecureStorage? storage,
  })  : _client = client,
        _storage = storage ?? const FlutterSecureStorage();

  final SharedApiClient _client;
  final FlutterSecureStorage _storage;

  static const _tokenKey = 'pearl_token';
  static const _userKey = 'pearl_user';

  User? _user;
  String? _token;

  User? get currentUser => _user;
  String? get token => _token;
  bool get isAuthenticated => _token != null && _token!.isNotEmpty;

  // ── Lifecycle ────────────────────────────────────────────────────────────

  /// Call once on app start to restore a persisted session.
  Future<void> init() async {
    final t = await _storage.read(key: _tokenKey);
    final uJson = await _storage.read(key: _userKey);
    if (t != null && t.isNotEmpty) {
      _token = t;
      _client.setToken(t);
      if (uJson != null) {
        try {
          _user = User.fromJson(_decodeJson(uJson));
        } catch (_) {
          // Corrupt storage — reset
          await _clearStorage();
        }
      }
    }
  }

  // ── Auth actions ─────────────────────────────────────────────────────────

  Future<User> register({
    required String fullName,
    required String email,
    required String password,
    String? phone,
  }) async {
    final res = await _client.post('/auth/register', data: {
      'full_name': fullName,
      'email': email,
      'password': password,
      'password_confirmation': password,
      if (phone != null) 'phone': phone,
    });
    return _handleAuthResponse(res.data as Map<String, dynamic>);
  }

  Future<User> login({
    required String email,
    required String password,
  }) async {
    final res = await _client.post('/auth/login', data: {
      'email': email,
      'password': password,
    });
    return _handleAuthResponse(res.data as Map<String, dynamic>);
  }

  Future<void> logout() async {
    try {
      await _client.post('/auth/logout');
    } catch (_) {
      // Best-effort logout to the server
    } finally {
      _client.clearToken();
      _user = null;
      _token = null;
      await _clearStorage();
    }
  }

  // ── Private helpers ──────────────────────────────────────────────────────

  Future<User> _handleAuthResponse(Map<String, dynamic> body) async {
    // Laravel BaseApiController wraps data in { success, message, data: {...} }
    final payload = (body['data'] ?? body) as Map<String, dynamic>;
    final token = payload['token'] as String?;
    if (token == null || token.isEmpty) {
      throw const ApiException('No token returned from server');
    }

    final userJson = payload['user'] as Map<String, dynamic>?;
    if (userJson == null) {
      throw const ApiException('No user object returned from server');
    }

    final user = User.fromJson(userJson);

    _token = token;
    _user = user;
    _client.setToken(token);

    await _storage.write(key: _tokenKey, value: token);
    await _storage.write(key: _userKey, value: _encodeJson(userJson));

    return user;
  }

  Future<void> _clearStorage() async {
    await _storage.delete(key: _tokenKey);
    await _storage.delete(key: _userKey);
  }

  Map<String, dynamic> _decodeJson(String raw) {
    return jsonDecode(raw) as Map<String, dynamic>;
  }

  String _encodeJson(Map<String, dynamic> map) {
    return jsonEncode(map);
  }
}

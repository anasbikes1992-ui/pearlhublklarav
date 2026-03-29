import 'package:dio/dio.dart';

class SharedApiClient {
  SharedApiClient({required String baseUrl}) : _dio = Dio(BaseOptions(baseUrl: baseUrl)) {
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          if (_accessToken != null && _accessToken!.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $_accessToken';
          }
          handler.next(options);
        },
        onError: (error, handler) async {
          if (error.response?.statusCode == 401 && _refreshCallback != null) {
            final refreshed = await _refreshCallback!.call();
            if (refreshed != null && refreshed.isNotEmpty) {
              _accessToken = refreshed;
              final retry = await _dio.fetch(error.requestOptions);
              return handler.resolve(retry);
            }
          }
          handler.next(error);
        },
      ),
    );
  }

  final Dio _dio;
  String? _accessToken;
  Future<String?> Function()? _refreshCallback;

  void setAccessToken(String? token) {
    _accessToken = token;
  }

  /// Convenience alias for [setAccessToken].
  void setToken(String token) => setAccessToken(token);

  /// Clears the stored access token.
  void clearToken() => setAccessToken(null);

  void setRefreshCallback(Future<String?> Function() callback) {
    _refreshCallback = callback;
  }

  Future<Response<dynamic>> get(String path, {Map<String, dynamic>? queryParameters}) {
    return _dio.get(path, queryParameters: queryParameters);
  }

  Future<Response<dynamic>> delete(String path) {
    return _dio.delete(path);
  }

  Future<Response<dynamic>> patch(String path, {Object? data}) {
    return _dio.patch(path, data: data);
  }

  Future<Response<dynamic>> post(String path, {Object? data}) {
    return _dio.post(path, data: data);
  }

  Future<Response<dynamic>> put(String path, {Object? data}) {
    return _dio.put(path, data: data);
  }
}

/// Base exception for all API errors
class ApiException implements Exception {
  final String message;
  final int? statusCode;

  const ApiException(this.message, {this.statusCode});

  @override
  String toString() => 'ApiException($statusCode): $message';
}

/// Thrown when a resource is not found (HTTP 404)
class NotFoundException extends ApiException {
  const NotFoundException(String message) : super(message, statusCode: 404);
}

/// Thrown when auth is invalid or token expired (HTTP 401)
class UnauthorizedException extends ApiException {
  const UnauthorizedException(String message) : super(message, statusCode: 401);
}

/// Thrown on server-side errors (HTTP 5xx)
class ServerException extends ApiException {
  const ServerException(String message, {int statusCode = 500})
      : super(message, statusCode: statusCode);
}

/// Thrown on validation errors (HTTP 422)
class ValidationException extends ApiException {
  final Map<String, List<String>> errors;

  const ValidationException(String message, this.errors)
      : super(message, statusCode: 422);
}

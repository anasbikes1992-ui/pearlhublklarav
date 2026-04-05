import '../models/social_models.dart';
import 'api_exceptions.dart';
import '../network/shared_api_client.dart';

class SocialApiService {
  final SharedApiClient _client;

  const SocialApiService(this._client);

  /// GET /api/v1/social/feed
  /// [vertical] optional filter e.g. 'property', 'stays', 'experience'
  Future<List<SocialPost>> getFeed({String? vertical, int page = 1}) async {
    final params = <String, String>{'page': '$page'};
    if (vertical != null) params['vertical'] = vertical;

    final response = await _client.get('/api/v1/social/feed', queryParameters: params);
    _assertOk(response, 'getFeed');

    final body = response.data as Map<String, dynamic>;
    final data = (body['data']?['data'] ?? body['data'] ?? []) as List<dynamic>;
    return data
        .map((e) => SocialPost.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  /// POST /api/v1/social/posts — requires auth
  Future<SocialPost> createPost({
    required String content,
    List<String>? mediaUrls,
    String? verticalTag,
    String? listingId,
  }) async {
    final payload = <String, dynamic>{
      'content': content,
      if (mediaUrls != null) 'media_urls': mediaUrls,
      if (verticalTag != null) 'vertical_tag': verticalTag,
      if (listingId != null) 'listing_id': listingId,
    };

    final response = await _client.post('/api/v1/social/posts', data: payload);
    _assertOk(response, 'createPost');

    final body = response.data as Map<String, dynamic>;
    return SocialPost.fromJson((body['data'] ?? body) as Map<String, dynamic>);
  }

  /// POST /api/v1/social/posts/{postId}/like — toggles like, requires auth
  Future<({bool liked, int likesCount})> toggleLike(String postId) async {
    final response =
      await _client.post('/api/v1/social/posts/$postId/like', data: {});
    _assertOk(response, 'toggleLike');

    final body = response.data as Map<String, dynamic>;
    final data = body['data'] as Map<String, dynamic>? ?? {};
    return (
      liked: (data['liked'] as bool?) ?? false,
      likesCount: (data['likes_count'] as num?)?.toInt() ?? 0,
    );
  }

  /// GET /api/v1/social/posts/{postId}/comments
  Future<List<SocialComment>> getComments(String postId, {int page = 1}) async {
    final response = await _client.get(
      '/api/v1/social/posts/$postId/comments',
      queryParameters: {'page': '$page'},
    );
    _assertOk(response, 'getComments');

    final body = response.data as Map<String, dynamic>;
    final data =
        (body['data']?['data'] ?? body['data'] ?? []) as List<dynamic>;
    return data
        .map((e) => SocialComment.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  /// POST /api/v1/social/posts/{postId}/comments — requires auth
  Future<SocialComment> addComment(
    String postId, {
    required String body,
    String? parentId,
  }) async {
    final payload = <String, dynamic>{
      'body': body,
      if (parentId != null) 'parent_id': parentId,
    };

    final response = await _client
      .post('/api/v1/social/posts/$postId/comments', data: payload);
    _assertOk(response, 'addComment');

    final responseBody = response.data as Map<String, dynamic>;
    final respBody = responseBody['data'] as Map<String, dynamic>? ?? responseBody;
    return SocialComment.fromJson(respBody);
  }

  /// POST /api/v1/social/users/{userId}/follow — requires auth
  Future<void> follow(String userId) async {
    final response =
      await _client.post('/api/v1/social/users/$userId/follow', data: {});
    _assertOk(response, 'follow');
  }

  /// DELETE /api/v1/social/users/{userId}/follow — requires auth
  Future<void> unfollow(String userId) async {
    final response =
        await _client.delete('/api/v1/social/users/$userId/follow');
    _assertOk(response, 'unfollow');
  }

  /// GET /api/v1/social/users/{userId}/profile
  Future<SocialProfile> getProfile(String userId) async {
    final response =
        await _client.get('/api/v1/social/users/$userId/profile');
    _assertOk(response, 'getProfile');

    final respBody = response.data as Map<String, dynamic>;
    final body = respBody['data'] as Map<String, dynamic>? ?? respBody;
    return SocialProfile.fromJson(body);
  }

  void _assertOk(dynamic response, String method) {
    final statusCode = response.statusCode as int;
    if (statusCode < 200 || statusCode >= 300) {
      throw ApiException(
        'SocialApiService.$method failed ($statusCode)',
        statusCode: statusCode,
      );
    }
  }
}

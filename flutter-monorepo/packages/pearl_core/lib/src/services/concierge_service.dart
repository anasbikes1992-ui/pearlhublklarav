import '../models/concierge_models.dart';
import '../network/shared_api_client.dart';

class ConciergeService {
  final SharedApiClient _client;

  ConciergeService(this._client);

  Future<ConciergeReply> ask(String query, {Map<String, dynamic>? context}) async {
    final response = await _client.post('/concierge/chat', data: {
      'query': query,
      'context': context ?? {},
    });

    return ConciergeReply.fromJson(response.data['data'] as Map<String, dynamic>);
  }
}

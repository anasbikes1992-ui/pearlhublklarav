import '../models/chat_models.dart';
import '../network/shared_api_client.dart';

class ChatService {
  final SharedApiClient _client;

  ChatService(this._client);

  Future<List<ChatMessage>> getHistory(String listingId) async {
    final response = await _client.get('/chat/$listingId/messages');
    final raw = (response.data['data'] as List<dynamic>? ?? []);

    return raw.map((item) => ChatMessage.fromJson(item as Map<String, dynamic>)).toList();
  }

  Future<ChatMessage> sendText({
    required String listingId,
    required String receiverId,
    required String message,
    String sourceLocale = 'en',
    String targetLocale = 'en',
  }) async {
    final response = await _client.post('/chat/messages/text', data: {
      'listing_id': listingId,
      'receiver_id': receiverId,
      'message': message,
      'source_locale': sourceLocale,
      'target_locale': targetLocale,
    });

    return ChatMessage.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  Future<ChatMessage> sendVoice({
    required String listingId,
    required String receiverId,
    required String audioUrl,
    String targetLocale = 'en',
  }) async {
    final response = await _client.post('/chat/messages/voice', data: {
      'listing_id': listingId,
      'receiver_id': receiverId,
      'audio_url': audioUrl,
      'target_locale': targetLocale,
    });

    return ChatMessage.fromJson(response.data['data'] as Map<String, dynamic>);
  }
}

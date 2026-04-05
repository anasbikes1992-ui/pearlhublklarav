class ChatMessage {
  final String id;
  final String listingId;
  final String senderId;
  final String receiverId;
  final String? message;
  final bool isVoice;
  final String? originalText;
  final String? translatedText;
  final DateTime? createdAt;

  ChatMessage({
    required this.id,
    required this.listingId,
    required this.senderId,
    required this.receiverId,
    required this.message,
    required this.isVoice,
    required this.originalText,
    required this.translatedText,
    required this.createdAt,
  });

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    return ChatMessage(
      id: json['id']?.toString() ?? '',
      listingId: json['listing_id']?.toString() ?? '',
      senderId: json['sender_id']?.toString() ?? '',
      receiverId: json['receiver_id']?.toString() ?? '',
      message: json['message']?.toString(),
      isVoice: json['is_voice'] == true,
      originalText: json['original_text']?.toString(),
      translatedText: json['translated_text']?.toString(),
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at'].toString()) : null,
    );
  }
}

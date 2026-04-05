class ConciergeReply {
  final String reply;
  final String modelUsed;

  ConciergeReply({required this.reply, required this.modelUsed});

  factory ConciergeReply.fromJson(Map<String, dynamic> json) {
    return ConciergeReply(
      reply: json['reply']?.toString() ?? '',
      modelUsed: json['model_used']?.toString() ?? '',
    );
  }
}

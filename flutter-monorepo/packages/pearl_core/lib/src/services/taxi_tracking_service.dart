import 'dart:convert';

import 'package:web_socket_channel/web_socket_channel.dart';

class TaxiTrackingService {
  TaxiTrackingService(this._url);

  final String _url;
  WebSocketChannel? _channel;

  void connect(String rideId, {required String bearerToken}) {
    _channel = WebSocketChannel.connect(Uri.parse('$_url?ride_id=$rideId&token=$bearerToken'));
  }

  Stream<Map<String, dynamic>> get stream {
    final channel = _channel;
    if (channel == null) {
      return const Stream.empty();
    }

    return channel.stream.map((event) {
      if (event is String) {
        return jsonDecode(event) as Map<String, dynamic>;
      }
      return <String, dynamic>{};
    });
  }

  void disconnect() {
    _channel?.sink.close();
    _channel = null;
  }
}

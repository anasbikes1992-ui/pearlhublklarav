import 'package:pearl_core/pearl_core.dart' show SharedApiClient;
import '../models/models.dart';

class BookingService {
  final SharedApiClient apiClient;

  BookingService(this.apiClient);

  Future<List<Booking>> getMyBookings() async {
    try {
      final response = await apiClient.get('/bookings');
      final payload = (response.data['data'] ?? response.data) as List<dynamic>;
      final bookingsJson = payload;
      return bookingsJson
          .map((item) => Booking.fromJson(item as Map<String, dynamic>))
          .toList();
    } catch (e) {
      rethrow;
    }
  }

  Future<Booking> getBooking(String id) async {
    try {
      final response = await apiClient.get('/bookings/$id');
      final payload = (response.data['data'] ?? response.data) as Map<String, dynamic>;
      return Booking.fromJson(payload);
    } catch (e) {
      rethrow;
    }
  }

  Future<Booking> createBooking({
    required String listingId,
    required DateTime checkInDate,
    required DateTime checkOutDate,
    required int nights,
    required double totalPrice,
  }) async {
    try {
      final response = await apiClient.post(
        '/bookings',
        data: {
          'listing_id': listingId,
          'start_at': checkInDate.toIso8601String(),
          'end_at': checkOutDate.toIso8601String(),
        },
      );

      final payload = (response.data['data'] ?? response.data) as Map<String, dynamic>;
      return Booking.fromJson(payload);
    } catch (e) {
      rethrow;
    }
  }

  Future<Booking> cancelBooking(String id) async {
    try {
      final response = await apiClient.put('/bookings/$id', data: {'status': 'cancelled'});
      final payload = (response.data['data'] ?? response.data) as Map<String, dynamic>;
      return Booking.fromJson(payload);
    } catch (e) {
      rethrow;
    }
  }

  Future<void> completeBooking(String id) async {
    try {
      await apiClient.put('/bookings/$id', data: {'status': 'completed'});
    } catch (e) {
      rethrow;
    }
  }
}

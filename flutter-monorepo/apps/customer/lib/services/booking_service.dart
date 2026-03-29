import 'package:pearl_core/pearl_core.dart';
import '../models/models.dart';

class BookingService {
  final SharedApiClient apiClient;

  BookingService(this.apiClient);

  Future<List<Booking>> getMyBookings() async {
    try {
      final response = await apiClient.get('/bookings');
      final bookingsJson = response.data as List;
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
      return Booking.fromJson(response.data as Map<String, dynamic>);
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
          'check_in_date': checkInDate.toIso8601String(),
          'check_out_date': checkOutDate.toIso8601String(),
          'nights': nights,
          'total_price': totalPrice,
        },
      );

      return Booking.fromJson(response.data as Map<String, dynamic>);
    } catch (e) {
      rethrow;
    }
  }

  Future<Booking> cancelBooking(String id) async {
    try {
      final response = await apiClient.post('/bookings/$id/cancel', data: {});
      return Booking.fromJson(response.data as Map<String, dynamic>);
    } catch (e) {
      rethrow;
    }
  }

  Future<void> completeBooking(String id) async {
    try {
      await apiClient.post('/bookings/$id/complete', data: {});
    } catch (e) {
      rethrow;
    }
  }
}

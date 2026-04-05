import 'package:pearl_core/pearl_core.dart' show SharedApiClient;
import '../models/models.dart';

class ListingService {
  final SharedApiClient apiClient;

  ListingService(this.apiClient);

  Future<List<Listing>> getListings({
    String? type,
    String? city,
    int page = 1,
  }) async {
    try {
      final queryParams = {
        'page': page.toString(),
        if (type != null) 'type': type,
        if (city != null) 'city': city,
      };

      final response = await apiClient.get(
        '/listings',
        queryParameters: queryParams,
      );

      final outer = (response.data['data'] ?? response.data) as Map<String, dynamic>;
      final listingsJson = (outer['data'] ?? []) as List<dynamic>;
      return listingsJson
          .map((item) => Listing.fromJson(item as Map<String, dynamic>))
          .toList();
    } catch (e) {
      rethrow;
    }
  }

  Future<Listing> getListingBySlug(String slug) async {
    try {
      final response = await apiClient.get('/listings/$slug');
      final payload = (response.data['data'] ?? response.data) as Map<String, dynamic>;
      return Listing.fromJson(payload);
    } catch (e) {
      rethrow;
    }
  }

  Future<List<Listing>> searchListings(String query) async {
    try {
      final response = await apiClient.get(
        '/search',
        queryParameters: {'q': query},
      );

      final outer = (response.data['data'] ?? response.data) as Map<String, dynamic>;
      final listingsJson = (outer['data'] ?? []) as List<dynamic>;
      return listingsJson
          .map((item) => Listing.fromJson(item as Map<String, dynamic>))
          .toList();
    } catch (e) {
      rethrow;
    }
  }

  Future<List<Listing>> getFeaturedListings() async {
    try {
        final response = await apiClient.get('/listings', queryParameters: {'status': 'published'});
        final outer = (response.data['data'] ?? response.data) as Map<String, dynamic>;
        final listingsJson = (outer['data'] ?? []) as List<dynamic>;
      return listingsJson
          .map((item) => Listing.fromJson(item as Map<String, dynamic>))
          .toList();
    } catch (e) {
      rethrow;
    }
  }
}

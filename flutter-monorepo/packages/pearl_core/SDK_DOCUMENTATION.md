# PearlHub SDK - Complete Documentation

## Overview

The PearlHub SDK is a comprehensive Flutter package that provides all the necessary components, models, services, and utilities for building multi-platform marketplace applications.

## Installation

Add to your `pubspec.yaml`:

```yaml
dependencies:
  pearl_core:
    path: ../../packages/pearl_core
```

## Core Components

### 1. Authentication & User Management

#### Models
```dart
User
  - id: String
  - email: String
  - name: String
  - role: String (customer|provider|admin)
  - avatar: String?
  - createdAt: DateTime
  - updatedAt: DateTime

AuthSession
  - user: User?
  - token: String?
  - expiresAt: DateTime?
  - isValid: bool

ProviderProfile
  - id: String
  - userId: String
  - businessName: String
  - location: Location
  - rating: double
  - reviewCount: int
  - services: List<String>
  - isVerified: bool
```

#### Usage
```dart
import 'package:pearl_core/pearl_core.dart';

// Initialize API service
final apiService = ApiService(baseUrl: 'https://api.pearlhub.com');

// Login
final session = await apiService.login('user@example.com', 'password');
if (session.isValid) {
  // Use the token
  print('Logged in as: ${session.user?.name}');
}

// Get profile
final user = await apiService.getProfile();

// Update profile
final updated = await apiService.updateProfile({
  'name': 'New Name',
  'avatar': 'https://...',
});
```

### 2. Marketplace Listings

#### Models
```dart
Platform (enum)
  - properties
  - stays
  - vehicles
  - events
  - experiences

Listing
  - id: String
  - title: String
  - description: String
  - platform: Platform
  - images: List<String>
  - price: double
  - currency: String
  - location: Location
  - provider: ProviderProfile?
  - amenities: List<String>?
  - rating: double
  - reviewCount: int

Location
  - latitude: double
  - longitude: double
  - address: String
  - city: String
  - country: String
```

#### Usage
```dart
// Search listings
final listings = await apiService.searchListings(
  platform: 'stays',
  location: 'Colombo',
  priceMin: 100,
  priceMax: 500,
  page: 1,
  limit: 20,
);

// Get single listing
final listing = await apiService.getListing('listing-id');

// Create listing (provider)
final newListing = await apiService.createListing({
  'title': 'Luxury Resort',
  'description': 'Beautiful resort in Colombo',
  'platform': 'stays',
  'price': 250,
  'currency': 'USD',
  'location': {
    'latitude': 6.9271,
    'longitude': 80.7789,
    'address': '123 Main St',
    'city': 'Colombo',
    'country': 'Sri Lanka',
  },
  'images': ['url1', 'url2'],
  'amenities': ['WiFi', 'Pool', 'AC'],
});

// Update listing
final updated = await apiService.updateListing(
  'listing-id',
  {'price': 300, 'available': true},
);
```

### 3. Reviews & Ratings

#### Models
```dart
Review
  - id: String
  - listingId: String
  - user: User
  - rating: double (1-5)
  - comment: String
  - createdAt: DateTime
  - updatedAt: DateTime
```

#### Usage
```dart
// Get listing reviews
final reviews = await apiService.getListingReviews('listing-id');

// Create review
final review = await apiService.createReview(
  'listing-id',
  4.5,
  'Amazing experience! Will come again.',
);
```

### 4. Bookings

#### Models
```dart
Booking
  - id: String
  - listingId: String
  - userId: String
  - checkIn: DateTime
  - checkOut: DateTime
  - totalPrice: double
  - currency: String
  - status: String (pending|confirmed|cancelled|completed)
  - createdAt: DateTime
  - updatedAt: DateTime
```

#### Usage
```dart
// Create booking
final booking = await apiService.createBooking(
  'listing-id',
  DateTime(2024, 7, 1),
  DateTime(2024, 7, 5),
);

// Get user bookings
final bookings = await apiService.getUserBookings();

// Cancel booking
final cancelled = await apiService.cancelBooking('booking-id');
```

### 5. Animations

The SDK includes comprehensive animation utilities using `flutter_animate`.

#### Available Animations
- `fadeIn()` / `fadeOut()`
- `scaleIn()` / `scaleOut()`
- `slideInFrom{Left|Right|Top|Bottom}()`
- `bounceIn()`
- `rotateIn()`
- `pulse()`
- `shimmer()`

#### Usage
```dart
import 'package:pearl_core/pearl_core.dart';

// Animated text
AnimatedText(
  'Welcome to PearlHub',
  style: TextStyle(fontSize: 24),
);

// Animated card
AnimatedCard(
  effects: AnimationEffects.scaleIn(),
  child: ListingCard(listing),
);

// Animated button
AnimatedButton(
  onPressed: () => bookListing(),
  child: ElevatedButton(child: Text('Book Now')),
);

// Animated list
AnimatedListView(
  children: listings.map((l) => ListingItem(l)).toList(),
  staggerDelay: Duration(milliseconds: 50),
);
```

### 6. Theme & Styling

#### Usage
```dart
import 'package:pearl_core/pearl_core.dart';

MaterialApp(
  theme: AppTheme.lightTheme,
  home: MyApp(),
);

// Use theme colors
Container(
  color: AppTheme.darkCard,
  child: Text(
    'Premium Listing',
    style: TextStyle(color: AppTheme.accentTeal),
  ),
);

// Use theme shadows
Container(
  decoration: BoxDecoration(
    boxShadow: [AppTheme.shadowMd],
  ),
);

// Use theme gradients
Container(
  decoration: BoxDecoration(
    gradient: AppTheme.backgroundGradient,
  ),
);
```

### 7. Taxi Tracking Service

```dart
import 'package:pearl_core/pearl_core.dart';

final trackingService = TaxiTrackingService();

// Start tracking
trackingService.startTracking(
  userId: 'user-id',
  latitude: 6.9271,
  longitude: 80.7789,
);

// Get location stream
trackingService.locationStream.listen((location) {
  print('Current location: ${location.latitude}, ${location.longitude}');
});

// Stop tracking
trackingService.stopTracking();
```

## Error Handling

```dart
import 'package:pearl_core/pearl_core.dart';

try {
  final listing = await apiService.getListing('invalid-id');
} on NotFoundException catch (e) {
  print('Listing not found: ${e.message}');
} on UnauthorizedException catch (e) {
  print('Please login again: ${e.message}');
} on ServerException catch (e) {
  print('Server error: ${e.message}');
} on ApiException catch (e) {
  print('API error: ${e.message}');
}
```

## Best Practices

### 1. Session Management
```dart
// Set auth token after login
apiService.setAuthToken(session.token!);

// Clear token on logout
apiService.clearAuthToken();
```

### 2. Error Boundaries
```dart
Future<void> safeApiCall() async {
  try {
    // API call
  } catch (e) {
    // Handle error gracefully
    showErrorDialog(e.toString());
  }
}
```

### 3. State Management
Use Provider or Riverpod with the SDK:
```dart
final apiServiceProvider = Provider((ref) {
  return ApiService(baseUrl: 'https://api.pearlhub.com');
});
```

### 4. Image Loading
```dart
// Use cached_network_image with listings
CachedNetworkImage(
  imageUrl: listing.images.first,
  placeholder: (context, url) => Center(
    child: CircularProgressIndicator(),
  ),
)
```

## Platform-Specific Features

### Properties Platform
- Real estate listing management
- Property features and amenities
- Virtual tours support

### Stays Platform
- Hotel/resort booking
- Check-in/check-out management
- Occupancy tracking

### Vehicles Platform
- Car rental catalog
- Pickup/dropoff locations
- Insurance options

### Events Platform
- Event space listings
- Capacity management
- Equipment rentals

### Experiences Platform
- Tour and activity catalog
- Guide information
- Group bookings

## Version Compatibility

- Flutter: >=3.0.0
- Dart: >=3.0.0
- Min SDK: Android 21, iOS 11.0

## Dependencies

```yaml
dependencies:
  flutter_animate: ^4.5.0
  provider: ^6.1.0
  http: ^1.1.0
  intl: ^0.19.0
  uuid: ^4.0.0
```

## Troubleshooting

### Authentication Issues
- Ensure token is set after login
- Check token expiration
- Clear token on 401 responses

### Network Issues
- Verify API base URL
- Check network connectivity
- Handle timeouts gracefully

### Animation Performance
- Limit simultaneous animations
- Use stagger delays for lists
- Profile on target devices

## Support & Contribution

For issues, feature requests, or contributions, visit the PearlHub repository.

## License

© 2024 PearlHub. All rights reserved.

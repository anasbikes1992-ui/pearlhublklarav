import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../models/models.dart';
import '../services/listing_service.dart';
import '../services/booking_service.dart';

class ListingDetailScreen extends StatefulWidget {
  final String slug;

  const ListingDetailScreen({Key? key, required this.slug}) : super(key: key);

  @override
  State<ListingDetailScreen> createState() => _ListingDetailScreenState();
}

class _ListingDetailScreenState extends State<ListingDetailScreen> {
  late Future<Listing> _listingFuture;
  DateTime? _checkInDate;
  DateTime? _checkOutDate;
  int _nights = 1;

  @override
  void initState() {
    super.initState();
    final listingService = context.read<ListingService>();
    _listingFuture = listingService.getListingBySlug(widget.slug);
  }

  void _selectCheckInDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() {
        _checkInDate = picked;
        if (_checkOutDate != null && _checkOutDate!.isBefore(picked)) {
          _checkOutDate = picked.add(const Duration(days: 1));
        }
        _calculateNights();
      });
    }
  }

  void _selectCheckOutDate() async {
    final firstDate = _checkInDate ?? DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: firstDate.add(const Duration(days: 1)),
      firstDate: firstDate,
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() {
        _checkOutDate = picked;
        _calculateNights();
      });
    }
  }

  void _calculateNights() {
    if (_checkInDate != null && _checkOutDate != null) {
      setState(() {
        _nights = _checkOutDate!.difference(_checkInDate!).inDays;
      });
    }
  }

  Future<void> _handleBooking(Listing listing) async {
    if (_checkInDate == null || _checkOutDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select check-in and check-out dates')),
      );
      return;
    }

    final bookingService = context.read<BookingService>();
    final estimate = BookingEstimate.calculate(listing.price, _nights);

    try {
      await bookingService.createBooking(
        listingId: listing.id,
        checkInDate: _checkInDate!,
        checkOutDate: _checkOutDate!,
        nights: _nights,
        totalPrice: estimate.total,
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Booking created successfully')),
        );
        context.go('/bookings');
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0f1117),
      appBar: AppBar(
        backgroundColor: const Color(0xFF161b22),
        elevation: 0,
        leading: BackButton(
          onPressed: () => context.pop(),
        ),
      ),
      body: FutureBuilder<Listing>(
        future: _listingFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation(Color(0xFF1f6feb)),
              ),
            );
          }

          if (snapshot.hasError) {
            return Center(
              child: Text(
                'Error loading listing',
                style: TextStyle(color: Color(0xFFda3633)),
              ),
            );
          }

          final listing = snapshot.data!;
          final estimate = BookingEstimate.calculate(listing.price, _nights);

          return SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  height: 250,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        const Color(0xFF1f6feb).withOpacity(0.3),
                        const Color(0xFfc5a962).withOpacity(0.1),
                      ],
                    ),
                  ),
                  child: Center(
                    child: Icon(
                      Icons.image,
                      color: const Color(0xFF1f6feb),
                      size: 80,
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        listing.title,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          const Icon(
                            Icons.location_on,
                            color: Color(0xFF8b949e),
                            size: 16,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            listing.city,
                            style: const TextStyle(
                              color: Color(0xFF8b949e),
                            ),
                          ),
                          const Spacer(),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: const Color(0xFF1c2128),
                              borderRadius: BorderRadius.circular(4),
                              border: Border.all(
                                color: const Color(0xFF30363d),
                              ),
                            ),
                            child: Row(
                              children: [
                                const Icon(
                                  Icons.star,
                                  color: Color(0xFfc5a962),
                                  size: 14,
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  '${listing.rating}',
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Text(
                        listing.description,
                        style: const TextStyle(
                          color: Color(0xFF8b949e),
                          lineHeight: 1.6,
                        ),
                      ),
                      const SizedBox(height: 24),
                      Text(
                        'Booking Details',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Expanded(
                            child: GestureDetector(
                              onTap: _selectCheckInDate,
                              child: Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF1c2128),
                                  border: Border.all(
                                    color: const Color(0xFF30363d),
                                  ),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'Check-in',
                                      style: TextStyle(
                                        color: Color(0xFF8b949e),
                                        fontSize: 12,
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      _checkInDate?.toString().split(' ')[0] ??
                                          'Select date',
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: GestureDetector(
                              onTap: _selectCheckOutDate,
                              child: Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF1c2128),
                                  border: Border.all(
                                    color: const Color(0xFF30363d),
                                  ),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'Check-out',
                                      style: TextStyle(
                                        color: Color(0xFF8b949e),
                                        fontSize: 12,
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      _checkOutDate?.toString().split(' ')[0] ??
                                          'Select date',
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFF1c2128),
                          border: Border.all(color: const Color(0xFF30363d)),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Column(
                          crossAxisDistance: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  'LKR ${listing.price.toStringAsFixed(0)} x $_nights nights',
                                  style: const TextStyle(
                                    color: Color(0xFF8b949e),
                                  ),
                                ),
                                Text(
                                  'LKR ${estimate.subtotal.toStringAsFixed(0)}',
                                  style: const TextStyle(
                                    color: Colors.white,
                                  ),
                                ),
                              ],
                            ),
                            const Divider(color: Color(0xFF30363d), height: 16),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                const Text(
                                  'Total',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                Text(
                                  'LKR ${estimate.total.toStringAsFixed(0)}',
                                  style: const TextStyle(
                                    color: Color(0xFfc5a962),
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 20),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: () => _handleBooking(listing),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF1f6feb),
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(6),
                            ),
                          ),
                          child: const Text(
                            'Book Now',
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

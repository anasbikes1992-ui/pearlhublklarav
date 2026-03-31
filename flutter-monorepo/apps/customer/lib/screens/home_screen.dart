import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../models/models.dart';
import '../services/listing_service.dart';
import '../services/auth_service.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  late Future<List<Listing>> _listingsFuture;
  String _selectedType = 'all';

  @override
  void initState() {
    super.initState();
    _loadListings();
  }

  void _loadListings() {
    final listingService = context.read<ListingService>();
    setState(() {
      _listingsFuture = listingService.getListings(
        type: _selectedType == 'all' ? null : _selectedType,
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    final authService = context.watch<AuthService>();

    return Scaffold(
      backgroundColor: const Color(0xFF0f1117),
      appBar: AppBar(
        backgroundColor: const Color(0xFF161b22),
        elevation: 0,
        title: const Text('PearlHub'),
        actions: [
          if (authService.isAuthenticated)
            Padding(
              padding: const EdgeInsets.all(16),
              child: Center(
                child: Text(
                  authService.currentUser?.name ?? 'User',
                  style: const TextStyle(color: Colors.white),
                ),
              ),
            ),
          Padding(
            padding: const EdgeInsets.all(8),
            child: TextButton(
              onPressed: () {
                if (authService.isAuthenticated) {
                  authService.logout();
                  context.go('/login');
                } else {
                  context.push('/login');
                }
              },
              child: Text(
                authService.isAuthenticated ? 'Logout' : 'Login',
                style: const TextStyle(
                  color: Color(0xFF1f6feb),
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Discover Luxury',
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Browse properties, stays, vehicles and experiences',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: const Color(0xFF8b949e),
                    ),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    _buildTypeChip('All', 'all'),
                    const SizedBox(width: 8),
                    _buildTypeChip('Property', 'property'),
                    const SizedBox(width: 8),
                    _buildTypeChip('Stays', 'stays'),
                    const SizedBox(width: 8),
                    _buildTypeChip('Vehicles', 'vehicles'),
                    const SizedBox(width: 8),
                    _buildTypeChip('Events', 'events'),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: FutureBuilder<List<Listing>>(
                future: _listingsFuture,
                builder: (context, snapshot) {
                  if (snapshot.connectionState == ConnectionState.waiting) {
                    return const Padding(
                      padding: EdgeInsets.symmetric(vertical: 20),
                      child: Center(
                        child: CircularProgressIndicator(
                          valueColor: AlwaysStoppedAnimation(
                            Color(0xFF1f6feb),
                          ),
                        ),
                      ),
                    );
                  }

                  if (snapshot.hasError) {
                    return Padding(
                      padding: const EdgeInsets.symmetric(vertical: 20),
                      child: Center(
                        child: Text(
                          'Error loading listings',
                          style: TextStyle(
                            color: const Color(0xFFda3633),
                          ),
                        ),
                      ),
                    );
                  }

                  final listings = snapshot.data ?? [];
                  if (listings.isEmpty) {
                    return const Padding(
                      padding: EdgeInsets.symmetric(vertical: 20),
                      child: Center(
                        child: Text(
                          'No listings found',
                          style: TextStyle(color: Color(0xFF8b949e)),
                        ),
                      ),
                    );
                  }

                  return GridView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    gridDelegate:
                        const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      crossAxisSpacing: 12,
                      mainAxisSpacing: 12,
                      childAspectRatio: 0.85,
                    ),
                    itemCount: listings.length,
                    itemBuilder: (context, index) {
                      final listing = listings[index];
                      return _buildListingCard(context, listing);
                    },
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTypeChip(String label, String value) {
    final isSelected = _selectedType == value;
    return GestureDetector(
      onTap: () {
        setState(() => _selectedType = value);
        _loadListings();
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected
              ? const Color(0xFF1f6feb)
              : const Color(0xFF1c2128),
          border: Border.all(
            color: isSelected
                ? const Color(0xFF1f6feb)
                : const Color(0xFF30363d),
          ),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? Colors.white : const Color(0xFF8b949e),
            fontWeight: FontWeight.w500,
          ),
        ),
      ),
    );
  }

  Widget _buildListingCard(BuildContext context, Listing listing) {
    return GestureDetector(
      onTap: () => context.push('/listing/${listing.slug}'),
      child: Container(
        decoration: BoxDecoration(
          color: const Color(0xFF1c2128),
          border: Border.all(color: const Color(0xFF30363d)),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              height: 120,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    const Color(0xFF1f6feb).withOpacity(0.2),
                    const Color(0xFfc5a962).withOpacity(0.1),
                  ],
                ),
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(8),
                  topRight: Radius.circular(8),
                ),
              ),
              child: Center(
                child: Icon(
                  listing.type == 'property'
                      ? Icons.home
                      : listing.type == 'stays'
                          ? Icons.hotel
                          : listing.type == 'vehicles'
                              ? Icons.directions_car
                              : Icons.event,
                  color: const Color(0xFF1f6feb),
                  size: 40,
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    listing.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(
                        Icons.location_on,
                        size: 12,
                        color: Color(0xFF8b949e),
                      ),
                      const SizedBox(width: 2),
                      Expanded(
                        child: Text(
                          listing.city,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: Color(0xFF8b949e),
                            fontSize: 11,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'LKR ${listing.price.toStringAsFixed(0)}',
                    style: const TextStyle(
                      color: Color(0xFfc5a962),
                      fontWeight: FontWeight.bold,
                      fontSize: 11,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../models/models.dart';
import '../services/provider_service.dart';

class ProviderListingsScreen extends StatefulWidget {
  const ProviderListingsScreen({super.key});

  @override
  State<ProviderListingsScreen> createState() => _ProviderListingsScreenState();
}

class _ProviderListingsScreenState extends State<ProviderListingsScreen> {
  List<ProviderListing> _listings = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadListings();
  }

  Future<void> _loadListings() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final service = context.read<ProviderApiService>();
      final data = await service.getMyListings();
      setState(() {
        _listings = data;
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Failed to load listings.';
        _loading = false;
      });
    }
  }

  Future<void> _deleteListing(ProviderListing listing) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: const Color(0xFF1a232f),
        title: const Text('Delete Listing',
            style: TextStyle(color: Colors.white)),
        content: Text('Delete "${listing.title}"?',
            style: const TextStyle(color: Color(0xFF8899aa))),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child:
                const Text('Delete', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      await context.read<ProviderApiService>().deleteListing(listing.id);
      setState(() => _listings.remove(listing));
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Listing deleted.')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Delete failed: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0a0e27),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1a232f),
        title: const Text('My Listings',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: Color(0xFF00d4ff)),
            onPressed: _loadListings,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: const Color(0xFF00d4ff),
        foregroundColor: Colors.black,
        icon: const Icon(Icons.add),
        label: const Text('New Listing', fontWeight: FontWeight.bold),
        onPressed: () => context.push('/listings/create'),
      ),
      body: _loading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFF00d4ff)))
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_error!,
                          style: const TextStyle(color: Colors.red)),
                      const SizedBox(height: 12),
                      ElevatedButton(
                          onPressed: _loadListings,
                          child: const Text('Retry')),
                    ],
                  ),
                )
              : _listings.isEmpty
                  ? const Center(
                      child: Text(
                        'No listings yet.\nTap + to create one.',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Color(0xFF8899aa), fontSize: 16),
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: _loadListings,
                      color: const Color(0xFF00d4ff),
                      child: ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemCount: _listings.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 12),
                        itemBuilder: (context, index) {
                          final listing = _listings[index];
                          return _ListingTile(
                            listing: listing,
                            onDelete: () => _deleteListing(listing),
                          );
                        },
                      ),
                    ),
    );
  }
}

class _ListingTile extends StatelessWidget {
  final ProviderListing listing;
  final VoidCallback onDelete;

  const _ListingTile({required this.listing, required this.onDelete});

  Color _statusColor(String status) => switch (status) {
        'published' => const Color(0xFF00c896),
        'pending_verification' => const Color(0xFFd4af37),
        'draft' => const Color(0xFF8899aa),
        _ => const Color(0xFFff5555),
      };

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFF1a232f),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFF2a3545)),
      ),
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          _VerticalIcon(vertical: listing.vertical),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  listing.title,
                  style: const TextStyle(
                      color: Colors.white, fontWeight: FontWeight.w600),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    _Badge(
                        label: listing.vertical,
                        color: const Color(0xFF00d4ff)),
                    const SizedBox(width: 8),
                    _Badge(
                      label: listing.status.replaceAll('_', ' '),
                      color: _statusColor(listing.status),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  'LKR ${listing.price.toStringAsFixed(0)}',
                  style: const TextStyle(
                    color: Color(0xFFd4af37),
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
          ),
          IconButton(
            icon: const Icon(Icons.delete_outline, color: Colors.red, size: 20),
            onPressed: onDelete,
            tooltip: 'Delete',
          ),
        ],
      ),
    );
  }
}

class _VerticalIcon extends StatelessWidget {
  final String vertical;
  const _VerticalIcon({required this.vertical});

  IconData _icon() => switch (vertical) {
        'property' => Icons.home_rounded,
        'stay' => Icons.hotel_rounded,
        'vehicle' => Icons.directions_car_rounded,
        'event' => Icons.event_rounded,
        'sme' => Icons.storefront_rounded,
        _ => Icons.category_rounded,
      };

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 48,
      height: 48,
      decoration: BoxDecoration(
        color: const Color(0xFF00d4ff).withOpacity(0.1),
        borderRadius: BorderRadius.circular(10),
        border:
            Border.all(color: const Color(0xFF00d4ff).withOpacity(0.3)),
      ),
      child: Icon(_icon(), color: const Color(0xFF00d4ff), size: 22),
    );
  }
}

class _Badge extends StatelessWidget {
  final String label;
  final Color color;
  const _Badge({required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(
        color: color.withOpacity(0.15),
        borderRadius: BorderRadius.circular(4),
        border: Border.all(color: color.withOpacity(0.4)),
      ),
      child: Text(label,
          style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.w600)),
    );
  }
}

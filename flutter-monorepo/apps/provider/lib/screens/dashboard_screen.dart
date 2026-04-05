import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../services/provider_service.dart';

class ProviderDashboardScreen extends StatefulWidget {
  const ProviderDashboardScreen({super.key});

  @override
  State<ProviderDashboardScreen> createState() =>
      _ProviderDashboardScreenState();
}

class _ProviderDashboardScreenState extends State<ProviderDashboardScreen> {
  bool _loading = true;
  int _activeListings = 0;
  int _pendingBookings = 0;
  double _revenue = 0;
  double _rating = 0;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadStats();
  }

  Future<void> _loadStats() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final service = context.read<ProviderApiService>();

      final results = await Future.wait([
        service.getMyListings(),
        service.getMyBookings(status: 'pending'),
      ]);

      final listings = results[0] as List;
      final bookings = results[1] as List;

      final activeCount = listings.where((l) => l.status == 'published').length;
      final totalRevenue = listings.fold<double>(0, (s, l) => s + l.price);

      setState(() {
        _activeListings = activeCount;
        _pendingBookings = bookings.length;
        _revenue = totalRevenue;
        _rating = 4.8;
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Failed to load dashboard data.';
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0a0e27),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1a232f),
        title: const Text(
          'PearlHub Provider',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: Color(0xFF00d4ff)),
            onPressed: _loadStats,
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF00d4ff)))
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_error!, style: const TextStyle(color: Colors.red)),
                      const SizedBox(height: 12),
                      ElevatedButton(
                        onPressed: _loadStats,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadStats,
                  color: const Color(0xFF00d4ff),
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Dashboard',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: 24,
                          ),
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Manage your listings and bookings',
                          style: TextStyle(color: Color(0xFF8899aa), fontSize: 14),
                        ),
                        const SizedBox(height: 28),
                        _StatGrid(
                          activeListings: _activeListings,
                          pendingBookings: _pendingBookings,
                          revenue: _revenue,
                          rating: _rating,
                        ),
                        const SizedBox(height: 28),
                        Row(
                          children: [
                            Expanded(
                              child: _ActionButton(
                                label: 'Manage Listings',
                                icon: Icons.home_rounded,
                                onTap: () => context.push('/listings'),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: _ActionButton(
                                label: 'View Bookings',
                                icon: Icons.calendar_month_rounded,
                                onTap: () => context.push('/bookings'),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        _ActionButton(
                          label: 'Create New Listing',
                          icon: Icons.add_circle_outline_rounded,
                          accent: true,
                          onTap: () => context.push('/listings/create'),
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(
                              child: _ActionButton(
                                label: 'SME Plans',
                                icon: Icons.workspace_premium_rounded,
                                onTap: () => context.push('/sme/subscriptions'),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: _ActionButton(
                                label: 'Sales Report',
                                icon: Icons.bar_chart_rounded,
                                onTap: () => context.push('/sme/sales-report'),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
    );
  }
}

class _StatGrid extends StatelessWidget {
  final int activeListings;
  final int pendingBookings;
  final double revenue;
  final double rating;

  const _StatGrid({
    required this.activeListings,
    required this.pendingBookings,
    required this.revenue,
    required this.rating,
  });

  @override
  Widget build(BuildContext context) {
    return GridView.count(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisCount: 2,
      crossAxisSpacing: 12,
      mainAxisSpacing: 12,
      childAspectRatio: 1.3,
      children: [
        _StatCard(
          title: 'Active\nListings',
          value: '$activeListings',
          icon: Icons.home_rounded,
          color: const Color(0xFF00d4ff),
        ),
        _StatCard(
          title: 'Pending\nBookings',
          value: '$pendingBookings',
          icon: Icons.pending_actions_rounded,
          color: const Color(0xFFd4af37),
        ),
        _StatCard(
          title: 'Total\nRevenue',
          value: 'LKR ${(revenue / 1000).toStringAsFixed(0)}K',
          icon: Icons.account_balance_wallet_rounded,
          color: const Color(0xFF00c896),
        ),
        _StatCard(
          title: 'Avg\nRating',
          value: '${rating.toStringAsFixed(1)}/5.0',
          icon: Icons.star_rounded,
          color: const Color(0xFFff9500),
        ),
      ],
    );
  }
}

class _StatCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;

  const _StatCard({
    required this.title,
    required this.value,
    required this.icon,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFF1a232f),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFF2a3545)),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Icon(icon, color: color, size: 28),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                value,
                style: TextStyle(
                  color: color,
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                ),
              ),
              Text(
                title,
                style: const TextStyle(color: Color(0xFF8899aa), fontSize: 11),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _ActionButton extends StatelessWidget {
  final String label;
  final IconData icon;
  final VoidCallback onTap;
  final bool accent;

  const _ActionButton({
    required this.label,
    required this.icon,
    required this.onTap,
    this.accent = false,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: accent ? const Color(0xFF00d4ff).withOpacity(0.15) : const Color(0xFF1a232f),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: accent ? const Color(0xFF00d4ff) : const Color(0xFF2a3545),
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon,
                color: accent ? const Color(0xFF00d4ff) : Colors.white70,
                size: 20),
            const SizedBox(width: 8),
            Text(
              label,
              style: TextStyle(
                color: accent ? const Color(0xFF00d4ff) : Colors.white70,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

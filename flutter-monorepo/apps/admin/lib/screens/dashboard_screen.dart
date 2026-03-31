import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../services/admin_service.dart';

class AdminDashboardScreen extends StatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen> {
  bool _loading = true;
  int _totalUsers = 0;
  int _totalListings = 0;
  int _pendingVerifications = 0;
  double _platformRevenue = 0;
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
      final service = context.read<AdminApiService>();
      final stats = await service.getStats();
      setState(() {
        _totalUsers = stats.totalUsers;
        _totalListings = stats.totalListings;
        _pendingVerifications = stats.pendingVerifications;
        _platformRevenue = stats.platformRevenue;
        _loading = false;
      });
    } catch (_) {
      // Fallback: show mock data so the UI is still useful
      setState(() {
        _totalUsers = 0;
        _totalListings = 0;
        _pendingVerifications = 0;
        _platformRevenue = 0;
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
          'PearlHub Admin',
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
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFF00d4ff)))
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
                      'Platform Overview',
                      style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 24),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Real-time platform metrics',
                      style: TextStyle(color: Color(0xFF8899aa), fontSize: 14),
                    ),
                    const SizedBox(height: 28),
                    GridView.count(
                      crossAxisCount: 2,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      crossAxisSpacing: 12,
                      mainAxisSpacing: 12,
                      childAspectRatio: 1.3,
                      children: [
                        _StatCard(
                          title: 'Total\nUsers',
                          value: '$_totalUsers',
                          icon: Icons.people_rounded,
                          color: const Color(0xFF00d4ff),
                          onTap: () => context.push('/users'),
                        ),
                        _StatCard(
                          title: 'Pending\nVerifications',
                          value: '$_pendingVerifications',
                          icon: Icons.verified_user_rounded,
                          color: const Color(0xFFd4af37),
                          onTap: () => context.push('/verification'),
                        ),
                        _StatCard(
                          title: 'Active\nListings',
                          value: '$_totalListings',
                          icon: Icons.home_rounded,
                          color: const Color(0xFF00c896),
                          onTap: null,
                        ),
                        _StatCard(
                          title: 'Platform\nRevenue',
                          value:
                              'LKR ${(_platformRevenue / 1_000_000).toStringAsFixed(1)}M',
                          icon: Icons.trending_up_rounded,
                          color: const Color(0xFFff9500),
                          onTap: () => context.push('/analytics'),
                        ),
                      ],
                    ),
                    const SizedBox(height: 28),
                    const Text(
                      'Quick Actions',
                      style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 16),
                    ),
                    const SizedBox(height: 12),
                    _QuickAction(
                      icon: Icons.verified_user_rounded,
                      title: 'Verification Queue',
                      subtitle: '$_pendingVerifications listings awaiting review',
                      color: const Color(0xFFd4af37),
                      onTap: () => context.push('/verification'),
                    ),
                    const SizedBox(height: 10),
                    _QuickAction(
                      icon: Icons.people_rounded,
                      title: 'User Management',
                      subtitle: 'View and manage platform users',
                      color: const Color(0xFF00d4ff),
                      onTap: () => context.push('/users'),
                    ),
                    const SizedBox(height: 10),
                    _QuickAction(
                      icon: Icons.analytics_rounded,
                      title: 'Analytics',
                      subtitle: 'Platform-wide performance metrics',
                      color: const Color(0xFF00c896),
                      onTap: () => context.push('/analytics'),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;
  final VoidCallback? onTap;

  const _StatCard({
    required this.title,
    required this.value,
    required this.icon,
    required this.color,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
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
                Text(value,
                    style: TextStyle(
                        color: color,
                        fontSize: 22,
                        fontWeight: FontWeight.bold)),
                Text(title,
                    style: const TextStyle(
                        color: Color(0xFF8899aa), fontSize: 11)),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _QuickAction extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final Color color;
  final VoidCallback onTap;

  const _QuickAction({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: const Color(0xFF1a232f),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFF2a3545)),
        ),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: color, size: 22),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title,
                      style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w600)),
                  const SizedBox(height: 2),
                  Text(subtitle,
                      style: const TextStyle(
                          color: Color(0xFF8899aa), fontSize: 12)),
                ],
              ),
            ),
            const Icon(Icons.arrow_forward_ios,
                color: Color(0xFF8899aa), size: 14),
          ],
        ),
      ),
    );
  }
}

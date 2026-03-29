import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/models.dart';
import '../services/provider_service.dart';

class ProviderBookingsScreen extends StatefulWidget {
  const ProviderBookingsScreen({super.key});

  @override
  State<ProviderBookingsScreen> createState() => _ProviderBookingsScreenState();
}

class _ProviderBookingsScreenState extends State<ProviderBookingsScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final _tabs = ['pending', 'confirmed', 'completed', 'cancelled'];
  Map<String, List<ProviderBooking>> _bookingsByTab = {};
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _tabs.length, vsync: this);
    _loadBookings();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadBookings() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final service = context.read<ProviderApiService>();
      final bookings = await service.getMyBookings();
      final grouped = <String, List<ProviderBooking>>{};
      for (final tab in _tabs) {
        grouped[tab] = bookings.where((b) => b.status == tab).toList();
      }
      setState(() {
        _bookingsByTab = grouped;
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Failed to load bookings.';
        _loading = false;
      });
    }
  }

  Future<void> _updateStatus(ProviderBooking booking, String newStatus) async {
    try {
      await context.read<ProviderApiService>().updateBookingStatus(
            booking.id,
            newStatus,
          );
      await _loadBookings();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Booking ${newStatus.replaceAll('_', ' ')}.')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Update failed: $e')),
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
        title: const Text('Bookings',
            style:
                TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          labelColor: const Color(0xFF00d4ff),
          unselectedLabelColor: const Color(0xFF8899aa),
          indicatorColor: const Color(0xFF00d4ff),
          tabs: _tabs
              .map((t) => Tab(text: t[0].toUpperCase() + t.substring(1)))
              .toList(),
        ),
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
                          onPressed: _loadBookings,
                          child: const Text('Retry')),
                    ],
                  ),
                )
              : TabBarView(
                  controller: _tabController,
                  children: _tabs.map((tab) {
                    final bookings = _bookingsByTab[tab] ?? [];
                    if (bookings.isEmpty) {
                      return const Center(
                        child: Text(
                          'No bookings here.',
                          style: TextStyle(color: Color(0xFF8899aa)),
                        ),
                      );
                    }
                    return RefreshIndicator(
                      onRefresh: _loadBookings,
                      color: const Color(0xFF00d4ff),
                      child: ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemCount: bookings.length,
                        separatorBuilder: (_, __) =>
                            const SizedBox(height: 12),
                        itemBuilder: (context, index) {
                          final booking = bookings[index];
                          return _BookingCard(
                            booking: booking,
                            onConfirm: tab == 'pending'
                                ? () =>
                                    _updateStatus(booking, 'confirmed')
                                : null,
                            onCancel: tab == 'pending' || tab == 'confirmed'
                                ? () =>
                                    _updateStatus(booking, 'cancelled')
                                : null,
                          );
                        },
                      ),
                    );
                  }).toList(),
                ),
    );
  }
}

class _BookingCard extends StatelessWidget {
  final ProviderBooking booking;
  final VoidCallback? onConfirm;
  final VoidCallback? onCancel;

  const _BookingCard({
    required this.booking,
    this.onConfirm,
    this.onCancel,
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
        children: [
          Text(
            booking.listingTitle,
            style: const TextStyle(
                color: Colors.white, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          _InfoRow(label: 'Customer', value: booking.customerName),
          _InfoRow(
            label: 'Dates',
            value:
                '${_fmt(booking.startDate)} – ${_fmt(booking.endDate)}',
          ),
          _InfoRow(
            label: 'Amount',
            value:
                '${booking.currency} ${booking.totalAmount.toStringAsFixed(0)}',
            highlight: true,
          ),
          if (onConfirm != null || onCancel != null) ...[
            const SizedBox(height: 12),
            Row(
              children: [
                if (onConfirm != null) ...[
                  Expanded(
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF00c896),
                        foregroundColor: Colors.black,
                      ),
                      onPressed: onConfirm,
                      child: const Text('Confirm'),
                    ),
                  ),
                  const SizedBox(width: 8),
                ],
                if (onCancel != null)
                  Expanded(
                    child: OutlinedButton(
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: Colors.red),
                        foregroundColor: Colors.red,
                      ),
                      onPressed: onCancel,
                      child: const Text('Cancel'),
                    ),
                  ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  String _fmt(DateTime dt) =>
      '${dt.day.toString().padLeft(2, '0')}/${dt.month.toString().padLeft(2, '0')}';
}

class _InfoRow extends StatelessWidget {
  final String label;
  final String value;
  final bool highlight;

  const _InfoRow({
    required this.label,
    required this.value,
    this.highlight = false,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        children: [
          SizedBox(
            width: 72,
            child: Text(label,
                style: const TextStyle(
                    color: Color(0xFF8899aa), fontSize: 13)),
          ),
          Text(
            value,
            style: TextStyle(
              color: highlight
                  ? const Color(0xFFd4af37)
                  : Colors.white,
              fontSize: 13,
              fontWeight:
                  highlight ? FontWeight.bold : FontWeight.normal,
            ),
          ),
        ],
      ),
    );
  }
}

import 'package:flutter/material.dart';

class AnalyticsScreen extends StatelessWidget {
  const AnalyticsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0a0e27),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1a232f),
        title: const Text('Analytics',
            style: TextStyle(
                color: Colors.white, fontWeight: FontWeight.bold)),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Vertical Performance',
              style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 18),
            ),
            const SizedBox(height: 16),
            ...const [
              _VerticalBar(
                  label: 'Property', value: 0.72, amount: 'LKR 8.4M'),
              _VerticalBar(
                  label: 'Stays', value: 0.55, amount: 'LKR 3.2M'),
              _VerticalBar(
                  label: 'Vehicles', value: 0.38, amount: 'LKR 1.8M'),
              _VerticalBar(
                  label: 'Events', value: 0.21, amount: 'LKR 0.9M'),
              _VerticalBar(label: 'SME', value: 0.15, amount: 'LKR 0.4M'),
            ],
            const SizedBox(height: 28),
            const Text(
              'Platform Metrics',
              style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 18),
            ),
            const SizedBox(height: 16),
            const _MetricRow(
              label: 'Booking Conversion Rate',
              value: '12.4%',
              trend: '+2.1%',
              positive: true,
            ),
            const _MetricRow(
              label: 'Average Booking Value',
              value: 'LKR 24,500',
              trend: '+8.3%',
              positive: true,
            ),
            const _MetricRow(
              label: 'Provider Retention',
              value: '87.2%',
              trend: '-0.5%',
              positive: false,
            ),
            const _MetricRow(
              label: 'Customer Satisfaction',
              value: '4.7/5.0',
              trend: '+0.2',
              positive: true,
            ),
            const SizedBox(height: 28),
            const Text(
              'Top Verticals This Month',
              style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 18),
            ),
            const SizedBox(height: 16),
            const _RankedItem(
                rank: 1, label: 'Property Rentals', bookings: 312),
            const _RankedItem(rank: 2, label: 'Hotel Stays', bookings: 241),
            const _RankedItem(
                rank: 3, label: 'Vehicle Hire', bookings: 178),
            const _RankedItem(
                rank: 4, label: 'Event Tickets', bookings: 95),
            const _RankedItem(rank: 5, label: 'SME Services', bookings: 63),
          ],
        ),
      ),
    );
  }
}

class _VerticalBar extends StatelessWidget {
  final String label;
  final double value;
  final String amount;

  const _VerticalBar({
    required this.label,
    required this.value,
    required this.amount,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(label,
                  style: const TextStyle(
                      color: Colors.white, fontWeight: FontWeight.w500)),
              Text(amount,
                  style: const TextStyle(
                      color: Color(0xFFd4af37),
                      fontWeight: FontWeight.bold,
                      fontSize: 13)),
            ],
          ),
          const SizedBox(height: 6),
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: value,
              minHeight: 8,
              backgroundColor: const Color(0xFF2a3545),
              valueColor: const AlwaysStoppedAnimation<Color>(
                  Color(0xFF00d4ff)),
            ),
          ),
        ],
      ),
    );
  }
}

class _MetricRow extends StatelessWidget {
  final String label;
  final String value;
  final String trend;
  final bool positive;

  const _MetricRow({
    required this.label,
    required this.value,
    required this.trend,
    required this.positive,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: const Color(0xFF1a232f),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFF2a3545)),
      ),
      child: Row(
        children: [
          Expanded(
            child: Text(label,
                style: const TextStyle(color: Color(0xFF8899aa))),
          ),
          Text(value,
              style: const TextStyle(
                  color: Colors.white, fontWeight: FontWeight.bold)),
          const SizedBox(width: 10),
          Container(
            padding:
                const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
            decoration: BoxDecoration(
              color: (positive
                      ? const Color(0xFF00c896)
                      : Colors.red)
                  .withOpacity(0.15),
              borderRadius: BorderRadius.circular(4),
            ),
            child: Text(
              trend,
              style: TextStyle(
                  color: positive
                      ? const Color(0xFF00c896)
                      : Colors.red,
                  fontSize: 12,
                  fontWeight: FontWeight.bold),
            ),
          ),
        ],
      ),
    );
  }
}

class _RankedItem extends StatelessWidget {
  final int rank;
  final String label;
  final int bookings;

  const _RankedItem({
    required this.rank,
    required this.label,
    required this.bookings,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: const Color(0xFF1a232f),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFF2a3545)),
      ),
      child: Row(
        children: [
          Container(
            width: 28,
            height: 28,
            decoration: BoxDecoration(
              color: rank == 1
                  ? const Color(0xFFd4af37).withOpacity(0.2)
                  : const Color(0xFF2a3545),
              shape: BoxShape.circle,
            ),
            child: Center(
              child: Text(
                '$rank',
                style: TextStyle(
                    color: rank == 1
                        ? const Color(0xFFd4af37)
                        : const Color(0xFF8899aa),
                    fontWeight: FontWeight.bold,
                    fontSize: 13),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(label,
                style: const TextStyle(color: Colors.white)),
          ),
          Text(
            '$bookings bookings',
            style: const TextStyle(
                color: Color(0xFF8899aa), fontSize: 13),
          ),
        ],
      ),
    );
  }
}

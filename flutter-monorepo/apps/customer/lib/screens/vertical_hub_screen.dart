import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:pearl_core/pearl_core.dart';

class VerticalHubScreen extends StatelessWidget {
  const VerticalHubScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final items = <_VerticalItem>[
      const _VerticalItem('Property', 'Buy, rent, and lease verified spaces.', Icons.home_rounded, Color(0xFF00d4ff)),
      const _VerticalItem('Stays', 'Villas, hotels, and boutique escapes.', Icons.hotel_rounded, Color(0xFFd4af37)),
      const _VerticalItem('Vehicles', 'Self-drive and chauffeur rentals.', Icons.directions_car_rounded, Color(0xFF38bdf8)),
      const _VerticalItem('Taxi', 'On-demand city and intercity rides.', Icons.local_taxi_rounded, Color(0xFFf59e0b)),
      const _VerticalItem('Events', 'Concert and cinema ticketing.', Icons.event_rounded, Color(0xFFf97316)),
      const _VerticalItem('SME', 'Showcase products and send inquiries.', Icons.storefront_rounded, Color(0xFF22c55e)),
      const _VerticalItem('Experiences', 'Tours and curated island activities.', Icons.terrain_rounded, Color(0xFF06b6d4)),
      const _VerticalItem('Social', 'Community stories and verified reviews.', Icons.chat_bubble_rounded, Color(0xFFa78bfa)),
    ];

    return Scaffold(
      backgroundColor: const Color(0xFF0f1117),
      appBar: AppBar(
        backgroundColor: const Color(0xFF161b22),
        title: const Text('Vertical Hub'),
        actions: [
          IconButton(
            onPressed: () => context.push('/concierge'),
            icon: const Icon(Icons.auto_awesome_rounded, color: Color(0xFFd4af37)),
          ),
        ],
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const AnimatedText(
              'All Marketplace Verticals',
              style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 6),
            Text(
              'Choose a vertical to browse focused inventory and actions.',
              style: TextStyle(color: Colors.blueGrey.shade200),
            ),
            const SizedBox(height: 16),
            Expanded(
              child: GridView.builder(
                itemCount: items.length,
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  crossAxisSpacing: 12,
                  mainAxisSpacing: 12,
                  childAspectRatio: 1.05,
                ),
                itemBuilder: (context, index) {
                  final item = items[index];
                  return AnimatedCard(
                    backgroundColor: const Color(0xFF1a1f2b),
                    borderRadius: 14,
                    onTap: () => context.go('/home'),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          width: 42,
                          height: 42,
                          decoration: BoxDecoration(
                            color: item.accent.withOpacity(0.16),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Icon(item.icon, color: item.accent),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          item.title,
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          item.subtitle,
                          style: const TextStyle(color: Color(0xFF9aa4b2), fontSize: 12),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _VerticalItem {
  final String title;
  final String subtitle;
  final IconData icon;
  final Color accent;

  const _VerticalItem(this.title, this.subtitle, this.icon, this.accent);
}

import 'package:flutter/material.dart';

class SmeSubscriptionScreen extends StatelessWidget {
  const SmeSubscriptionScreen({super.key});

  @override
  Widget build(BuildContext context) {
    const plans = [
      ('Silver', 'LKR 25,000/year', 'Up to 100 products'),
      ('Gold', 'LKR 50,000/year', 'Up to 500 products + insights'),
      ('Platinum', 'LKR 65,000/year', 'Unlimited products + variants + bulk upload'),
    ];

    return Scaffold(
      backgroundColor: const Color(0xFF0a0e27),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1a232f),
        title: const Text('SME Subscriptions'),
      ),
      body: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: plans.length,
        itemBuilder: (context, i) {
          final plan = plans[i];
          final accent = i == 2 ? const Color(0xFFd4af37) : const Color(0xFF00d4ff);
          return Container(
            margin: const EdgeInsets.only(bottom: 12),
            decoration: BoxDecoration(
              color: const Color(0xFF1a232f),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: accent.withOpacity(0.5)),
            ),
            child: ListTile(
              title: Text(plan.$1, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
              subtitle: Text('${plan.$2}\n${plan.$3}', style: const TextStyle(color: Color(0xFF93a0af))),
              trailing: ElevatedButton(
                onPressed: () {},
                style: ElevatedButton.styleFrom(backgroundColor: accent, foregroundColor: Colors.black),
                child: const Text('Activate'),
              ),
            ),
          );
        },
      ),
    );
  }
}

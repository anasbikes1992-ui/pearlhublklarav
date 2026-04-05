import 'package:flutter/material.dart';

class VerticalPolicyScreen extends StatelessWidget {
  const VerticalPolicyScreen({super.key});

  @override
  Widget build(BuildContext context) {
    const rows = [
      ('Property', '6%', 'Booking + Escrow'),
      ('Stay', '9%', 'Booking + Escrow'),
      ('Vehicle', '8%', 'Booking + Escrow'),
      ('Taxi', '12%', 'Instant booking'),
      ('Event', '10%', 'Booking + ticket flow'),
      ('SME', '0%', 'Inquiry-only showcase'),
    ];

    return Scaffold(
      backgroundColor: const Color(0xFF0a0e27),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1a232f),
        title: const Text('Vertical Policy Rules'),
      ),
      body: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: rows.length,
        itemBuilder: (context, i) {
          final row = rows[i];
          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFF1a232f),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFF2a3545)),
            ),
            child: Row(
              children: [
                Expanded(child: Text(row.$1, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600))),
                Text(row.$2, style: const TextStyle(color: Color(0xFFd4af37), fontWeight: FontWeight.bold)),
                const SizedBox(width: 14),
                Text(row.$3, style: const TextStyle(color: Color(0xFF93a0af), fontSize: 12)),
              ],
            ),
          );
        },
      ),
    );
  }
}

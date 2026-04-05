import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:pearl_core/pearl_core.dart';

class ConciergeScreen extends StatefulWidget {
  const ConciergeScreen({super.key});

  @override
  State<ConciergeScreen> createState() => _ConciergeScreenState();
}

class _ConciergeScreenState extends State<ConciergeScreen> {
  final _queryCtrl = TextEditingController();
  String _reply = 'Ask for recommendations, translation help, or booking assistance.';
  bool _loading = false;

  Future<void> _ask() async {
    if (_queryCtrl.text.trim().isEmpty) {
      return;
    }

    setState(() => _loading = true);
    try {
      final service = ConciergeService(context.read<SharedApiClient>());
      final response = await service.ask(_queryCtrl.text.trim(), context: {'source': 'customer_app'});
      setState(() => _reply = response.reply);
    } catch (_) {
      setState(() => _reply = 'Concierge is currently unavailable.');
    } finally {
      setState(() => _loading = false);
    }
  }

  @override
  void dispose() {
    _queryCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0f1117),
      appBar: AppBar(
        backgroundColor: const Color(0xFF161b22),
        title: const Text('AI Concierge'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            AnimatedCard(
              backgroundColor: const Color(0xFF1a1f2b),
              borderRadius: 14,
              child: Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: const Color(0xFFd4af37).withOpacity(0.2),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.auto_awesome_rounded, color: Color(0xFFd4af37)),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(_reply, style: const TextStyle(color: Color(0xFFd3d9e2))),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _queryCtrl,
              style: const TextStyle(color: Colors.white),
              decoration: InputDecoration(
                hintText: 'Where should I stay in Ella under LKR 30,000?',
                hintStyle: const TextStyle(color: Color(0xFF7f8b99)),
                filled: true,
                fillColor: const Color(0xFF1a1f2b),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: Color(0xFF2f3a49)),
                ),
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _loading ? null : _ask,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF00d4ff),
                  foregroundColor: Colors.black,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                ),
                icon: _loading
                    ? const SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.black),
                      )
                    : const Icon(Icons.send_rounded),
                label: const Text('Ask Concierge', style: TextStyle(fontWeight: FontWeight.w700)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

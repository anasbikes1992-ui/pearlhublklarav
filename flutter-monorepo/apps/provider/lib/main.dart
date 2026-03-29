import 'package:flutter/material.dart';

void main() {
  runApp(const ProviderApp());
}

class ProviderApp extends StatelessWidget {
  const ProviderApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'PearlHub Provider',
      home: const Scaffold(
        body: Center(child: Text('Provider App Scaffold')),
      ),
    );
  }
}

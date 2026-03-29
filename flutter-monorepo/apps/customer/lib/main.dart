import 'package:flutter/material.dart';

void main() {
  runApp(const CustomerApp());
}

class CustomerApp extends StatelessWidget {
  const CustomerApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'PearlHub Customer',
      home: const Scaffold(
        body: Center(child: Text('Customer App Scaffold')),
      ),
    );
  }
}

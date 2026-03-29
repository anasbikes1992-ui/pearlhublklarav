import 'package:flutter/material.dart';

void main() {
  runApp(const AdminApp());
}

class AdminApp extends StatelessWidget {
  const AdminApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'PearlHub Admin',
      home: const Scaffold(
        body: Center(child: Text('Admin App Scaffold')),
      ),
    );
  }
}

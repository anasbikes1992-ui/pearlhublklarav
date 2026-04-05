import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:pearl_core/pearl_core.dart';
import 'services/provider_service.dart';
import 'screens/dashboard_screen.dart';
import 'screens/listings_screen.dart';
import 'screens/bookings_screen.dart';
import 'screens/create_listing_screen.dart';
import 'screens/sme_subscription_screen.dart';
import 'screens/sales_report_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  final apiClient = SharedApiClient(
    baseUrl: const String.fromEnvironment(
      'API_URL',
      defaultValue: 'http://localhost:8000/api/v1',
    ),
  );

  final authService = ProviderAuthService(apiClient: apiClient);
  await authService.initialize();

  runApp(MyApp(apiClient: apiClient, authService: authService));
}

class MyApp extends StatelessWidget {
  final SharedApiClient apiClient;
  final ProviderAuthService authService;

  const MyApp({required this.apiClient, required this.authService});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<SharedApiClient>.value(value: apiClient),
        Provider<ProviderAuthService>.value(value: authService),
        Provider<ProviderApiService>(
          create: (_) => ProviderApiService(apiClient: apiClient),
        ),
      ],
      child: MaterialApp.router(
        title: 'PearlHub Provider',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          useMaterial3: true,
          brightness: Brightness.dark,
          scaffoldBackgroundColor: const Color(0xFF0a0e27),
          colorScheme: ColorScheme.dark(
            primary: const Color(0xFF00d4ff),
            secondary: const Color(0xFFd4af37),
            surface: const Color(0xFF1a232f),
          ),
          appBarTheme: const AppBarTheme(
            backgroundColor: Color(0xFF1a232f),
            elevation: 0,
            titleTextStyle: TextStyle(
              color: Colors.white,
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
            iconTheme: IconThemeData(color: Colors.white),
          ),
        ),
        routerConfig: GoRouter(
          initialLocation:
              authService.isAuthenticated ? '/dashboard' : '/login',
          routes: [
            GoRoute(
              path: '/login',
              builder: (context, state) => const _ProviderLoginScreen(),
            ),
            GoRoute(
              path: '/dashboard',
              builder: (context, state) => const ProviderDashboardScreen(),
            ),
            GoRoute(
              path: '/listings',
              builder: (context, state) => const ProviderListingsScreen(),
            ),
            GoRoute(
              path: '/listings/create',
              builder: (context, state) => const CreateListingScreen(),
            ),
            GoRoute(
              path: '/bookings',
              builder: (context, state) => const ProviderBookingsScreen(),
            ),
            GoRoute(
              path: '/sme/subscriptions',
              builder: (context, state) => const SmeSubscriptionScreen(),
            ),
            GoRoute(
              path: '/sme/sales-report',
              builder: (context, state) => const SalesReportScreen(),
            ),
          ],
        ),
      ),
    );
  }
}

/// Minimal login screen for the provider app.
class _ProviderLoginScreen extends StatefulWidget {
  const _ProviderLoginScreen();

  @override
  State<_ProviderLoginScreen> createState() => _ProviderLoginScreenState();
}

class _ProviderLoginScreenState extends State<_ProviderLoginScreen> {
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  bool _loading = false;
  String? _error;

  Future<void> _login() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      await context
          .read<ProviderAuthService>()
          .login(_emailCtrl.text.trim(), _passCtrl.text);
      if (mounted) context.go('/dashboard');
    } catch (e) {
      setState(() {
        _error = 'Invalid credentials. Please try again.';
        _loading = false;
      });
    }
  }

  @override
  void dispose() {
    _emailCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0a0e27),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.storefront_rounded,
                  color: Color(0xFF00d4ff), size: 60),
              const SizedBox(height: 16),
              const Text(
                'PearlHub Provider',
                style: TextStyle(
                    color: Colors.white,
                    fontSize: 24,
                    fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              const Text('Sign in to manage your listings',
                  style: TextStyle(color: Color(0xFF8899aa))),
              const SizedBox(height: 32),
              if (_error != null) ...[  
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.red.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.red.withOpacity(0.4)),
                  ),
                  child: Text(_error!,
                      style: const TextStyle(color: Colors.red)),
                ),
                const SizedBox(height: 16),
              ],
              TextField(
                controller: _emailCtrl,
                keyboardType: TextInputType.emailAddress,
                style: const TextStyle(color: Colors.white),
                decoration: _inputDeco('Email'),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: _passCtrl,
                obscureText: true,
                style: const TextStyle(color: Colors.white),
                decoration: _inputDeco('Password'),
                onSubmitted: (_) => _login(),
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF00d4ff),
                    foregroundColor: Colors.black,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: _loading ? null : _login,
                  child: _loading
                      ? const SizedBox(
                          width: 22,
                          height: 22,
                          child: CircularProgressIndicator(
                              color: Colors.black, strokeWidth: 2.5),
                        )
                      : const Text('Sign In',
                          style: TextStyle(
                              fontWeight: FontWeight.bold, fontSize: 16)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  InputDecoration _inputDeco(String label) => InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(color: Color(0xFF8899aa)),
        filled: true,
        fillColor: const Color(0xFF1a232f),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Color(0xFF2a3545)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Color(0xFF2a3545)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide:
              const BorderSide(color: Color(0xFF00d4ff), width: 1.5),
        ),
      );
}

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:pearl_core/pearl_core.dart';
import 'models/models.dart';
import 'services/admin_service.dart';
import 'screens/dashboard_screen.dart';
import 'screens/verification_screen.dart';
import 'screens/analytics_screen.dart';
import 'screens/users_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  final apiClient = SharedApiClient(
    baseUrl: const String.fromEnvironment(
      'API_URL',
      defaultValue: 'http://localhost:8000/api/v1',
    ),
  );

  final authService = AdminAuthService(apiClient: apiClient);
  await authService.initialize();

  runApp(MyApp(apiClient: apiClient, authService: authService));
}

class MyApp extends StatelessWidget {
  final SharedApiClient apiClient;
  final AdminAuthService authService;

  const MyApp({required this.apiClient, required this.authService});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<SharedApiClient>.value(value: apiClient),
        Provider<AdminAuthService>.value(value: authService),
        Provider<AdminApiService>(
          create: (_) => AdminApiService(apiClient: apiClient),
        ),
      ],
      child: MaterialApp.router(
        title: 'PearlHub Admin',
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
              builder: (context, state) => const _AdminLoginScreen(),
            ),
            GoRoute(
              path: '/dashboard',
              builder: (context, state) => const AdminDashboardScreen(),
            ),
            GoRoute(
              path: '/verification',
              builder: (context, state) => const VerificationScreen(),
            ),
            GoRoute(
              path: '/analytics',
              builder: (context, state) => const AnalyticsScreen(),
            ),
            GoRoute(
              path: '/users',
              builder: (context, state) => const UsersScreen(),
            ),
          ],
        ),
      ),
    );
  }
}

class _AdminLoginScreen extends StatefulWidget {
  const _AdminLoginScreen();

  @override
  State<_AdminLoginScreen> createState() => _AdminLoginScreenState();
}

class _AdminLoginScreenState extends State<_AdminLoginScreen> {
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
          .read<AdminAuthService>()
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
              const Icon(Icons.admin_panel_settings_rounded,
                  color: Color(0xFFd4af37), size: 60),
              const SizedBox(height: 16),
              const Text(
                'PearlHub Admin',
                style: TextStyle(
                    color: Colors.white,
                    fontSize: 24,
                    fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              const Text('Administrator access only',
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
                decoration: _inputDeco('Admin Email'),
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
                    backgroundColor: const Color(0xFFd4af37),
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
              const BorderSide(color: Color(0xFFd4af37), width: 1.5),
        ),
      );
}

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:pearl_core/pearl_core.dart';
import 'services/auth_service.dart';
import 'services/listing_service.dart';
import 'services/booking_service.dart';
import 'screens/login_screen.dart';
import 'screens/register_screen.dart';
import 'screens/home_screen.dart';
import 'screens/listing_detail_screen.dart';
import 'screens/bookings_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize the API client
  final apiClient = SharedApiClient(
    baseUrl: const String.fromEnvironment(
      'API_URL',
      defaultValue: 'http://localhost:8000/api/v1',
    ),
  );

  // Initialize auth service
  final authService = AuthService(apiClient: apiClient);
  await authService.initialize();

  runApp(MyApp(
    apiClient: apiClient,
    authService: authService,
  ));
}

class MyApp extends StatelessWidget {
  final SharedApiClient apiClient;
  final AuthService authService;

  const MyApp({
    required this.apiClient,
    required this.authService,
  });

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<SharedApiClient>.value(value: apiClient),
        Provider<AuthService>.value(value: authService),
        ProxyProvider<SharedApiClient, ListingService>(
          update: (_, apiClient, __) => ListingService(apiClient),
        ),
        ProxyProvider<SharedApiClient, BookingService>(
          update: (_, apiClient, __) => BookingService(apiClient),
        ),
      ],
      child: MaterialApp.router(
        title: 'PearlHub Customer',
        theme: ThemeData(
          useMaterial3: true,
          brightness: Brightness.dark,
          scaffoldBackgroundColor: const Color(0xFF0f1117),
          appBarTheme: const AppBarTheme(
            color: Color(0xFF161b22),
            elevation: 0,
          ),
        ),
        routerConfig: _buildRouter(authService),
      ),
    );
  }

  GoRouter _buildRouter(AuthService authService) {
    return GoRouter(
      initialLocation: authService.isAuthenticated ? '/home' : '/login',
      redirect: (context, state) {
        final isLoggedIn = authService.isAuthenticated;
        final isLoggingIn = state.matchedLocation == '/login' ||
            state.matchedLocation == '/register';

        if (!isLoggedIn && !isLoggingIn) {
          return '/login';
        }

        if (isLoggedIn && isLoggingIn) {
          return '/home';
        }

        return null;
      },
      routes: [
        GoRoute(
          path: '/login',
          builder: (context, state) => const LoginScreen(),
        ),
        GoRoute(
          path: '/register',
          builder: (context, state) => const RegisterScreen(),
        ),
        GoRoute(
          path: '/home',
          builder: (context, state) => const HomeScreen(),
        ),
        GoRoute(
          path: '/listing/:slug',
          builder: (context, state) => ListingDetailScreen(
            slug: state.pathParameters['slug']!,
          ),
        ),
        GoRoute(
          path: '/bookings',
          builder: (context, state) => const BookingsScreen(),
        ),
      ],
    );
  }
}


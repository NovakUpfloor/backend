import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:waisaka_property_mobile/core/di/service_locator.dart';
import 'package:waisaka_property_mobile/features/auth/data/repositories/auth_repository.dart';
import 'package:waisaka_property_mobile/features/auth/presentation/views/login_screen.dart';
import 'package:waisaka_property_mobile/features/auth/presentation/views/register_screen.dart';
import 'package:waisaka_property_mobile/features/home/presentation/views/home_screen.dart';
import 'package:waisaka_property_mobile/features/property/presentation/views/property_detail_screen.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/presentation/views/add_property_screen.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/presentation/views/dashboard_screen.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/presentation/views/purchase_package_screen.dart';

// Main App Widget
class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      routerConfig: _router,
      title: 'Waisaka Property',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.blue),
        useMaterial3: true,
        scaffoldBackgroundColor: Colors.grey[100],
        appBarTheme: const AppBarTheme(
          backgroundColor: Colors.blue,
          foregroundColor: Colors.white,
          elevation: 2,
        ),
      ),
    );
  }
}

// GoRouter configuration
final GoRouter _router = GoRouter(
  initialLocation: '/',
  redirect: (BuildContext context, GoRouterState state) async {
    final authRepository = sl<AuthRepository>();
    final token = await authRepository.getToken();

    final loggingIn = state.matchedLocation == '/login' || state.matchedLocation == '/register';
    final isPublic = state.matchedLocation == '/' || state.matchedLocation.startsWith('/property');

    // If there's no token and the user is trying to access a protected route
    if (token == null && !loggingIn && !isPublic) {
      return '/login';
    }

    // If the user is logged in and tries to go to login/register, redirect to home
    if (token != null && isLoggingIn) {
      return '/';
    }

    // No redirect needed
    return null;
  },
  routes: [
    GoRoute(
      path: '/',
      builder: (context, state) => const HomeScreen(),
    ),
    GoRoute(
      path: '/login',
      builder: (context, state) => const LoginScreen(),
    ),
    GoRoute(
      path: '/register',
      builder: (context, state) => const RegisterScreen(),
    ),
    GoRoute(
      path: '/property/:id',
      builder: (context, state) {
        final id = state.pathParameters['id']!;
        return PropertyDetailScreen(propertyId: id);
      },
    ),
    GoRoute(
      path: '/dashboard',
      builder: (context, state) => const DashboardScreen(),
      routes: [
        GoRoute(
          path: 'purchase-package',
          builder: (context, state) => const PurchasePackageScreen(),
        ),
        GoRoute(
          path: 'add-property',
          builder: (context, state) => const AddPropertyScreen(),
        ),
      ]
    ),
  ],
);

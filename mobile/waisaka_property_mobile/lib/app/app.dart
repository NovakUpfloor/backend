import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:waisaka_property_mobile/features/article/data/models/article.dart';
import 'package:waisaka_property_mobile/features/article/presentation/views/article_detail_screen.dart';
import 'package:waisaka_property_mobile/features/auth/presentation/views/login_screen.dart';
import 'package:waisaka_property_mobile/features/auth/presentation/views/register_screen.dart';
import 'package:waisaka_property_mobile/features/admin_dashboard/presentation/views/purchase_confirmation_screen.dart';
import 'package:waisaka_property_mobile/features/home/presentation/views/home_screen.dart';
import 'package:waisaka_property_mobile/features/property/presentation/views/property_detail_screen.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/presentation/views/dashboard_screen.dart';

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
      ),
    );
  }
}

// GoRouter configuration
final GoRouter _router = GoRouter(
  initialLocation: '/home',
  routes: [
    GoRoute(
      path: '/home',
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
      path: '/article',
      builder: (context, state) {
        final article = state.extra as Article;
        return ArticleDetailScreen(article: article);
      },
    ),
    GoRoute(
      path: '/dashboard',
      builder: (context, state) => const DashboardScreen(),
    ),
    GoRoute(
      path: '/admin/purchase-confirmations',
      builder: (context, state) => const PurchaseConfirmationScreen(),
    ),
  ],
  errorBuilder: (context, state) => Scaffold(
    body: Center(
      child: Text('Page not found: ${state.error}'),
    ),
  ),
);

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:waisaka_property_mobile/core/di/service_locator.dart';
import 'package:waisaka_property_mobile/features/auth/data/models/user.dart';
import 'package:waisaka_property_mobile/features/auth/presentation/bloc/auth_bloc.dart';

class DashboardScreen extends StatelessWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => sl<AuthBloc>()..add(FetchUserProfile()),
      child: const _DashboardView(),
    );
  }
}

class _DashboardView extends StatefulWidget {
  const _DashboardView();

  @override
  State<_DashboardView> createState() => _DashboardViewState();
}

class _DashboardViewState extends State<_DashboardView> {
  int _selectedIndex = 0;

  @override
  void initState() {
    super.initState();
    // Ensure we fetch the user profile when the screen is initialized.
    // The BlocProvider above already does this, but this is a good practice
    // if the logic were to change.
    context.read<AuthBloc>().add(FetchUserProfile());
  }

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<AuthBloc, AuthState>(
      builder: (context, state) {
        if (state is UserProfileLoading || state is AuthInitial) {
          return const Scaffold(body: Center(child: CircularProgressIndicator()));
        }
        if (state is AuthFailure) {
          return Scaffold(body: Center(child: Text('Failed to load user data: ${state.error}')));
        }
        if (state is UserProfileLoaded) {
          final user = state.user;
          final bool isAdmin = user.aksesLevel == 'Admin';

          final List<Widget> widgetOptions = [
            const _MyPropertiesView(),
            const _AddPropertyView(),
            _ProfileView(user: user),
            if (isAdmin) const _AdminDashboardView(),
          ];

          final List<BottomNavigationBarItem> navItems = [
            const BottomNavigationBarItem(icon: Icon(Icons.list_alt), label: 'My Properties'),
            const BottomNavigationBarItem(icon: Icon(Icons.add_business), label: 'Add Property'),
            const BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Profile'),
            if (isAdmin) const BottomNavigationBarItem(icon: Icon(Icons.admin_panel_settings), label: 'Admin'),
          ];

          return BlocListener<AuthBloc, AuthState>(
            listener: (context, state) {
              if (state is AuthLogoutSuccess) {
                // Using go() to clear the navigation stack and go to home
                GoRouter.of(context).go('/');
              }
            },
            child: Scaffold(
              appBar: AppBar(
                title: const Text('My Dashboard'),
                actions: [
                  IconButton(
                    icon: const Icon(Icons.logout),
                    tooltip: 'Logout',
                    onPressed: () {
                      context.read<AuthBloc>().add(AuthLogoutRequested());
                    },
                  ),
                ],
              ),
              body: widgetOptions.elementAt(_selectedIndex),
              bottomNavigationBar: BottomNavigationBar(
                items: navItems,
                currentIndex: _selectedIndex,
                selectedItemColor: Colors.blue[800],
                unselectedItemColor: Colors.grey,
                onTap: _onItemTapped,
              ),
            ),
          );
        }
        return const Scaffold(body: Center(child: Text('Something went wrong.')));
      },
    );
  }
}

// --- Placeholder Views for each tab ---

class _MyPropertiesView extends StatelessWidget {
  const _MyPropertiesView();
  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Padding(
        padding: EdgeInsets.all(16.0),
        child: Text(
          'Feature to view your listed properties is coming soon.',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 16, color: Colors.grey),
        ),
      ),
    );
  }
}

class _AddPropertyView extends StatelessWidget {
  const _AddPropertyView();
  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Padding(
        padding: EdgeInsets.all(16.0),
        child: Text(
          'Feature to add a new property is coming soon.',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 16, color: Colors.grey),
        ),
      ),
    );
  }
}

class _ProfileView extends StatelessWidget {
  final User user;
  const _ProfileView({required this.user});

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16.0),
      children: [
        CircleAvatar(
          radius: 50,
          child: Text(user.nama.substring(0, 1), style: const TextStyle(fontSize: 40)),
        ),
        const SizedBox(height: 16),
        Text(user.nama, textAlign: TextAlign.center, style: Theme.of(context).textTheme.headlineSmall),
        Text(user.email, textAlign: TextAlign.center, style: Theme.of(context).textTheme.bodySmall),
        const SizedBox(height: 24),
        Card(
          child: ListTile(
            leading: const Icon(Icons.article, color: Colors.blue),
            title: const Text('Remaining Ad Quota'),
            trailing: Text(
              user.sisaKuota.toString(),
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(color: Colors.green[800]),
            ),
          ),
        ),
        const SizedBox(height: 24),
        ElevatedButton.icon(
          icon: const Icon(Icons.add_shopping_cart),
          label: const Text('Purchase Ad Package'),
          style: ElevatedButton.styleFrom(
            padding: const EdgeInsets.symmetric(vertical: 12),
          ),
          onPressed: () {
            // TODO: Create and navigate to the purchase screen
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Purchase screen coming soon!')),
            );
          },
        ),
      ],
    );
  }
}

class _AdminDashboardView extends StatelessWidget {
  const _AdminDashboardView();
  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Text('Admin Management Area'),
    );
  }
}

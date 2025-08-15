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

          return Scaffold(
            appBar: AppBar(
              title: const Text('My Dashboard'),
              // TODO: Add logout button
            ),
            body: widgetOptions.elementAt(_selectedIndex),
            bottomNavigationBar: BottomNavigationBar(
              items: navItems,
              currentIndex: _selectedIndex,
              selectedItemColor: Colors.blue[800],
              unselectedItemColor: Colors.grey,
              onTap: _onItemTapped,
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
    return const Center(child: Text('List of my properties will be here.'));
  }
}

class _AddPropertyView extends StatelessWidget {
  const _AddPropertyView();
  @override
  Widget build(BuildContext context) {
    return const Center(child: Text('Form to add a new property will be here.'));
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
        const SizedBox(height: 16),
        ElevatedButton.icon(
          icon: const Icon(Icons.history),
          label: const Text('View Purchase History'),
          onPressed: () {
            // TODO: Navigate to Purchase History Screen
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Purchase History screen coming soon!')),
            );
          },
        ),
        const SizedBox(height: 8),
        TextButton.icon(
          icon: const Icon(Icons.edit),
          label: const Text('Edit Profile'),
          onPressed: () {
            // TODO: Navigate to Edit Profile Screen
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
    return ListView(
      padding: const EdgeInsets.all(16.0),
      children: [
        Text('Admin Tools', style: Theme.of(context).textTheme.headlineSmall),
        const SizedBox(height: 16),
        Card(
          child: ListTile(
            leading: const Icon(Icons.check_circle_outline, color: Colors.green),
            title: const Text('Purchase Confirmations'),
            subtitle: const Text('Approve or reject new package purchases.'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () {
              // TODO: Navigate to Purchase Confirmation Screen
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Purchase Confirmation screen coming soon!')),
              );
            },
          ),
        ),
        Card(
          child: ListTile(
            leading: const Icon(Icons.people_outline, color: Colors.blue),
            title: const Text('Agent Management'),
            subtitle: const Text('View and manage all property agents.'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () {
              // TODO: Navigate to Agent Management Screen
               ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Agent Management screen coming soon!')),
              );
            },
          ),
        ),
      ],
    );
  }
}

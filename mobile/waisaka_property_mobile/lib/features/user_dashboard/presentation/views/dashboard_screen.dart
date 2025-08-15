import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:waisaka_property_mobile/core/di/service_locator.dart';
import 'package:waisaka_property_mobile/features/auth/data/models/user.dart';
import 'package:waisaka_property_mobile/features/auth/presentation/bloc/auth_bloc.dart';
import 'package:waisaka_property_mobile/features/property/data/models/property.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/presentation/bloc/my_properties_bloc.dart';

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
                showUnselectedLabels: true,
              ),
            ),
          );
        }
        return const Scaffold(body: Center(child: Text('Something went wrong.')));
      },
    );
  }
}

// --- Views for each tab ---

class _MyPropertiesView extends StatelessWidget {
  const _MyPropertiesView();
  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => sl<MyPropertiesBloc>()..add(FetchMyPropertiesList()),
      child: BlocBuilder<MyPropertiesBloc, MyPropertiesState>(
        builder: (context, state) {
          if (state is MyPropertiesLoading) {
            return const Center(child: CircularProgressIndicator());
          }
          if (state is MyPropertiesLoadFailure) {
            return Center(child: Text('Error: ${state.error}'));
          }
          if (state is MyPropertiesLoadSuccess) {
            if (state.properties.isEmpty) {
              return const Center(child: Text('You have not listed any properties yet.'));
            }
            return ListView.builder(
              itemCount: state.properties.length,
              itemBuilder: (context, index) {
                final Property property = state.properties[index];
                return Card(
                  margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: ListTile(
                    title: Text(property.namaProperty, style: const TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: Text('Rp. ${NumberFormat.decimalPattern('id_ID').format(property.harga ?? 0)}'),
                    trailing: Icon(
                      property.status == 1 ? Icons.visibility : Icons.visibility_off,
                      color: property.status == 1 ? Colors.green : Colors.red,
                    ),
                    onTap: () => context.go('/property/${property.id}'),
                  ),
                );
              },
            );
          }
          return const Center(child: Text('Welcome to your properties.'));
        },
      ),
    );
  }
}

class _AddPropertyView extends StatelessWidget {
  const _AddPropertyView();
  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text(
              'Do you have a property to sell or rent?',
              style: TextStyle(fontSize: 18),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              icon: const Icon(Icons.add_business_outlined),
              label: const Text('Add New Property Listing'),
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
              ),
              onPressed: () => context.go('/dashboard/add-property'),
            ),
          ],
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
            context.go('/dashboard/purchase-package');
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

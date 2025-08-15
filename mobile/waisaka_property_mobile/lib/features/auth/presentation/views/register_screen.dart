import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:waisaka_property_mobile/core/di/service_locator.dart';
import 'package:waisaka_property_mobile/features/auth/data/models/package.dart';
import 'package:waisaka_property_mobile/features/auth/data/repositories/package_repository.dart';
import 'package:waisaka_property_mobile/features/auth/presentation/bloc/auth_bloc.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _usernameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  List<AdPackage> _packages = [];
  int? _selectedPackageId;
  File? _paymentProof;
  bool _isFetchingPackages = true;

  @override
  void initState() {
    super.initState();
    _fetchPackages();
  }

  Future<void> _fetchPackages() async {
    try {
      final packageRepository = sl<PackageRepository>();
      final packages = await packageRepository.fetchPackages();
      setState(() {
        _packages = packages;
        _isFetchingPackages = false;
      });
    } catch (e) {
      setState(() {
        _isFetchingPackages = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to load packages: $e')),
      );
    }
  }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final pickedFile = await picker.pickImage(source: ImageSource.gallery);
    if (pickedFile != null) {
      setState(() {
        _paymentProof = File(pickedFile.path);
      });
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _usernameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _register() {
    if (_formKey.currentState!.validate()) {
      if (_selectedPackageId == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Please select an advertising package.')),
        );
        return;
      }
      if (_paymentProof == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Please upload your proof of payment.')),
        );
        return;
      }

      context.read<AuthBloc>().add(AuthRegisterRequested(
            name: _nameController.text,
            username: _usernameController.text,
            email: _emailController.text,
            password: _passwordController.text,
            packageId: _selectedPackageId!,
            paymentProofPath: _paymentProof!.path,
          ));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Create Account'),
      ),
      body: BlocListener<AuthBloc, AuthState>(
        listener: (context, state) {
          if (state is AuthRegisterSuccess) {
            showDialog(
              context: context,
              builder: (context) => AlertDialog(
                title: const Text('Registration Successful'),
                content: const Text('A verification link has been sent to your email. Please verify to continue.'),
                actions: [
                  TextButton(
                    onPressed: () => GoRouter.of(context).go('/login'),
                    child: const Text('OK'),
                  ),
                ],
              ),
            );
          } else if (state is AuthFailure) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text('Registration Failed: ${state.error}')),
            );
          }
        },
        child: SingleChildScrollView(
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text(
                    'Want to advertise your property with us? Sign up now!',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 16, color: Colors.black54),
                  ),
                  const SizedBox(height: 24),
                  TextFormField(
                    controller: _nameController,
                    decoration: const InputDecoration(labelText: 'Full Name', border: OutlineInputBorder(), prefixIcon: Icon(Icons.badge)),
                    validator: (v) => v!.isEmpty ? 'Please enter your full name' : null,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _usernameController,
                    decoration: const InputDecoration(labelText: 'Username', border: OutlineInputBorder(), prefixIcon: Icon(Icons.person)),
                    validator: (v) => v!.isEmpty ? 'Please enter a username' : null,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    decoration: const InputDecoration(labelText: 'Email', border: OutlineInputBorder(), prefixIcon: Icon(Icons.email)),
                    validator: (v) => v!.isEmpty || !v.contains('@') ? 'Please enter a valid email' : null,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _passwordController,
                    obscureText: true,
                    decoration: const InputDecoration(labelText: 'Password', border: OutlineInputBorder(), prefixIcon: Icon(Icons.lock)),
                    validator: (v) => v!.length < 6 ? 'Password must be at least 6 characters' : null,
                  ),
                  const SizedBox(height: 24),
                  Text('Select a Package', style: Theme.of(context).textTheme.titleLarge),
                  _buildPackageList(),
                  const SizedBox(height: 24),
                  _buildPaymentProofSection(),
                  const SizedBox(height: 24),
                  BlocBuilder<AuthBloc, AuthState>(
                    builder: (context, state) {
                      if (state is AuthLoading) {
                        return const Center(child: CircularProgressIndicator());
                      }
                      return ElevatedButton(
                        onPressed: _register,
                        style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16)),
                        child: const Text('Register'),
                      );
                    },
                  ),
                  const SizedBox(height: 16),
                  TextButton(
                    onPressed: () => GoRouter.of(context).go('/login'),
                    child: const Text('Already have an account? Login'),
                  )
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPackageList() {
    if (_isFetchingPackages) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_packages.isEmpty) {
      return const Center(child: Text('No packages available.'));
    }
    return Column(
      children: _packages.map((pkg) {
        return Card(
          margin: const EdgeInsets.symmetric(vertical: 4.0),
          child: RadioListTile<int>(
            title: Text(pkg.name),
            subtitle: Text('Rp. ${NumberFormat.decimalPattern('id_ID').format(pkg.price)}\n${pkg.adQuota} ad credits'),
            value: pkg.id,
            groupValue: _selectedPackageId,
            onChanged: (value) {
              setState(() {
                _selectedPackageId = value;
              });
            },
          ),
        );
      }).toList(),
    );
  }

  Widget _buildPaymentProofSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Upload Payment Proof', style: Theme.of(context).textTheme.titleLarge),
        const SizedBox(height: 8),
        Container(
          height: 150,
          width: double.infinity,
          decoration: BoxDecoration(
            border: Border.all(color: Colors.grey),
            borderRadius: BorderRadius.circular(8),
          ),
          child: _paymentProof != null
              ? Image.file(_paymentProof!, fit: BoxFit.cover)
              : const Center(child: Text('No image selected')),
        ),
        const SizedBox(height: 8),
        SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            icon: const Icon(Icons.upload_file),
            label: const Text('Pick Image'),
            onPressed: _pickImage,
          ),
        ),
      ],
    );
  }
}

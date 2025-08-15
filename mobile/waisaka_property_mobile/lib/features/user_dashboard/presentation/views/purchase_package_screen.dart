import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:waisaka_property_mobile/core/di/service_locator.dart';
import 'package:waisaka_property_mobile/features/auth/data/repositories/auth_repository.dart';
import 'package:waisaka_property_mobile/features/auth/presentation/bloc/auth_bloc.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/presentation/bloc/purchase_bloc.dart';

class PurchasePackageScreen extends StatelessWidget {
  const PurchasePackageScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiBlocProvider(
      providers: [
        BlocProvider(
          create: (context) => sl<AuthBloc>()..add(FetchPackages()),
        ),
        BlocProvider(
          create: (context) => sl<PurchaseBloc>(),
        ),
      ],
      child: const _PurchasePackageView(),
    );
  }
}


class _PurchasePackageView extends StatefulWidget {
  const _PurchasePackageView();

  @override
  State<_PurchasePackageView> createState() => _PurchasePackageViewState();
}

class _PurchasePackageViewState extends State<_PurchasePackageView> {
  final _formKey = GlobalKey<FormState>();
  final _whatsappController = TextEditingController();
  int? _selectedPackageId;
  File? _paymentProofImage;

  final ImagePicker _picker = ImagePicker();

  @override
  void dispose() {
    _whatsappController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    final XFile? pickedFile = await _picker.pickImage(source: ImageSource.gallery);
    if (pickedFile != null) {
      setState(() {
        _paymentProofImage = File(pickedFile.path);
      });
    }
  }

  void _submitPurchase() {
    if (context.read<PurchaseBloc>().state is PurchaseLoading) return;

    if (_formKey.currentState!.validate()) {
      if (_selectedPackageId == null) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please select a package.')));
        return;
      }
      if (_paymentProofImage == null) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please upload payment proof.')));
        return;
      }

      context.read<PurchaseBloc>().add(SubmitPurchase(
        packageId: _selectedPackageId!,
        whatsappNumber: _whatsappController.text,
        paymentProof: _paymentProofImage!,
      ));
    }
  }


  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Purchase Ad Package'),
      ),
      body: BlocListener<PurchaseBloc, PurchaseState>(
        listener: (context, state) {
          if (state is PurchaseFailure) {
            ScaffoldMessenger.of(context)..hideCurrentSnackBar()..showSnackBar(SnackBar(content: Text('Submission Failed: ${state.error}')));
          }
          if (state is PurchaseSuccess) {
             ScaffoldMessenger.of(context)..hideCurrentSnackBar()..showSnackBar(const SnackBar(content: Text('Purchase submitted successfully! Waiting for admin confirmation.')));
             context.pop();
          }
        },
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16.0),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                _buildPackageList(),
                const SizedBox(height: 24),
                TextFormField(
                  controller: _whatsappController,
                  decoration: const InputDecoration(
                    labelText: 'WhatsApp Number (e.g., 62812...)'
                  ),
                  keyboardType: TextInputType.phone,
                  validator: (v) => v == null || v.isEmpty ? 'Please enter your WhatsApp number' : null,
                ),
                const SizedBox(height: 24),
                _buildImagePicker(),
                const SizedBox(height: 32),
                BlocBuilder<PurchaseBloc, PurchaseState>(
                  builder: (context, state) {
                    final isLoading = state is PurchaseLoading;
                    return ElevatedButton(
                      onPressed: isLoading ? null : _submitPurchase,
                      style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16)),
                      child: isLoading ? const CircularProgressIndicator(color: Colors.white) : const Text('Confirm Purchase'),
                    );
                  },
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPackageList() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Select a Package:', style: Theme.of(context).textTheme.titleMedium),
        const SizedBox(height: 8),
        BlocBuilder<AuthBloc, AuthState>(
          builder: (context, state) {
            if (state is AuthPackagesLoading) return const Center(child: CircularProgressIndicator());
            if (state is AuthPackagesLoadSuccess) {
              return Container(
                decoration: BoxDecoration(border: Border.all(color: Colors.grey.shade400), borderRadius: BorderRadius.circular(8)),
                child: ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: state.packages.length,
                  itemBuilder: (context, index) {
                    final package = state.packages[index];
                    return RadioListTile<int>(
                      title: Text(package.name),
                      subtitle: Text('${package.adQuota} ads - Rp. ${NumberFormat.decimalPattern('id_ID').format(double.tryParse(package.price) ?? 0)}'),
                      value: package.id,
                      groupValue: _selectedPackageId,
                      onChanged: (value) => setState(() => _selectedPackageId = value),
                    );
                  },
                  separatorBuilder: (context, index) => const Divider(height: 1, thickness: 1),
                ),
              );
            }
            return const Text('Could not load packages.');
          },
        ),
      ],
    );
  }

  Widget _buildImagePicker() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Upload Payment Proof:', style: Theme.of(context).textTheme.titleMedium),
        const SizedBox(height: 8),
        Container(
          height: 150,
          width: double.infinity,
          decoration: BoxDecoration(
            border: Border.all(color: Colors.grey.shade400),
            borderRadius: BorderRadius.circular(8),
          ),
          child: _paymentProofImage != null
              ? Image.file(_paymentProofImage!, fit: BoxFit.cover)
              : const Center(child: Text('No image selected.')),
        ),
        const SizedBox(height: 8),
        TextButton.icon(
          icon: const Icon(Icons.upload_file),
          label: const Text('Select Image'),
          onPressed: _pickImage,
        ),
      ],
    );
  }
}

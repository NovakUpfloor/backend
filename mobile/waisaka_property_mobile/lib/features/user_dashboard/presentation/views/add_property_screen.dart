import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import 'package:waisaka_property_mobile/core/di/service_locator.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/presentation/bloc/add_property_bloc.dart';

class AddPropertyScreen extends StatelessWidget {
  const AddPropertyScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => sl<AddPropertyBloc>(),
      child: const _AddPropertyView(),
    );
  }
}

class _AddPropertyView extends StatefulWidget {
  const _AddPropertyView();

  @override
  State<_AddPropertyView> createState() => _AddPropertyViewState();
}

class _AddPropertyViewState extends State<_AddPropertyView> {
  final _formKey = GlobalKey<FormState>();
  final _controllers = {
    'nama_property': TextEditingController(),
    'harga': TextEditingController(),
    'lt': TextEditingController(),
    'lb': TextEditingController(),
    'kamar_tidur': TextEditingController(),
    'kamar_mandi': TextEditingController(),
    'lantai': TextEditingController(),
    'alamat': TextEditingController(),
    'isi': TextEditingController(),
  };

  // Example values, these should be fetched from an API
  String _selectedTipe = 'Jual';
  String _selectedSurat = 'SHM';
  int? _selectedKategori;
  int? _selectedProvinsi;
  int? _selectedKabupaten;
  int? _selectedKecamatan;

  List<File> _propertyImages = [];
  final ImagePicker _picker = ImagePicker();

  @override
  void dispose() {
    _controllers.forEach((_, controller) => controller.dispose());
    super.dispose();
  }

  Future<void> _pickImages() async {
    final List<XFile> pickedFiles = await _picker.pickMultiImage();
    if (pickedFiles.isNotEmpty) {
      setState(() {
        _propertyImages.addAll(pickedFiles.map((file) => File(file.path)));
      });
    }
  }

  void _submit() {
    if (_formKey.currentState!.validate() && _propertyImages.isNotEmpty) {
      final propertyData = {
        'nama_property': _controllers['nama_property']!.text,
        'harga': _controllers['harga']!.text,
        'lt': _controllers['lt']!.text,
        'lb': _controllers['lb']!.text,
        'kamar_tidur': _controllers['kamar_tidur']!.text,
        'kamar_mandi': _controllers['kamar_mandi']!.text,
        'lantai': _controllers['lantai']!.text,
        'alamat': _controllers['alamat']!.text,
        'isi': _controllers['isi']!.text,
        'tipe': _selectedTipe,
        'surat': _selectedSurat,
        'id_kategori_property': _selectedKategori ?? 1,
        'id_provinsi': _selectedProvinsi ?? 1,
        'id_kabupaten': _selectedKabupaten ?? 1,
        'id_kecamatan': _selectedKecamatan ?? 1,
      };

      context.read<AddPropertyBloc>().add(SubmitAddProperty(
        propertyData: propertyData,
        images: _propertyImages,
      ));
    } else if (_propertyImages.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please select at least one image.')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Add New Property')),
      body: BlocListener<AddPropertyBloc, AddPropertyState>(
        listener: (context, state) {
          if (state is AddPropertySuccess) {
            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Property added successfully!')));
            context.pop();
          }
          if (state is AddPropertyFailure) {
            ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: ${state.error}')));
          }
        },
        child: Form(
          key: _formKey,
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                ..._buildFormFields(),
                const SizedBox(height: 24),
                _buildImagePicker(),
                const SizedBox(height: 32),
                BlocBuilder<AddPropertyBloc, AddPropertyState>(
                  builder: (context, state) {
                    if (state is AddPropertyLoading) {
                      return const Center(child: CircularProgressIndicator());
                    }
                    return ElevatedButton(onPressed: _submit, child: const Text('Submit Property'));
                  },
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  List<Widget> _buildFormFields() {
    return [
      TextFormField(controller: _controllers['nama_property'], decoration: const InputDecoration(labelText: 'Property Name')),
      TextFormField(controller: _controllers['harga'], decoration: const InputDecoration(labelText: 'Price'), keyboardType: TextInputType.number),
      // Add Dropdowns for categorical data like tipe, surat, kategori, lokasi etc.
      // For simplicity, we are using default values for now.
      TextFormField(controller: _controllers['lt'], decoration: const InputDecoration(labelText: 'Land Area (m²)'), keyboardType: TextInputType.number),
      TextFormField(controller: _controllers['lb'], decoration: const InputDecoration(labelText: 'Building Area (m²)'), keyboardType: TextInputType.number),
      TextFormField(controller: _controllers['kamar_tidur'], decoration: const InputDecoration(labelText: 'Bedrooms'), keyboardType: TextInputType.number),
      TextFormField(controller: _controllers['kamar_mandi'], decoration: const InputDecoration(labelText: 'Bathrooms'), keyboardType: TextInputType.number),
      TextFormField(controller: _controllers['lantai'], decoration: const InputDecoration(labelText: 'Floors'), keyboardType: TextInputType.number),
      TextFormField(controller: _controllers['alamat'], decoration: const InputDecoration(labelText: 'Address')),
      TextFormField(controller: _controllers['isi'], decoration: const InputDecoration(labelText: 'Description'), maxLines: 4),
    ];
  }

  Widget _buildImagePicker() {
    return Column(
      children: [
        ElevatedButton.icon(onPressed: _pickImages, icon: const Icon(Icons.image), label: const Text('Select Images')),
        const SizedBox(height: 8),
        Wrap(
          spacing: 8.0,
          runSpacing: 8.0,
          children: _propertyImages.map((image) => Image.file(image, width: 100, height: 100, fit: BoxFit.cover)).toList(),
        )
      ],
    );
  }
}

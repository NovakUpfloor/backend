import 'package:waisaka_property_mobile/features/property/data/models/agent.dart';

class Property {
  final int id;
  final String namaProperty;
  final String? deskripsi;
  final String? tipe;
  final double? harga;
  final int? lt; // Luas Tanah
  final int? lb; // Luas Bangunan
  final int? kamarTidur;
  final int? kamarMandi;
  final String? namaProvinsi;
  final String? namaKabupaten;
  final String? gambar; // Primary image
  final List<String> gallery;
  final Agent? agent;

  Property({
    required this.id,
    required this.namaProperty,
    this.deskripsi,
    this.tipe,
    this.harga,
    this.lt,
    this.lb,
    this.kamarTidur,
    this.kamarMandi,
    this.namaProvinsi,
    this.namaKabupaten,
    this.gambar,
    required this.gallery,
    this.agent,
  });

  factory Property.fromJson(Map<String, dynamic> json) {
    final propertyData = json['property'] ?? {};
    final imagesData = json['images'] as List<dynamic>? ?? [];
    final agentData = json['agent'] as Map<String, dynamic>?;

    double? parseDouble(dynamic value) {
      if (value is double) return value;
      if (value is int) return value.toDouble();
      if (value is String) return double.tryParse(value);
      return null;
    }

    int? parseInt(dynamic value) {
      if (value is int) return value;
      if (value is String) return int.tryParse(value);
      return null;
    }

    return Property(
      id: propertyData['id_property'],
      namaProperty: propertyData['nama_property'] ?? 'No Title',
      deskripsi: propertyData['isi'],
      tipe: propertyData['tipe'],
      harga: parseDouble(propertyData['harga']),
      lt: parseInt(propertyData['lt']),
      lb: parseInt(propertyData['lb']),
      kamarTidur: parseInt(propertyData['kamar_tidur']),
      kamarMandi: parseInt(propertyData['kamar_mandi']),
      namaProvinsi: propertyData['nama_provinsi'],
      namaKabupaten: propertyData['nama_kabupaten'],
      gambar: propertyData['gambar'],
      gallery: List<String>.from(imagesData),
      agent: agentData != null ? Agent.fromJson(agentData) : null,
    );
  }
}

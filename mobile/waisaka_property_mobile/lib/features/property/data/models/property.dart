import 'package:flutter/foundation.dart';

class Property {
  final int id;
  final String namaProperty;
  final String? tipe;
  final double? harga;
  final int? lt; // Luas Tanah
  final int? lb; // Luas Bangunan
  final int? kamarTidur;
  final int? kamarMandi;
  final String? namaProvinsi;
  final String? namaKabupaten;
  final String? gambar; // Assuming the API provides a primary image URL

  Property({
    required this.id,
    required this.namaProperty,
    this.tipe,
    this.harga,
    this.lt,
    this.lb,
    this.kamarTidur,
    this.kamarMandi,
    this.namaProvinsi,
    this.namaKabupaten,
    this.gambar,
  });

  factory Property.fromJson(Map<String, dynamic> json) {
    // Helper to safely parse numbers that might be strings or null
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
      id: json['id_property'],
      namaProperty: json['nama_property'] ?? 'No Title',
      tipe: json['tipe'],
      harga: parseDouble(json['harga']),
      lt: parseInt(json['lt']),
      lb: parseInt(json['lb']),
      kamarTidur: parseInt(json['kamar_tidur']),
      kamarMandi: parseInt(json['kamar_mandi']),
      namaProvinsi: json['nama_provinsi'],
      namaKabupaten: json['nama_kabupaten'],
      gambar: json['gambar'], // Assuming 'gambar' is the key for the image URL
    );
  }
}

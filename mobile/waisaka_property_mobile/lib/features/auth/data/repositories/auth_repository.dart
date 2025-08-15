import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:waisaka_property_mobile/core/api/api_client.dart';
import 'package:waisaka_property_mobile/features/auth/data/models/user.dart';

// Define the Package model here for simplicity
class Package {
  final int id;
  final String name;
  final String price;
  final int adQuota;
  final String? description;

  Package({
    required this.id,
    required this.name,
    required this.price,
    required this.adQuota,
    this.description,
  });

  factory Package.fromJson(Map<String, dynamic> json) {
    return Package(
      id: json['id'],
      name: json['nama_paket'],
      price: json['harga'],
      adQuota: json['kuota_iklan'],
      description: json['deskripsi'],
    );
  }
}


class AuthRepository {
  final ApiClient _apiClient;
  final FlutterSecureStorage _secureStorage;

  AuthRepository({
    required ApiClient apiClient,
    required FlutterSecureStorage secureStorage,
  })  : _api_client = apiClient,
        _secureStorage = secureStorage;

  Future<List<Package>> fetchPackages() async {
    try {
      final response = await _apiClient.get('/packages');
      final List<dynamic> packageList = response.data['data'];
      return packageList.map((json) => Package.fromJson(json)).toList();
    } catch (e) {
      debugPrint('Failed to fetch packages: $e');
      rethrow;
    }
  }

  Future<User> login({
    required String username,
    required String password,
  }) async {
    try {
      final response = await _apiClient.post(
        '/auth/login',
        data: {'username': username, 'password': password},
      );

      final token = response.data['token'] as String;
      final user = User.fromJson(response.data['user']);

      await _secureStorage.write(key: 'auth_token', value: token);

      return user;
    } catch (e) {
      debugPrint('Login failed: $e');
      rethrow;
    }
  }

  Future<User> getUserProfile() async {
    try {
      final response = await _apiClient.get('/dashboard/profile');
      return User.fromJson(response.data['data']);
    } catch (e) {
      debugPrint('Failed to get user profile: $e');
      rethrow;
    }
  }

  Future<void> register({
    required String name,
    required String username,
    required String email,
    required String password,
    int? packageId, // packageId is optional for now
  }) async {
    try {
      await _apiClient.post(
        '/auth/register',
        data: {
          'nama': name,
          'username': username,
          'email': email,
          'password': password,
          'paket_id': packageId,
        },
      );
    } catch (e) {
      debugPrint('Registration failed: $e');
      rethrow;
    }
  }

  Future<void> logout() async {
    // Also call the API to invalidate the token on the server
    try {
      await _apiClient.post('/auth/logout');
    } catch (e) {
      debugPrint('Failed to logout from server: $e');
    } finally {
      await _secureStorage.delete(key: 'auth_token');
    }
  }

  Future<String?> getToken() async {
    return await _secureStorage.read(key: 'auth_token');
  }
}

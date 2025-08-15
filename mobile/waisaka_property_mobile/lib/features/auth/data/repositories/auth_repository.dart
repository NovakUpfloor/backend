import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:waisaka_property_mobile/core/api/api__client.dart';
import 'package:waisaka_property_mobile/features/auth/data/models/user.dart';

class AuthRepository {
  final ApiClient _apiClient;
  final FlutterSecureStorage _secureStorage;

  AuthRepository({
    required ApiClient apiClient,
    required FlutterSecureStorage secureStorage,
  })  : _apiClient = apiClient,
        _secureStorage = secureStorage;

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
      final response = await _apiClient.get('/user-panel/profile');
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
    required int packageId,
    required String paymentProofPath,
  }) async {
    try {
      String fileName = paymentProofPath.split('/').last;
      FormData formData = FormData.fromMap({
        'nama': name,
        'username': username,
        'email': email,
        'password': password,
        'paket_id': packageId,
        'bukti_pembayaran': await MultipartFile.fromFile(paymentProofPath, filename: fileName),
      });

      await _apiClient.post(
        '/auth/register',
        data: formData,
      );
    } catch (e) {
      debugPrint('Registration failed: $e');
      rethrow;
    }
  }

  Future<void> logout() async {
    await _secureStorage.delete(key: 'auth_token');
    // TODO: Call API logout to invalidate token on server
  }

  Future<String?> getToken() async {
    return await _secureStorage.read(key: 'auth_token');
  }
}

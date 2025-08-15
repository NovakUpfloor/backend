import 'package:flutter/foundation.dart';
import 'package:waisaka_property_mobile/core/api/api_client.dart';
import 'package:waisaka_property_mobile/features/property/data/models/property.dart';

class DashboardRepository {
  final ApiClient _apiClient;

  DashboardRepository({required ApiClient apiClient}) : _apiClient = apiClient;

  Future<List<Property>> fetchMyProperties() async {
    try {
      final response = await _apiClient.get('/dashboard/my-properties');
      final List<dynamic> propertyList = response.data['data'];
      // The property model might need adjustment if the API returns different fields
      return propertyList.map((json) => Property.fromJson(json)).toList();
    } catch (e) {
      debugPrint('Failed to fetch my properties: $e');
      rethrow;
    }
  }

  Future<void> purchasePackage({
    required int packageId,
    required String whatsappNumber,
    required File paymentProof,
  }) async {
    try {
      String fileName = paymentProof.path.split('/').last;
      FormData formData = FormData.fromMap({
        "paket_id": packageId,
        "nomor_whatsapp": whatsappNumber,
        "bukti_pembayaran": await MultipartFile.fromFile(
          paymentProof.path,
          filename: fileName,
        ),
      });

      await _apiClient.postMultipart(
        '/dashboard/purchase-package',
        data: formData,
      );
    } catch (e) {
      debugPrint('Failed to submit package purchase: $e');
      rethrow;
    }
  }

  Future<void> addProperty({
    required Map<String, dynamic> propertyData,
    required List<File> images,
  }) async {
    try {
      final formData = FormData.fromMap(propertyData);
      for (var image in images) {
        formData.files.addAll([
          MapEntry(
            'property_images[]',
            await MultipartFile.fromFile(image.path),
          ),
        ]);
      }

      await _apiClient.postMultipart(
        '/dashboard/property',
        data: formData,
      );
    } catch (e) {
      debugPrint('Failed to add property: $e');
      rethrow;
    }
  }
}

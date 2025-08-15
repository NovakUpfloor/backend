import 'package:waisaka_property_mobile/core/api/api_client.dart';
import 'package:waisaka_property_mobile/features/auth/data/models/package.dart';

class PackageRepository {
  final ApiClient _apiClient;

  PackageRepository({required ApiClient apiClient}) : _apiClient = apiClient;

  Future<List<AdPackage>> fetchPackages() async {
    try {
      final response = await _apiClient.get('/packages');
      final List<dynamic> data = response.data['data'];
      return data.map((json) => AdPackage.fromJson(json)).toList();
    } catch (e) {
      rethrow;
    }
  }
}

import 'package:waisaka_property_mobile/core/api/api_client.dart';
import 'package:waisaka_property_mobile/features/property/data/models/property.dart';

class PropertyRepository {
  final ApiClient _apiClient;

  PropertyRepository({required ApiClient apiClient}) : _apiClient = apiClient;

  Future<List<Property>> fetchProperties() async {
    try {
      final response = await _apiClient.get('/properties');
      // The API returns paginated data, we need to access the 'data' key
      final List<dynamic> data = response.data['data']['data'];
      return data.map((json) => Property.fromJson(json)).toList();
    } catch (e) {
      rethrow;
    }
  }

  Future<Property> fetchPropertyById(String id) async {
    try {
      final response = await _apiClient.get('/properties/$id');
      return Property.fromJson(response.data['data']);
    } catch (e) {
      rethrow;
    }
  }
}

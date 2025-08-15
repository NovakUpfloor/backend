import 'package:waisaka_property_mobile/core/api/api_client.dart';
import 'package:waisaka_property_mobile/features/property/data/models/property.dart';

class PropertyRepository {
  final ApiClient _apiClient;

  PropertyRepository({required ApiClient apiClient}) : _apiClient = apiClient;

  Future<List<Property>> fetchProperties() async {
    try {
      final response = await _apiClient.get('/properties');
      final List<dynamic> propertyList = response.data['data'];
      return propertyList.map((json) => Property.fromJson(json)).toList();
    } catch (e) {
      rethrow;
    }
  }

  Future<Property> fetchPropertyDetails(String id) async {
    try {
      final response = await _apiClient.get('/properties/$id');
      // The API likely returns the property object directly or nested under a 'data' key.
      // Assuming it's under 'data' based on the list endpoint.
      return Property.fromJson(response.data['data']);
    } catch (e) {
      rethrow;
    }
  }
}

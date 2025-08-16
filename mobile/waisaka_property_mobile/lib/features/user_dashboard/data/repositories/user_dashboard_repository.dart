import 'package:waisaka_property_mobile/core/api/api_client.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/data/models/purchase_history.dart';

class UserDashboardRepository {
  final ApiClient _apiClient;

  UserDashboardRepository({required ApiClient apiClient}) : _apiClient = apiClient;

  Future<List<PurchaseHistory>> fetchPurchaseHistory() async {
    try {
      final response = await _apiClient.get('/user-panel/purchases');
      final List<dynamic> data = response.data['data'];
      return data.map((json) => PurchaseHistory.fromJson(json)).toList();
    } catch (e) {
      rethrow;
    }
  }
}

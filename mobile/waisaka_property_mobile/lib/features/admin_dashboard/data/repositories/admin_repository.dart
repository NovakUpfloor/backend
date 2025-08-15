import 'package:waisaka_property_mobile/core/api/api_client.dart';
import 'package:waisaka_property_mobile/features/admin_dashboard/data/models/member.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/data/models/purchase_history.dart';

class AdminRepository {
  final ApiClient _apiClient;

  AdminRepository({required ApiClient apiClient}) : _apiClient = apiClient;

  Future<List<Member>> fetchMembers() async {
    try {
      final response = await _apiClient.get('/admin-panel/agents');
      final List<dynamic> memberList = response.data['data'];
      return memberList.map((json) => Member.fromJson(json)).toList();
    } catch (e) {
      rethrow;
    }
  }

  Future<List<PurchaseHistory>> fetchPurchaseConfirmations() async {
    try {
      final response = await _apiClient.get('/admin-panel/purchase-confirmations');
      final List<dynamic> data = response.data['data'];
      return data.map((json) => PurchaseHistory.fromJson(json)).toList();
    } catch (e) {
      rethrow;
    }
  }

  Future<void> updatePurchaseStatus(int transactionId, String status) async {
    try {
      await _apiClient.post(
        '/admin-panel/purchase-confirmations/$transactionId/update-status',
        data: {'status': status},
      );
    } catch (e) {
      rethrow;
    }
  }
}

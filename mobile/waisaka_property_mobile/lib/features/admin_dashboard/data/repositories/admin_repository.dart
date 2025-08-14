import 'package:waisaka_property_mobile/core/api/api_client.dart';
import 'package:waisaka_property_mobile/features/admin_dashboard/data/models/member.dart';

class AdminRepository {
  final ApiClient _apiClient;

  AdminRepository({required ApiClient apiClient}) : _apiClient = apiClient;

  Future<List<Member>> fetchMembers() async {
    try {
      final response = await _apiClient.get('/admin/members');
      final List<dynamic> memberList = response.data['data'];
      return memberList.map((json) => Member.fromJson(json)).toList();
    } catch (e) {
      rethrow;
    }
  }

  // TODO: Add methods for approving activations, updating status, etc.
}

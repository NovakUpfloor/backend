import 'package:waisaka_property_mobile/core/api/api_client.dart';
import 'package:waisaka_property_mobile/features/article/data/models/article.dart';

class ArticleRepository {
  final ApiClient _apiClient;

  ArticleRepository({required ApiClient apiClient}) : _apiClient = apiClient;

  Future<List<Article>> fetchArticles() async {
    try {
      final response = await _apiClient.get('/articles');
       // The API returns paginated data, we need to access the 'data' key
      final List<dynamic> data = response.data['data']['data'];
      return data.map((json) => Article.fromJson(json)).toList();
    } catch (e) {
      rethrow;
    }
  }
}

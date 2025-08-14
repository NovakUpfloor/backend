import 'package:waisaka_property_mobile/core/api/api_client.dart';
import 'package:waisaka_property_mobile/features/article/data/models/article.dart';

class ArticleRepository {
  final ApiClient _apiClient;

  ArticleRepository({required ApiClient apiClient}) : _apiClient = apiClient;

  Future<List<Article>> fetchArticles() async {
    try {
      final response = await _apiClient.get('/articles');
      final List<dynamic> articleList = response.data['data'];
      return articleList.map((json) => Article.fromJson(json)).toList();
    } catch (e) {
      rethrow;
    }
  }
}

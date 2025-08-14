import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:waisaka_property_mobile/features/article/data/models/article.dart';
import 'package:waisaka_property_mobile/features/article/data/repositories/article_repository.dart';
import 'package:waisaka_property_mobile/features/property/data/models/property.dart';
import 'package:waisaka_property_mobile/features/property/data/repositories/property_repository.dart';

part 'home_event.dart';
part 'home_state.dart';

class HomeBloc extends Bloc<HomeEvent, HomeState> {
  final PropertyRepository _propertyRepository;
  final ArticleRepository _articleRepository;

  HomeBloc({
    required PropertyRepository propertyRepository,
    required ArticleRepository articleRepository,
  })  : _propertyRepository = propertyRepository,
        _articleRepository = articleRepository,
        super(HomeInitial()) {
    on<HomeDataFetched>(_onHomeDataFetched);
  }

  Future<void> _onHomeDataFetched(
    HomeDataFetched event,
    Emitter<HomeState> emit,
  ) async {
    emit(HomeLoading());
    try {
      // Fetch properties and articles concurrently
      final results = await Future.wait([
        _propertyRepository.fetchProperties(),
        _articleRepository.fetchArticles(),
      ]);

      final properties = results[0] as List<Property>;
      final articles = results[1] as List<Article>;

      emit(HomeLoadSuccess(properties: properties, articles: articles));
    } catch (e) {
      emit(HomeLoadFailure(error: e.toString()));
    }
  }
}

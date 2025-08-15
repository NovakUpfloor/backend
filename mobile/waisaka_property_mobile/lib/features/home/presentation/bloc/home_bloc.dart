import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:waisaka_property_mobile/features/article/data/models/article.dart';
import 'package:waisaka_property_mobile/features/article/data/repositories/article_repository.dart';
import 'package:waisaka_property_mobile/features/gemini/data/repositories/gemini_repository.dart';
import 'package:waisaka_property_mobile/features/property/data/models/property.dart';
import 'package:waisaka_property_mobile/features/property/data/repositories/property_repository.dart';

part 'home_event.dart';
part 'home_state.dart';

class HomeBloc extends Bloc<HomeEvent, HomeState> {
  final PropertyRepository _propertyRepository;
  final ArticleRepository _articleRepository;
  final GeminiRepository _geminiRepository;

  HomeBloc({
    required PropertyRepository propertyRepository,
    required ArticleRepository articleRepository,
    required GeminiRepository geminiRepository,
  })  : _propertyRepository = propertyRepository,
        _articleRepository = articleRepository,
        _geminiRepository = geminiRepository,
        super(HomeInitial()) {
    on<HomeDataFetched>(_onHomeDataFetched);
    on<HomeVoiceCommandReceived>(_onHomeVoiceCommandReceived);
  }

  Future<void> _onHomeDataFetched(
    HomeDataFetched event,
    Emitter<HomeState> emit,
  ) async {
    emit(HomeLoading());
    try {
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

  Future<void> _onHomeVoiceCommandReceived(
    HomeVoiceCommandReceived event,
    Emitter<HomeState> emit,
  ) async {
    try {
      final responseString = await _geminiRepository.sendCommand(event.command, 'home_search');
      final responseJson = jsonDecode(responseString) as Map<String, dynamic>;

      if (responseJson['action'] == 'search') {
        emit(HomeNavigateToSearch(
          location: responseJson['location'],
          type: responseJson['type'],
        ));
      } else {
        // Optionally handle the 'unknown' action, e.g., show a message
      }
    } catch (e) {
      emit(HomeLoadFailure(error: 'Failed to process voice command: $e'));
    }
  }
}

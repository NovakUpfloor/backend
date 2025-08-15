part of 'home_bloc.dart';

@immutable
abstract class HomeState {}

class HomeInitial extends HomeState {}

class HomeLoading extends HomeState {}

class HomeLoadSuccess extends HomeState {
  final List<Property> properties;
  final List<Article> articles;

  HomeLoadSuccess({
    required this.properties,
    required this.articles,
  });
}

class HomeLoadFailure extends HomeState {
  final String error;

  HomeLoadFailure({required this.error});
}

class HomeNavigateToSearch extends HomeState {
  final String query;

  HomeNavigateToSearch({required this.query});
}

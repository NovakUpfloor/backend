part of 'home_bloc.dart';

@immutable
abstract class HomeEvent {}

class HomeDataFetched extends HomeEvent {}

class HomeVoiceCommandReceived extends HomeEvent {
  final String command;
  HomeVoiceCommandReceived(this.command);
}

part of 'home_bloc.dart';

@immutable
abstract class HomeEvent {}

class HomeDataFetched extends HomeEvent {}

class VoiceCommandSubmitted extends HomeEvent {
  final String command;

  VoiceCommandSubmitted(this.command);
}

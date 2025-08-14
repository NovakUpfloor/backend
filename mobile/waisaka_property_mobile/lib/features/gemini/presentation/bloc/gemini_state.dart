part of 'gemini_bloc.dart';

@immutable
abstract class GeminiState {}

class GeminiInitial extends GeminiState {}

class GeminiLoading extends GeminiState {}

// For when Gemini returns a structured action to be performed by the app
class GeminiActionSuccess extends GeminiState {
  final Map<String, dynamic> action; // e.g., {'action': 'contact_whatsapp'}

  GeminiActionSuccess(this.action);
}

// For when Gemini returns a simple text response
class GeminiTextSuccess extends GeminiState {
  final String response;

  GeminiTextSuccess(this.response);
}

class GeminiFailure extends GeminiState {
  final String error;

  GeminiFailure(this.error);
}

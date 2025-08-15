import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:waisaka_property_mobile/features/gemini/data/repositories/gemini_repository.dart';

part 'gemini_event.dart';
part 'gemini_state.dart';

class GeminiBloc extends Bloc<GeminiEvent, GeminiState> {
  final GeminiRepository _geminiRepository;

  GeminiBloc({required GeminiRepository geminiRepository})
      : _geminiRepository = geminiRepository,
        super(GeminiInitial()) {
    on<SendCommandToGemini>(_onSendCommand);
  }

  Future<void> _onSendCommand(
    SendCommandToGemini event,
    Emitter<GeminiState> emit,
  ) async {
    emit(GeminiLoading());
    try {
      final responseText = await _geminiRepository.sendCommand(
        event.textCommand,
        event.pageContext,
      );

      if (responseText.trim().startsWith('{')) {
        // Try to parse as JSON for actions
        try {
          final actionJson = jsonDecode(responseText) as Map<String, dynamic>;
          emit(GeminiActionSuccess(actionJson));
        } catch (e) {
          // If JSON parsing fails, treat it as a text response
          emit(GeminiTextSuccess(responseText));
        }
      } else {
        // If it's not JSON, it's a text response
        emit(GeminiTextSuccess(responseText));
      }
    } catch (e) {
      emit(GeminiFailure(e.toString()));
    }
  }
}

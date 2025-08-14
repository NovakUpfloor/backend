import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:google_generative_ai/google_generative_ai.dart';

class GeminiRepository {
  // IMPORTANT: API key should be stored securely and not hardcoded.
  // Using a placeholder for now.
  static const String _apiKey = 'YOUR_API_KEY_HERE';

  final GenerativeModel _model;

  GeminiRepository()
      : _model = GenerativeModel(
          model: 'gemini-pro',
          apiKey: _apiKey,
        );

  String _getSystemPrompt(String pageContext) {
    switch (pageContext) {
      case 'property_detail':
        return '''
          You are a property assistant. The user is viewing a specific property.
          Your task is to understand user commands and respond ONLY with a valid JSON object.
          Valid commands are related to contacting an agent or sharing the property.
          - If the user wants to contact via WhatsApp, respond with: {"action": "contact_whatsapp"}
          - If the user wants to call by phone, respond with: {"action": "contact_phone"}
          - If the user wants to share to Facebook, respond with: {"action": "share_facebook"}
          - If the user wants to share to WhatsApp, respond with: {"action": "share_whatsapp"}
          If the command is not one of these, respond with: {"action": "unknown"}
        ''';
      case 'home_search':
        return '''
          You are a friendly property search assistant for Waisaka Property.
          Answer questions about finding properties.
          If the user asks about anything other than property search, politely decline.
        ''';
      default:
        return 'You are a helpful assistant.';
    }
  }

  Future<String> sendCommand(String textCommand, String pageContext) async {
    final systemPrompt = _getSystemPrompt(pageContext);
    final fullPrompt = '$systemPrompt\n\nUser Command: "$textCommand"';

    try {
      final response = await _model.generateContent([
        Content.text(fullPrompt),
      ]);

      // TODO: Log token usage to our backend API
      debugPrint('Gemini Response: ${response.text}');
      return response.text ?? '';
    } catch (e) {
      debugPrint('Error calling Gemini API: $e');
      rethrow;
    }
  }
}

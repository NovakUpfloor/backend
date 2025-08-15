import 'package:flutter/material.dart';
import 'package:waisaka_property_mobile/app/app.dart';
import 'package:waisaka_property_mobile/core/di/service_locator.dart';

void main() {
  // Setup dependency injection before running the app
  setupServiceLocator();

  runApp(const MyApp());
}

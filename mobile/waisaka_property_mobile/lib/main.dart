import 'package:flutter/material.dart';
import 'package:waisaka_property_mobile/app/app.dart';
import 'package:waisaka_property_mobile/core/di/service_locator.dart';

void main() {
  // Setup service locator for dependency injection
  setupServiceLocator();

  runApp(const MyApp());
}

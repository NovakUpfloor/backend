part of 'add_property_bloc.dart';

@immutable
abstract class AddPropertyEvent {}

class SubmitAddProperty extends AddPropertyEvent {
  final Map<String, dynamic> propertyData;
  final List<File> images;

  SubmitAddProperty({required this.propertyData, required this.images});
}

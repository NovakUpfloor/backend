part of 'my_properties_bloc.dart';

@immutable
abstract class MyPropertiesState {}

class MyPropertiesInitial extends MyPropertiesState {}

class MyPropertiesLoading extends MyPropertiesState {}

class MyPropertiesLoadSuccess extends MyPropertiesState {
  final List<Property> properties;
  MyPropertiesLoadSuccess({required this.properties});
}

class MyPropertiesLoadFailure extends MyPropertiesState {
  final String error;
  MyPropertiesLoadFailure({required this.error});
}

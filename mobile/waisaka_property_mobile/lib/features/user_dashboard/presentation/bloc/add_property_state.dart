part of 'add_property_bloc.dart';

@immutable
abstract class AddPropertyState {}

class AddPropertyInitial extends AddPropertyState {}

class AddPropertyLoading extends AddPropertyState {}

class AddPropertySuccess extends AddPropertyState {}

class AddPropertyFailure extends AddPropertyState {
  final String error;
  AddPropertyFailure({required this.error});
}

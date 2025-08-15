part of 'property_detail_bloc.dart';

@immutable
abstract class PropertyDetailState {}

class PropertyDetailInitial extends PropertyDetailState {}

class PropertyDetailLoading extends PropertyDetailState {}

class PropertyDetailLoadSuccess extends PropertyDetailState {
  final Property property; // We'll need a more detailed model later

  PropertyDetailLoadSuccess(this.property);
}

class PropertyDetailLoadFailure extends PropertyDetailState {
  final String error;

  PropertyDetailLoadFailure(this.error);
}

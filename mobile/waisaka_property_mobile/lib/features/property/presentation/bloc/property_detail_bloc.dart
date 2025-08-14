import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:waisaka_property_mobile/features/property/data/models/property.dart';
import 'package:waisaka_property_mobile/features/property/data/repositories/property_repository.dart';

part 'property_detail_event.dart';
part 'property_detail_state.dart';

class PropertyDetailBloc
    extends Bloc<PropertyDetailEvent, PropertyDetailState> {
  final PropertyRepository _propertyRepository;

  PropertyDetailBloc({required PropertyRepository propertyRepository})
      : _propertyRepository = propertyRepository,
        super(PropertyDetailInitial()) {
    on<FetchPropertyDetails>(_onFetchPropertyDetails);
  }

  Future<void> _onFetchPropertyDetails(
    FetchPropertyDetails event,
    Emitter<PropertyDetailState> emit,
  ) async {
    emit(PropertyDetailLoading());
    try {
      final property = await _propertyRepository.fetchPropertyDetails(event.propertyId);
      emit(PropertyDetailLoadSuccess(property));
    } catch (e) {
      emit(PropertyDetailLoadFailure(e.toString()));
    }
  }
}

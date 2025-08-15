import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/data/repositories/dashboard_repository.dart';

part 'add_property_event.dart';
part 'add_property_state.dart';

class AddPropertyBloc extends Bloc<AddPropertyEvent, AddPropertyState> {
  final DashboardRepository _dashboardRepository;

  AddPropertyBloc({required DashboardRepository dashboardRepository})
      : _dashboardRepository = dashboardRepository,
        super(AddPropertyInitial()) {
    on<SubmitAddProperty>(_onSubmitAddProperty);
  }

  Future<void> _onSubmitAddProperty(
    SubmitAddProperty event,
    Emitter<AddPropertyState> emit,
  ) async {
    emit(AddPropertyLoading());
    try {
      await _dashboardRepository.addProperty(
        propertyData: event.propertyData,
        images: event.images,
      );
      emit(AddPropertySuccess());
    } catch (e) {
      emit(AddPropertyFailure(error: e.toString()));
    }
  }
}

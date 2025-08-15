import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:waisaka_property_mobile/features/property/data/models/property.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/data/repositories/dashboard_repository.dart';

part 'my_properties_event.dart';
part 'my_properties_state.dart';

class MyPropertiesBloc extends Bloc<MyPropertiesEvent, MyPropertiesState> {
  final DashboardRepository _dashboardRepository;

  MyPropertiesBloc({required DashboardRepository dashboardRepository})
      : _dashboardRepository = dashboardRepository,
        super(MyPropertiesInitial()) {
    on<FetchMyPropertiesList>(_onFetchMyProperties);
  }

  Future<void> _onFetchMyProperties(
    FetchMyPropertiesList event,
    Emitter<MyPropertiesState> emit,
  ) async {
    emit(MyPropertiesLoading());
    try {
      final properties = await _dashboardRepository.fetchMyProperties();
      emit(MyPropertiesLoadSuccess(properties: properties));
    } catch (e) {
      emit(MyPropertiesLoadFailure(error: e.toString()));
    }
  }
}

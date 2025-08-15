import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/data/repositories/dashboard_repository.dart';

part 'purchase_bloc_event.dart';
part 'purchase_bloc_state.dart';

class PurchaseBloc extends Bloc<PurchaseEvent, PurchaseState> {
  final DashboardRepository _dashboardRepository;

  PurchaseBloc({required DashboardRepository dashboardRepository})
      : _dashboardRepository = dashboardRepository,
        super(PurchaseInitial()) {
    on<SubmitPurchase>(_onSubmitPurchase);
  }

  Future<void> _onSubmitPurchase(
    SubmitPurchase event,
    Emitter<PurchaseState> emit,
  ) async {
    emit(PurchaseLoading());
    try {
      await _dashboardRepository.purchasePackage(
        packageId: event.packageId,
        whatsappNumber: event.whatsappNumber,
        paymentProof: event.paymentProof,
      );
      emit(PurchaseSuccess());
    } catch (e) {
      emit(PurchaseFailure(error: e.toString()));
    }
  }
}

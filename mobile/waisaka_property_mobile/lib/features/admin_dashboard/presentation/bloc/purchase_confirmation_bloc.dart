import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:waisaka_property_mobile/features/admin_dashboard/data/repositories/admin_repository.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/data/models/purchase_history.dart';

part 'purchase_confirmation_event.dart';
part 'purchase_confirmation_state.dart';

class PurchaseConfirmationBloc extends Bloc<PurchaseConfirmationEvent, PurchaseConfirmationState> {
  final AdminRepository _repository;

  PurchaseConfirmationBloc({required AdminRepository repository})
      : _repository = repository,
        super(PurchaseConfirmationInitial()) {
    on<FetchConfirmations>(_onFetchConfirmations);
    on<UpdateConfirmationStatus>(_onUpdateStatus);
  }

  Future<void> _onFetchConfirmations(
    FetchConfirmations event,
    Emitter<PurchaseConfirmationState> emit,
  ) async {
    emit(PurchaseConfirmationLoading());
    try {
      final confirmations = await _repository.fetchPurchaseConfirmations();
      emit(PurchaseConfirmationLoadSuccess(confirmations: confirmations));
    } catch (e) {
      emit(PurchaseConfirmationLoadFailure(error: e.toString()));
    }
  }

  Future<void> _onUpdateStatus(
    UpdateConfirmationStatus event,
    Emitter<PurchaseConfirmationState> emit,
  ) async {
    emit(PurchaseConfirmationUpdateInProgress());
    try {
      await _repository.updatePurchaseStatus(event.transactionId, event.status);
      emit(PurchaseConfirmationUpdateSuccess());
      // Fetch the list again to show the updated data
      add(FetchConfirmations());
    } catch (e) {
      emit(PurchaseConfirmationUpdateFailure(error: e.toString()));
    }
  }
}

part of 'purchase_confirmation_bloc.dart';

@immutable
abstract class PurchaseConfirmationState {}

class PurchaseConfirmationInitial extends PurchaseConfirmationState {}

class PurchaseConfirmationLoading extends PurchaseConfirmationState {}

class PurchaseConfirmationLoadSuccess extends PurchaseConfirmationState {
  final List<PurchaseHistory> confirmations;
  PurchaseConfirmationLoadSuccess({required this.confirmations});
}

class PurchaseConfirmationLoadFailure extends PurchaseConfirmationState {
  final String error;
  PurchaseConfirmationLoadFailure({required this.error});
}

class PurchaseConfirmationUpdateInProgress extends PurchaseConfirmationState {}

class PurchaseConfirmationUpdateSuccess extends PurchaseConfirmationState {}

class PurchaseConfirmationUpdateFailure extends PurchaseConfirmationState {
    final String error;
  PurchaseConfirmationUpdateFailure({required this.error});
}

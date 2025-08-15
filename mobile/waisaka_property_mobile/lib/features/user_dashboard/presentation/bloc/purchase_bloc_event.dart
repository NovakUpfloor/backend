part of 'purchase_bloc.dart';

@immutable
abstract class PurchaseEvent {}

class SubmitPurchase extends PurchaseEvent {
  final int packageId;
  final String whatsappNumber;
  final File paymentProof;

  SubmitPurchase({
    required this.packageId,
    required this.whatsappNumber,
    required this.paymentProof,
  });
}

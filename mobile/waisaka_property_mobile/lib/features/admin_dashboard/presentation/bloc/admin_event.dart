part of 'admin_bloc.dart';

@immutable
abstract class AdminEvent {}

class FetchMembers extends AdminEvent {}

class FetchActivations extends AdminEvent {}

class ApproveActivation extends AdminEvent {
  final int activationId;
  ApproveActivation(this.activationId);
}

part of 'auth_bloc.dart';

@immutable
abstract class AuthEvent {}

class AuthLoginRequested extends AuthEvent {
  final String username;
  final String password;

  AuthLoginRequested({required this.username, required this.password});
}

class AuthRegisterRequested extends AuthEvent {
  final String name;
  final String username;
  final String email;
  final String password;
  final int packageId;
  final String paymentProofPath;

  AuthRegisterRequested({
    required this.name,
    required this.username,
    required this.email,
    required this.password,
    required this.packageId,
    required this.paymentProofPath,
  });
}

class FetchUserProfile extends AuthEvent {}

class AuthLogoutRequested extends AuthEvent {}

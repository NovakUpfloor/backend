part of 'auth_bloc.dart';

@immutable
abstract class AuthState {}

class AuthInitial extends AuthState {}

class AuthLoading extends AuthState {}

class AuthSuccess extends AuthState {
  final User user;
  AuthSuccess({required this.user});
}

class AuthFailure extends AuthState {
  final String error;
  AuthFailure({required this.error});
}

class AuthRegisterSuccess extends AuthState {}

// States for fetching user profile within the dashboard
class UserProfileLoading extends AuthState {}

class UserProfileLoaded extends AuthState {
  final User user;
  UserProfileLoaded({required this.user});
}

class AuthLogoutSuccess extends AuthState {}

class AuthPackagesLoading extends AuthState {}

class AuthPackagesLoadSuccess extends AuthState {
  final List<Package> packages;
  AuthPackagesLoadSuccess({required this.packages});
}

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:waisaka_property_mobile/features/auth/data/models/user.dart';
import 'package:waisaka_property_mobile/features/auth/data/repositories/auth_repository.dart';

part 'auth_event.dart';
part 'auth_state.dart';

class AuthBloc extends Bloc<AuthEvent, AuthState> {
  final AuthRepository _authRepository;

  AuthBloc({required AuthRepository authRepository})
      : _authRepository = authRepository,
        super(AuthInitial()) {
    on<AuthLoginRequested>(_onLoginRequested);
    on<AuthRegisterRequested>(_onRegisterRequested);
    on<FetchUserProfile>(_onFetchUserProfile);
  }

  Future<void> _onLoginRequested(
    AuthLoginRequested event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      final user = await _authRepository.login(
        username: event.username,
        password: event.password,
      );
      emit(AuthSuccess(user: user));
    } catch (e) {
      emit(AuthFailure(error: e.toString()));
    }
  }

  Future<void> _onRegisterRequested(
    AuthRegisterRequested event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      await _authRepository.register(
        name: event.name,
        username: event.username,
        email: event.email,
        password: event.password,
        packageId: event.packageId,
        paymentProofPath: event.paymentProofPath,
      );
      emit(AuthRegisterSuccess());
    } catch (e) {
      emit(AuthFailure(error: e.toString()));
    }
  }

  Future<void> _onFetchUserProfile(
    FetchUserProfile event,
    Emitter<AuthState> emit,
  ) async {
    emit(UserProfileLoading());
    try {
      final user = await _authRepository.getUserProfile();
      emit(UserProfileLoaded(user: user));
    } catch (e) {
      emit(AuthFailure(error: e.toString()));
    }
  }
}

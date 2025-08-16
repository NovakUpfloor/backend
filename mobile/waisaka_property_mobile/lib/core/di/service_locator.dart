import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:get_it/get_it.dart';
import 'package:waisaka_property_mobile/core/api/api_client.dart';
import 'package:waisaka_property_mobile/features/admin_dashboard/data/repositories/admin_repository.dart';
import 'package:waisaka_property_mobile/features/admin_dashboard/presentation/bloc/purchase_confirmation_bloc.dart';
import 'package:waisaka_property_mobile/features/article/data/repositories/article_repository.dart';
import 'package:waisaka_property_mobile/features/auth/data/repositories/auth_repository.dart';
import 'package:waisaka_property_mobile/features/auth/data/repositories/package_repository.dart';
import 'package:waisaka_property_mobile/features/auth/presentation/bloc/auth_bloc.dart';
import 'package:waisaka_property_mobile/features/gemini/data/repositories/gemini_repository.dart';
import 'package:waisaka_property_mobile/features/gemini/presentation/bloc/gemini_bloc.dart';
import 'package:waisaka_property_mobile/features/home/presentation/bloc/home_bloc.dart';
import 'package:waisaka_property_mobile/features/property/data/repositories/property_repository.dart';
import 'package:waisaka_property_mobile/features/property/presentation/bloc/property_detail_bloc.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/data/repositories/user_dashboard_repository.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/presentation/bloc/purchase_history_bloc.dart';

final sl = GetIt.instance;

void setupServiceLocator() {
  // BLoCs
  sl.registerFactory(() => HomeBloc(
        propertyRepository: sl(),
        articleRepository: sl(),
        geminiRepository: sl(),
      ));
  sl.registerFactory(() => PropertyDetailBloc(propertyRepository: sl()));
  sl.registerFactory(() => GeminiBloc(geminiRepository: sl()));
  sl.registerFactory(() => AuthBloc(authRepository: sl()));
  sl.registerFactory(() => PurchaseHistoryBloc(repository: sl()));
  sl.registerFactory(() => PurchaseConfirmationBloc(repository: sl()));

  // Repositories
  sl.registerLazySingleton(() => PropertyRepository(apiClient: sl()));
  sl.registerLazySingleton(() => ArticleRepository(apiClient: sl()));
  sl.registerLazySingleton(() => GeminiRepository());
  sl.registerLazySingleton(() => AuthRepository(apiClient: sl(), secureStorage: sl()));
  sl.registerLazySingleton(() => PackageRepository(apiClient: sl()));
  sl.registerLazySingleton(() => UserDashboardRepository(apiClient: sl()));
  sl.registerLazySingleton(() => AdminRepository(apiClient: sl()));

  // Core & External
  sl.registerLazySingleton(() => ApiClient(secureStorage: sl()));
  sl.registerLazySingleton(() => const FlutterSecureStorage());
}

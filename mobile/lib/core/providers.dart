import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'connectivity/connectivity_service.dart';
import 'db/app_database.dart';
import 'network/api_client.dart';
import 'security/token_storage.dart';

final tokenStorageProvider = Provider<TokenStorage>((ref) => TokenStorage());

final apiClientProvider = Provider<ApiClient>((ref) {
  return ApiClient(tokenStorage: ref.watch(tokenStorageProvider));
});

final appDatabaseProvider = Provider<AppDatabase>((ref) {
  final db = AppDatabase();
  ref.onDispose(db.close);

  return db;
});

final connectivityServiceProvider =
    Provider<ConnectivityService>((ref) => ConnectivityService());

final isOnlineProvider = StreamProvider<bool>((ref) {
  return ref.watch(connectivityServiceProvider).onConnectivityChanged;
});

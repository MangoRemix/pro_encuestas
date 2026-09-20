import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'core/providers.dart';
import 'core/router/app_router.dart';
import 'features/sync/sync_service.dart';

class ProEncuestasApp extends ConsumerWidget {
  const ProEncuestasApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);

    // Al recuperar conexión, intenta subir lo pendiente en silencio (mejor
    // esfuerzo — cualquier falla queda registrada por instancia y se puede
    // reintentar manualmente desde el historial).
    ref.listen(isOnlineProvider, (previous, next) {
      final wasOffline = previous?.valueOrNull == false;
      final isNowOnline = next.valueOrNull == true;

      if (wasOffline && isNowOnline) {
        ref.read(syncServiceProvider).uploadPending();
      }
    });

    return MaterialApp.router(
      title: 'Encuestas Cumaná',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.blue.shade900),
        useMaterial3: true,
      ),
      routerConfig: router,
    );
  }
}

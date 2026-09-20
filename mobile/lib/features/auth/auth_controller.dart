import 'dart:io';

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/network/api_client.dart';
import '../../core/providers.dart';
import '../../core/security/token_storage.dart';
import 'auth_state.dart';

class AuthController extends StateNotifier<AuthState> {
  AuthController({required ApiClient apiClient, required TokenStorage tokenStorage})
      : _apiClient = apiClient,
        _tokenStorage = tokenStorage,
        super(const AuthUnknown()) {
    _restoreSession();
  }

  final ApiClient _apiClient;
  final TokenStorage _tokenStorage;

  Future<void> _restoreSession() async {
    final hasSession = await _tokenStorage.hasSession();

    if (!hasSession) {
      state = const AuthUnauthenticated();

      return;
    }

    final personId = await _tokenStorage.readPersonId();
    final personName = await _tokenStorage.readPersonName();

    // El token puede ser inválido (revocado desde el panel web), pero no se
    // valida contra el servidor aquí: la app debe poder abrir sesión sin
    // conexión. Una petición fallida por 401 en cualquier pantalla ya limpia
    // la sesión (ver ApiClient) y regresa al login.
    state = AuthAuthenticated(
      personId: personId ?? 0,
      personName: personName ?? '',
    );
  }

  Future<bool> login({required String email, required String password}) async {
    try {
      final response = await _apiClient.dio.post('mobile/login', data: {
        'email': email,
        'password': password,
        'device_name': Platform.isAndroid ? 'android' : 'ios',
      });

      final token = response.data['token'] as String;
      final user = response.data['user'] as Map<String, dynamic>;

      await _tokenStorage.save(
        token: token,
        personId: user['id'] as int,
        personName: user['name'] as String,
      );

      state = AuthAuthenticated(
        personId: user['id'] as int,
        personName: user['name'] as String,
      );

      return true;
    } on DioException catch (error) {
      state = AuthUnauthenticated(errorMessage: _extractErrorMessage(error));

      return false;
    }
  }

  String _extractErrorMessage(DioException error) {
    final data = error.response?.data;

    if (data is Map && data['errors'] is Map) {
      final errors = (data['errors'] as Map).values.expand((v) => v as List);

      return errors.join(' ');
    }

    if (data is Map && data['message'] is String) {
      return data['message'] as String;
    }

    return 'No se pudo iniciar sesión. Verifica tu conexión e intenta de nuevo.';
  }

  Future<void> logout() async {
    try {
      await _apiClient.dio.post('mobile/logout');
    } catch (_) {
      // Sin conexión: se revoca solo localmente, el token en el servidor
      // queda vivo hasta que se pueda alcanzar (riesgo aceptado — el panel
      // web permite deshabilitar la cuenta si el dispositivo se pierde).
    }

    await _tokenStorage.clear();
    state = const AuthUnauthenticated();
  }
}

final authControllerProvider =
    StateNotifierProvider<AuthController, AuthState>((ref) {
  return AuthController(
    apiClient: ref.watch(apiClientProvider),
    tokenStorage: ref.watch(tokenStorageProvider),
  );
});

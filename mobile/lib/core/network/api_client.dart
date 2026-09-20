import 'package:dio/dio.dart';

import '../security/token_storage.dart';

/// URL base del backend. Se puede sobreescribir en tiempo de compilación:
///   flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8081/api/
/// (10.0.2.2 es el alias del host desde el emulador de Android).
const String _defaultApiBaseUrl = 'https://encuestas.alcaldiamunsucre.org/api/';
const String apiBaseUrl = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: _defaultApiBaseUrl,
);

/// Cliente HTTP único de la app: agrega el token de sesión a cada petición y
/// centraliza el manejo de 401 (token inválido/expirado -> cerrar sesión).
class ApiClient {
  ApiClient({required TokenStorage tokenStorage, Dio? dio})
      : _tokenStorage = tokenStorage,
        _dio = dio ??
            Dio(BaseOptions(
              baseUrl: apiBaseUrl,
              connectTimeout: const Duration(seconds: 15),
              receiveTimeout: const Duration(seconds: 30),
              headers: {'Accept': 'application/json'},
            )) {
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await _tokenStorage.readToken();

          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }

          handler.next(options);
        },
        onError: (error, handler) async {
          if (error.response?.statusCode == 401) {
            await _tokenStorage.clear();
          }

          handler.next(error);
        },
      ),
    );
  }

  final Dio _dio;
  final TokenStorage _tokenStorage;

  Dio get dio => _dio;
}

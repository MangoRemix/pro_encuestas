import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Guarda el token de sesión y la identidad básica del encuestador en el
/// almacenamiento seguro del dispositivo (Keychain en iOS, Keystore en
/// Android) — nunca en SharedPreferences ni en la base de datos local sin
/// cifrar.
class TokenStorage {
  TokenStorage({FlutterSecureStorage? storage})
      : _storage = storage ?? const FlutterSecureStorage();

  final FlutterSecureStorage _storage;

  static const _tokenKey = 'auth_token';
  static const _personIdKey = 'auth_person_id';
  static const _personNameKey = 'auth_person_name';

  Future<void> save({
    required String token,
    required int personId,
    required String personName,
  }) async {
    await _storage.write(key: _tokenKey, value: token);
    await _storage.write(key: _personIdKey, value: personId.toString());
    await _storage.write(key: _personNameKey, value: personName);
  }

  Future<String?> readToken() => _storage.read(key: _tokenKey);

  Future<int?> readPersonId() async {
    final value = await _storage.read(key: _personIdKey);

    return value == null ? null : int.tryParse(value);
  }

  Future<String?> readPersonName() => _storage.read(key: _personNameKey);

  Future<bool> hasSession() async => (await readToken()) != null;

  Future<void> clear() async {
    await _storage.delete(key: _tokenKey);
    await _storage.delete(key: _personIdKey);
    await _storage.delete(key: _personNameKey);
  }
}

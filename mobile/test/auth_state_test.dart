import 'package:flutter_test/flutter_test.dart';
import 'package:pro_encuestas_mobile/features/auth/auth_state.dart';

void main() {
  group('AuthState', () {
    test('AuthAuthenticated exposes the person id and name', () {
      const state = AuthAuthenticated(personId: 7, personName: 'Juana Pérez');

      expect(state.personId, 7);
      expect(state.personName, 'Juana Pérez');
    });

    test('AuthUnauthenticated carries an optional error message', () {
      const withError = AuthUnauthenticated(errorMessage: 'Credenciales inválidas');
      const withoutError = AuthUnauthenticated();

      expect(withError.errorMessage, 'Credenciales inválidas');
      expect(withoutError.errorMessage, isNull);
    });
  });
}

sealed class AuthState {
  const AuthState();
}

class AuthUnknown extends AuthState {
  const AuthUnknown();
}

class AuthUnauthenticated extends AuthState {
  const AuthUnauthenticated({this.errorMessage});

  final String? errorMessage;
}

class AuthAuthenticated extends AuthState {
  const AuthAuthenticated({required this.personId, required this.personName});

  final int personId;
  final String personName;
}

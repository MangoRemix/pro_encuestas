import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/auth_controller.dart';
import '../../features/auth/auth_state.dart';
import '../../features/auth/login_screen.dart';
import '../../features/fill_wizard/survey_fill_screen.dart';
import '../../features/history/history_screen.dart';
import '../../features/respondents/respondent_info_screen.dart';
import '../../features/surveys/survey_list_screen.dart';

final routerProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authControllerProvider);

  return GoRouter(
    initialLocation: '/surveys',
    redirect: (context, state) {
      final isLoggingIn = state.matchedLocation == '/login';

      if (authState is AuthUnknown) {
        // Aún restaurando la sesión guardada — no redirigir todavía.
        return null;
      }

      final isAuthenticated = authState is AuthAuthenticated;

      if (!isAuthenticated && !isLoggingIn) return '/login';
      if (isAuthenticated && isLoggingIn) return '/surveys';

      return null;
    },
    routes: [
      GoRoute(path: '/login', builder: (context, state) => const LoginScreen()),
      GoRoute(
        path: '/surveys',
        builder: (context, state) => const SurveyListScreen(),
      ),
      GoRoute(
        path: '/respondent/:surveyId',
        builder: (context, state) => RespondentInfoScreen(
          surveyId: int.parse(state.pathParameters['surveyId']!),
        ),
      ),
      GoRoute(
        path: '/fill/:instanceUuid',
        builder: (context, state) => SurveyFillScreen(
          instanceUuid: state.pathParameters['instanceUuid']!,
        ),
      ),
      GoRoute(path: '/history', builder: (context, state) => const HistoryScreen()),
    ],
  );
});

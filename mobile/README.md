# App móvil — Encuestadores (Alcaldía de Cumaná)

App Flutter para que los encuestadores llenen encuestas en campo, principalmente **sin conexión**: la sesión y las encuestas se descargan una vez y quedan disponibles para llenarlas repetidamente; lo llenado se guarda en el teléfono y se sube cuando hay wifi/datos.

## Requisitos

- Flutter SDK (canal `stable`) — este proyecto **no fue compilado ni probado localmente** durante su creación (la máquina de desarrollo no tenía el SDK instalado). Antes de usarlo, es indispensable correr los pasos de abajo y corregir cualquier error de compilación que surja — el CI (`.github/workflows/mobile-ci.yml`) también lo hace en cada push a `mobile/**`.

## Primeros pasos

```bash
cd mobile
flutter pub get

# Genera el código de Drift (app_database.g.dart) — obligatorio antes de compilar
dart run build_runner build --delete-conflicting-outputs

flutter analyze
flutter test
flutter run
```

Para apuntar a un backend distinto al de producción (por ejemplo, el stack local vía Docker Compose):

```bash
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8081/api/
```

(`10.0.2.2` es el alias del host desde el emulador de Android; en iOS/dispositivo físico usa la IP real de la máquina que corre el backend).

## Arquitectura

- **Estado**: Riverpod (`flutter_riverpod`), `StateNotifier` para auth, providers simples para servicios.
- **Almacenamiento local**: Drift (SQLite) — cachea la estructura completa de las encuestas asignadas y lleva la cola de instancias en progreso/listas/subidas. Ver `lib/core/db/app_database.dart`.
- **Token de sesión**: `flutter_secure_storage` (Keychain/Keystore) — nunca en la base de datos local ni en SharedPreferences.
- **Red**: `dio`, con interceptor que agrega el `Authorization: Bearer` a cada petición.
- **Navegación**: `go_router`, con redirección automática según el estado de sesión.

## Flujo principal

1. **Login** (`features/auth/`) — solo encuestadores (rol `POLLSTER`); emite un token de Sanctum sin expiración.
2. **Selector de encuestas** (`features/surveys/`) — descarga `GET /mobile/surveys` (asignadas y vigentes) y, la primera vez, la estructura completa de cada una vía `GET /survey/show-full/{id}`.
3. **Datos del encuestado** (`features/respondents/`) — crea una nueva instancia local antes de empezar a llenar.
4. **Asistente de llenado** (`features/fill_wizard/`) — un paso por categoría; cada respuesta se guarda de inmediato (permite reanudar exactamente donde se quedó si se cierra la app).
5. Al finalizar: la instancia pasa a "preparada", se muestra el conteo de encuestados de esa encuesta, y se regresa directo al paso 3 para la siguiente persona.
6. **Historial** (`features/history/`) — estados Incompleta (roja, retoma el llenado)/Preparada (naranja)/Completada (verde, ya subida); subida y borrado individual o masivo.

## Sincronización

`features/sync/sync_service.dart` sube cada instancia "preparada" al endpoint atómico `POST /api/result/batch-instance` (crea el encuestado + todas sus respuestas en una transacción en el servidor). Es idempotente por `instance_uuid`: reintentar una subida cortada no duplica datos. Se sube una por una (no en paralelo) para no saturar el servidor y mantener el manejo de fallos simple — una instancia fallida no bloquea a las demás.

## Decisiones/simplificaciones a tener en cuenta

- **No se implementó certificate pinning.** El plan original lo dejaba como "deseable, no obligatorio" — se omitió porque fijar el pin del certificado de producción sin poder verificarlo hubiera sido más riesgoso que no hacerlo (un pin incorrecto rompe toda la conectividad de la app). Si se quiere agregar, `dio` soporta interceptores de pinning fácilmente.
- **Sin el toggle "solo por wifi"** para la subida automática — hoy sube apenas detecta cualquier conexión (wifi o datos). Es una mejora simple de agregar después si el consumo de datos móviles es una preocupación real para el personal de campo.
- El token de sesión no expira (coherente con el requisito de trabajar offline por periodos largos); el cierre de sesión revoca el token explícitamente contra el servidor.

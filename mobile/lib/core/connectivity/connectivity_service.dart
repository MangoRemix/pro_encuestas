import 'package:connectivity_plus/connectivity_plus.dart';

/// `connectivity_plus` solo informa el estado de la interfaz de red (wifi/
/// datos/ninguna), no si hay internet real — por eso `hasRealInternet` hace
/// además una petición de red real y liviana antes de intentar sincronizar.
class ConnectivityService {
  ConnectivityService({Connectivity? connectivity})
      : _connectivity = connectivity ?? Connectivity();

  final Connectivity _connectivity;

  Stream<bool> get onConnectivityChanged => _connectivity
      .onConnectivityChanged
      .map((results) => !results.contains(ConnectivityResult.none));

  Future<bool> hasNetworkInterface() async {
    final results = await _connectivity.checkConnectivity();

    return !results.contains(ConnectivityResult.none);
  }
}

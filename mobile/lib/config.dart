class Config {
  // Point this at your RentCeylon Laravel API.
  //  - Android emulator reaches host machine via 10.0.2.2
  //  - iOS simulator can use localhost
  //  - Production: https://rentceylon.lk/api
  static const String apiBase = String.fromEnvironment(
    'API_BASE',
    defaultValue: 'http://10.0.2.2:8000/api',
  );

  static const String brandName = 'RentCeylon';
}

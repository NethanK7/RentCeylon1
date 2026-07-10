import 'package:flutter/material.dart';

/// Matches the web app's tailwind.config.js palette exactly, so the mobile
/// app and website feel like the same product: navy (`brand`) is the identity
/// color, gold (`gold`) is the call-to-action / accent lifted from the logo's
/// ring, ceylon teal is used sparingly for trust icons.
class AppColors {
  static const navy50 = Color(0xFFEEF4FB);
  static const navy300 = Color(0xFF7EA9E0);
  static const navy600 = Color(0xFF1F4278);
  static const navy700 = Color(0xFF173562);
  static const navy800 = Color(0xFF123063);
  static const navy900 = Color(0xFF0D2247);

  static const gold300 = Color(0xFFEFC468);
  static const gold500 = Color(0xFFC6900F);
  static const gold600 = Color(0xFFA5760C);

  static const ceylon600 = Color(0xFF0D9488);

  static const rose600 = Color(0xFFE11D48);
  static const emerald600 = Color(0xFF059669);
}

ThemeData buildAppTheme() {
  final base = ThemeData(
    useMaterial3: true,
    colorScheme: ColorScheme.fromSeed(
      seedColor: AppColors.navy800,
      primary: AppColors.navy800,
      secondary: AppColors.gold500,
    ),
    scaffoldBackgroundColor: Colors.white,
    fontFamily: 'Roboto',
  );

  return base.copyWith(
    appBarTheme: const AppBarTheme(
      backgroundColor: Colors.white,
      foregroundColor: Colors.black87,
      elevation: 0,
      surfaceTintColor: Colors.white,
      centerTitle: false,
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.gold500,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 20),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        textStyle: const TextStyle(fontWeight: FontWeight.w700),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: AppColors.navy800,
        side: const BorderSide(color: Color(0xFFD1D5DB)),
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 20),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: const Color(0xFFF9FAFB),
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: AppColors.gold500, width: 1.5),
      ),
    ),
    bottomNavigationBarTheme: const BottomNavigationBarThemeData(
      backgroundColor: Colors.white,
      selectedItemColor: AppColors.navy700,
      unselectedItemColor: Color(0xFF9CA3AF),
      type: BottomNavigationBarType.fixed,
      selectedLabelStyle: TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
      unselectedLabelStyle: TextStyle(fontSize: 11),
    ),
  );
}

import 'package:flutter/material.dart';

/// KandaNews Africa Design System
class KnColors {
  // Primary
  static const Color navy = Color(0xFF1E2B42);
  static const Color navyLight = Color(0xFF2A3F5F);
  static const Color orange = Color(0xFFF05A1A);
  static const Color orangeLight = Color(0xFFFF7A3D);

  // Neutrals
  static const Color white = Color(0xFFFFFFFF);
  static const Color background = Color(0xFFF5F6FA);
  static const Color surface = Color(0xFFFFFFFF);
  static const Color border = Color(0xFFE1E8ED);
  static const Color textPrimary = Color(0xFF1E2B42);
  static const Color textSecondary = Color(0xFF6B7280);
  static const Color textMuted = Color(0xFF9CA3AF);

  // Semantic
  static const Color success = Color(0xFF10B981);
  static const Color error = Color(0xFFEF4444);
  static const Color warning = Color(0xFFF59E0B);
  static const Color info = Color(0xFF3B82F6);

  // Gradients
  static const LinearGradient primaryGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [navy, navyLight],
  );

  static const LinearGradient orangeGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [orange, orangeLight],
  );
}

class KnTheme {
  static ThemeData get light => ThemeData(
        useMaterial3: true,
        brightness: Brightness.light,
        colorScheme: const ColorScheme.light(
          primary: KnColors.orange,
          onPrimary: KnColors.white,
          secondary: KnColors.navy,
          onSecondary: KnColors.white,
          surface: KnColors.surface,
          onSurface: KnColors.textPrimary,
          error: KnColors.error,
        ),
        scaffoldBackgroundColor: KnColors.background,
        fontFamily: 'System',
        appBarTheme: const AppBarTheme(
          backgroundColor: KnColors.navy,
          foregroundColor: KnColors.white,
          elevation: 0,
          centerTitle: false,
          titleTextStyle: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.w700,
            color: KnColors.white,
          ),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            backgroundColor: KnColors.orange,
            foregroundColor: KnColors.white,
            elevation: 2,
            padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            textStyle: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
        outlinedButtonTheme: OutlinedButtonThemeData(
          style: OutlinedButton.styleFrom(
            foregroundColor: KnColors.navy,
            side: const BorderSide(color: KnColors.border, width: 2),
            padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            textStyle: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
        inputDecorationTheme: InputDecorationTheme(
          filled: true,
          fillColor: KnColors.white,
          contentPadding:
              const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: KnColors.border, width: 2),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: KnColors.border, width: 2),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: KnColors.orange, width: 2),
          ),
          errorBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: KnColors.error, width: 2),
          ),
          labelStyle: const TextStyle(
            color: KnColors.textSecondary,
            fontWeight: FontWeight.w600,
          ),
          hintStyle: const TextStyle(color: KnColors.textMuted),
        ),
        cardTheme: CardTheme(
          elevation: 2,
          shadowColor: KnColors.navy.withAlpha(25),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          clipBehavior: Clip.antiAlias,
        ),
        bottomNavigationBarTheme: const BottomNavigationBarThemeData(
          backgroundColor: KnColors.white,
          selectedItemColor: KnColors.orange,
          unselectedItemColor: KnColors.textMuted,
          type: BottomNavigationBarType.fixed,
          elevation: 8,
        ),
        dividerTheme: const DividerThemeData(
          color: KnColors.border,
          thickness: 1,
        ),
      );

  static ThemeData get dark => light.copyWith(
        brightness: Brightness.dark,
        scaffoldBackgroundColor: const Color(0xFF0F1624),
        colorScheme: const ColorScheme.dark(
          primary: KnColors.orange,
          onPrimary: KnColors.white,
          secondary: KnColors.navyLight,
          surface: Color(0xFF1A2332),
          onSurface: KnColors.white,
          error: KnColors.error,
        ),
      );
}

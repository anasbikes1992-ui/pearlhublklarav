import 'package:flutter/material.dart';

/// PearlHub App Theme Configuration
class AppTheme {
  // Colors
  static const Color darkBg = Color(0xFF0a0e27);
  static const Color darkBgSecondary = Color(0xFF0d111e);
  static const Color darkSecondary = Color(0xFF0f1422);
  static const Color darkCard = Color(0xFF1a232f);
  static const Color darkHover = Color(0xFF20293d);

  static const Color textWhite = Color(0xFFffffff);
  static const Color textPrimary = Color(0xFFe6edf3);
  static const Color textSecondary = Color(0xFF8b949e);
  static const Color textMuted = Color(0xFF6e7681);

  static const Color accentTeal = Color(0xFF00d4ff);
  static const Color accentGold = Color(0xFFd4af37);
  static const Color accentEmerald = Color(0xFF10b981);
  static const Color accentRose = Color(0xFFf43f5e);
  static const Color accentOrange = Color(0xFFf59e0b);
  static const Color accentPurple = Color(0xFF8b5cf6);

  static const Color borderColor = Color(0xFF2a3f5f);

  // Shadows
  static const BoxShadow shadowSm = BoxShadow(
    color: Color.fromARGB(76, 0, 0, 0),
    blurRadius: 12,
    offset: Offset(0, 4),
  );

  static const BoxShadow shadowMd = BoxShadow(
    color: Color.fromARGB(102, 0, 0, 0),
    blurRadius: 32,
    offset: Offset(0, 12),
  );

  static const BoxShadow shadowLg = BoxShadow(
    color: Color.fromARGB(127, 0, 0, 0),
    blurRadius: 48,
    offset: Offset(0, 20),
  );

  // Border Radius
  static const double radiusSm = 4;
  static const double radiusMd = 8;
  static const double radiusLg = 12;
  static const double radiusXl = 16;
  static const double radius2xl = 20;

  /// Light Theme
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      scaffoldBackgroundColor: darkBg,
      primaryColor: accentTeal,
      colorScheme: const ColorScheme.dark(
        primary: accentTeal,
        secondary: accentGold,
        tertiary: accentEmerald,
        error: accentRose,
        background: darkBg,
        surface: darkCard,
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: darkSecondary,
        foregroundColor: textPrimary,
        elevation: 0,
        centerTitle: true,
      ),
      cardTheme: CardTheme(
        color: darkCard,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(radiusXl),
          side: const BorderSide(color: borderColor, width: 1),
        ),
      ),
      buttonTheme: ButtonThemeData(
        height: 48,
        minWidth: 120,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(radiusLg),
        ),
        textTheme: ButtonTextTheme.primary,
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: accentTeal,
          foregroundColor: darkBg,
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(radiusLg),
          ),
          elevation: 12,
          shadowColor: Color.fromARGB(76, 0, 212, 255),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: accentTeal,
          side: const BorderSide(color: accentTeal, width: 2),
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(radiusLg),
          ),
        ),
      ),
      textTheme: const TextTheme(
        displayLarge: TextStyle(
          fontSize: 32,
          fontWeight: FontWeight.w800,
          color: textWhite,
          letterSpacing: -0.5,
        ),
        displayMedium: TextStyle(
          fontSize: 28,
          fontWeight: FontWeight.w700,
          color: textWhite,
          letterSpacing: -0.3,
        ),
        displaySmall: TextStyle(
          fontSize: 24,
          fontWeight: FontWeight.w700,
          color: textWhite,
        ),
        headlineMedium: TextStyle(
          fontSize: 20,
          fontWeight: FontWeight.w700,
          color: textWhite,
        ),
        headlineSmall: TextStyle(
          fontSize: 18,
          fontWeight: FontWeight.w600,
          color: textWhite,
        ),
        titleLarge: TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.w600,
          color: textPrimary,
        ),
        bodyLarge: TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.w400,
          color: textPrimary,
        ),
        bodyMedium: TextStyle(
          fontSize: 14,
          fontWeight: FontWeight.w400,
          color: textSecondary,
        ),
        bodySmall: TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.w400,
          color: textMuted,
        ),
        labelLarge: TextStyle(
          fontSize: 14,
          fontWeight: FontWeight.w600,
          color: textPrimary,
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: darkCard,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(radiusMd),
          borderSide: const BorderSide(color: borderColor),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(radiusMd),
          borderSide: const BorderSide(color: borderColor),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(radiusMd),
          borderSide: const BorderSide(color: accentTeal, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(radiusMd),
          borderSide: const BorderSide(color: accentRose),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        hintStyle: const TextStyle(color: textMuted),
        labelStyle: const TextStyle(color: textPrimary),
        errorStyle: const TextStyle(color: accentRose),
      ),
      dividerColor: const Color.fromARGB(31, 42, 63, 95),
      visualDensity: VisualDensity.adaptivePlatformDensity,
    );
  }

  /// Get gradient background
  static LinearGradient get backgroundGradient {
    return const LinearGradient(
      begin: Alignment.topLeft,
      end: Alignment.bottomRight,
      colors: [darkBg, darkBgSecondary],
    );
  }

  /// Get accent gradient
  static LinearGradient get accentGradient {
    return const LinearGradient(
      begin: Alignment.topLeft,
      end: Alignment.bottomRight,
      colors: [accentTeal, accentGold],
    );
  }
}

import 'package:flutter/material.dart';

/// Pearl Hub Theme Configuration
/// Consistent dark theme across web and mobile
class PearlTheme {
  // ============= COLORS =============

  /// Primary dark background
  static const Color darkBg = Color(0xFF0a0e27);

  /// Secondary background for cards, containers
  static const Color darkSecondary = Color(0xFF0f1422);

  /// Card/surface background
  static const Color darkCard = Color(0xFF1a232f);

  /// Primary accent - Teal/Cyan
  static const Color accentTeal = Color(0xFF00d4ff);

  /// Secondary accent - Gold
  static const Color accentGold = Color(0xFFd4af37);

  /// Emerald accent for success states
  static const Color accentEmerald = Color(0xFF10b981);

  /// Rose accent for destructive actions
  static const Color accentRose = Color(0xFFf43f5e);

  /// Orange accent for warnings
  static const Color accentOrange = Color(0xFFf59e0b);

  /// Text colors
  static const Color textWhite = Color(0xFFffffff);
  static const Color textGray = Color(0xFF8892b0);
  static const Color textDark = Color(0xFF1f2937);

  /// Border colors
  static const Color borderLight = Color(0xFF374151);
  static const Color borderDark = Color(0xFF1f2937);

  // ============= GRADIENTS =============

  /// Primary gradient: Teal to Gold
  static const LinearGradient gradientPrimary = LinearGradient(
    colors: [accentTeal, accentGold],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  /// Accent gradient for hero sections
  static const LinearGradient gradientAccent = LinearGradient(
    colors: [accentTeal, Color(0xFF0a0e27), accentGold],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  /// Subtle gradient for card backgrounds
  static LinearGradient gradientCard = const LinearGradient(
    colors: [Color(0xFF0f1422), Color(0xFF1a232f)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  // ============= SHADOWS =============

  /// Glow shadow for teal accent
  static List<BoxShadow> shadowGlowTeal = [
    BoxShadow(
      color: accentTeal.withOpacity(0.2),
      blurRadius: 20,
      spreadRadius: 0,
    ),
    BoxShadow(
      color: accentTeal.withOpacity(0.1),
      blurRadius: 40,
      spreadRadius: 2,
    ),
  ];

  /// Glow shadow for gold accent
  static List<BoxShadow> shadowGlowGold = [
    BoxShadow(
      color: accentGold.withOpacity(0.2),
      blurRadius: 20,
      spreadRadius: 0,
    ),
  ];

  /// Elevated shadow for cards
  static const List<BoxShadow> shadowElevated = [
    BoxShadow(
      color: Color(0x1a000000),
      blurRadius: 25,
      spreadRadius: -5,
      offset: Offset(0, 10),
    ),
  ];

  /// Subtle shadow for hover states
  static const List<BoxShadow> shadowSubtle = [
    BoxShadow(
      color: Color(0x0a000000),
      blurRadius: 12,
      spreadRadius: 0,
    ),
  ];

  // ============= TEXT STYLES =============

  /// Display Large - Hero headings
  static const TextStyle displayLarge = TextStyle(
    fontSize: 48,
    fontWeight: FontWeight.bold,
    color: textWhite,
    letterSpacing: -2,
  );

  /// Display Medium - Section headings
  static const TextStyle displayMedium = TextStyle(
    fontSize: 36,
    fontWeight: FontWeight.bold,
    color: textWhite,
    letterSpacing: -1,
  );

  /// Headline - Card titles
  static const TextStyle headline = TextStyle(
    fontSize: 24,
    fontWeight: FontWeight.w700,
    color: textWhite,
  );

  /// Title Large - Sub-headings
  static const TextStyle titleLarge = TextStyle(
    fontSize: 20,
    fontWeight: FontWeight.w600,
    color: textWhite,
  );

  /// Title Medium - Form labels, secondary titles
  static const TextStyle titleMedium = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w600,
    color: textWhite,
  );

  /// Body Large - Primary text
  static const TextStyle bodyLarge = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w400,
    color: textWhite,
  );

  /// Body Medium - Secondary text
  static const TextStyle bodyMedium = TextStyle(
    fontSize: 14,
    fontWeight: FontWeight.w400,
    color: textGray,
  );

  /// Body Small - Tertiary text
  static const TextStyle bodySmall = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w400,
    color: textGray,
  );

  /// Label - Badges, tags
  static const TextStyle label = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w600,
    color: textWhite,
    letterSpacing: 0.5,
  );

  // ============= BUTTON STYLES =============

  /// Primary button style
  static ButtonStyle buttonPrimary = ElevatedButton.styleFrom(
    backgroundColor: accentTeal,
    foregroundColor: darkBg,
    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
    elevation: 0,
  );

  /// Secondary button style
  static ButtonStyle buttonSecondary = ElevatedButton.styleFrom(
    backgroundColor: darkCard,
    foregroundColor: accentTeal,
    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(8),
      side: const BorderSide(color: accentTeal, width: 1.5),
    ),
    elevation: 0,
  );

  /// Outlined button style
  static ButtonStyle buttonOutlined = OutlinedButton.styleFrom(
    foregroundColor: accentTeal,
    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
    side: const BorderSide(color: borderLight, width: 1),
  );

  // ============= INPUT STYLES =============

  /// Input decoration for text fields
  static InputDecoration inputDecoration({
    required String hintText,
    String? labelText,
    IconData? prefixIcon,
  }) {
    return InputDecoration(
      hintText: hintText,
      labelText: labelText,
      hintStyle: const TextStyle(color: textGray),
      labelStyle: const TextStyle(color: textGray),
      prefixIcon: prefixIcon != null ? Icon(prefixIcon, color: textGray) : null,
      filled: true,
      fillColor: darkCard,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: borderDark),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: borderDark),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: accentTeal, width: 2),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: accentRose),
      ),
    );
  }

  // ============= CARD STYLES =============

  /// Default card decoration
  static BoxDecoration cardDecoration = BoxDecoration(
    color: darkCard,
    borderRadius: BorderRadius.circular(12),
    border: Border.all(color: borderDark, width: 1),
    boxShadow: shadowSubtle,
  );

  /// Elevated card decoration with glow
  static BoxDecoration cardDecorrationElevated = BoxDecoration(
    color: darkCard,
    borderRadius: BorderRadius.circular(12),
    border: Border.all(color: accentTeal.withOpacity(0.3), width: 1),
    boxShadow: shadowGlowTeal,
  );

  // ============= SPACING =============

  static const double spacingXxs = 4;
  static const double spacingXs = 8;
  static const double spacingSmall = 12;
  static const double spacingMedium = 16;
  static const double spacingLarge = 24;
  static const double spacingXl = 32;
  static const double spacingXxl = 48;

  // ============= BORDER RADIUS =============

  static const double radiusSmall = 4;
  static const double radiusMedium = 8;
  static const double radiusLarge = 12;
  static const double radiusXl = 16;
  static const double radiusXxl = 20;

  // ============= DARK THEME DEFINITION =============

  static ThemeData get darkTheme => ThemeData(
    useMaterial3: true,
    brightness: Brightness.dark,
    
    // Primary color scheme
    colorScheme: ColorScheme.dark(
      brightness: Brightness.dark,
      primary: accentTeal,
      onPrimary: darkBg,
      secondary: accentGold,
      onSecondary: darkBg,
      surface: darkCard,
      onSurface: textWhite,
      error: accentRose,
      onError: textWhite,
      background: darkBg,
      onBackground: textWhite,
    ),

    // Scaffold background
    scaffoldBackgroundColor: darkBg,

    // App bar theme
    appBarTheme: const AppBarTheme(
      backgroundColor: darkSecondary,
      foregroundColor: textWhite,
      elevation: 0,
      centerTitle: true,
      titleTextStyle: TextStyle(
        color: textWhite,
        fontSize: 20,
        fontWeight: FontWeight.w600,
      ),
    ),

    // Card theme
    cardTheme: CardTheme(
      color: darkCard,
      surfaceTintColor: Colors.transparent,
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    ),

    // Text theme
    textTheme: const TextTheme(
      displayLarge: displayLarge,
      displayMedium: displayMedium,
      displaySmall: headline,
      headlineMedium: titleLarge,
      headlineSmall: titleMedium,
      titleLarge: bodyLarge,
      titleMedium: bodyMedium,
      titleSmall: bodySmall,
      bodyLarge: bodyLarge,
      bodyMedium: bodyMedium,
      bodySmall: bodySmall,
      labelLarge: label,
    ),

    // Button themes
    elevatedButtonTheme: ElevatedButtonThemeData(style: buttonPrimary),
    outlinedButtonTheme: OutlinedButtonThemeData(style: buttonOutlined),

    // Input decoration theme
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: darkCard,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(radiusMedium),
        borderSide: const BorderSide(color: borderDark),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(radiusMedium),
        borderSide: const BorderSide(color: borderDark),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(radiusMedium),
        borderSide: const BorderSide(color: accentTeal, width: 2),
      ),
      contentPadding: const EdgeInsets.symmetric(
        horizontal: spacingMedium,
        vertical: spacingSmall,
      ),
    ),

    // Divider theme
    dividerTheme: const DividerThemeData(
      color: borderDark,
      thickness: 1,
      space: 24,
    ),

    // Checkbox theme
    checkboxTheme: CheckboxThemeData(
      fillColor: MaterialStateProperty.all(accentTeal),
      side: const BorderSide(color: accentTeal),
    ),

    // Radio theme
    radioTheme: RadioThemeData(
      fillColor: MaterialStateProperty.all(accentTeal),
    ),

    // Switch theme
    switchTheme: SwitchThemeData(
      thumbColor: MaterialStateProperty.all(accentTeal),
      trackColor: MaterialStateProperty.all(accentTeal.withOpacity(0.3)),
    ),
  );

  // ============= UTILITY METHODS =============

  /// Get a gradient text style (used for hero titles)
  static TextStyle getGradientTextStyle({
    required double fontSize,
    required FontWeight fontWeight,
  }) {
    return TextStyle(
      fontSize: fontSize,
      fontWeight: fontWeight,
      color: accentTeal,
      shadows: [
        Shadow(
          color: accentGold.withOpacity(0.5),
          offset: const Offset(2, 2),
          blurRadius: 8,
        ),
      ],
    );
  }

  /// Generate a category color based on index
  static Color getCategoryColor(int index) {
    const colors = [
      accentTeal,
      accentGold,
      accentEmerald,
      accentRose,
      accentOrange,
      Color(0xFF8b5cf6), // Purple
    ];
    return colors[index % colors.length];
  }

  /// Generate contrast color for text on colored backgrounds
  static Color getContrastTextColor(Color backgroundColor) {
    // Simple luminance calculation
    final luminance = (0.299 * backgroundColor.red +
            0.587 * backgroundColor.green +
            0.114 * backgroundColor.blue) /
        255;
    return luminance > 0.5 ? textDark : textWhite;
  }
}

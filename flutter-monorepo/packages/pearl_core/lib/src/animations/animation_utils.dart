import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';

/// Animation duration constants for consistent animation timing across the app
class AnimationDurations {
  static const Duration fast = Duration(milliseconds: 200);
  static const Duration normal = Duration(milliseconds: 300);
  static const Duration slow = Duration(milliseconds: 500);
  static const Duration verySlow = Duration(milliseconds: 800);
}

/// Animation curve presets for consistent animation easing
class AnimationCurves {
  static const Curve standard = Curves.easeInOut;
  static const Curve entrance = Curves.easeOut;
  static const Curve exit = Curves.easeIn;
  static const Curve bounce = Curves.elasticOut;
}

/// Predefined animation effects for common UI patterns
class AnimationEffects {
  /// Fade in effect
  static List<Effect<dynamic>> fadeIn({
    Duration duration = const Duration(milliseconds: 300),
  }) {
    return [
      FadeEffect(duration: duration, curve: AnimationCurves.entrance),
    ];
  }

  /// Fade out effect
  static List<Effect<dynamic>> fadeOut({
    Duration duration = const Duration(milliseconds: 300),
  }) {
    return [
      FadeEffect(duration: duration, curve: AnimationCurves.exit),
    ];
  }

  /// Scale up animation with fade
  static List<Effect<dynamic>> scaleIn({
    Duration duration = const Duration(milliseconds: 300),
    double beginScale = 0.8,
  }) {
    return [
      ScaleEffect(
        duration: duration,
        curve: AnimationCurves.entrance,
        begin: Offset(beginScale, beginScale),
      ),
      FadeEffect(duration: duration, curve: AnimationCurves.entrance),
    ];
  }

  /// Scale down animation with fade
  static List<Effect<dynamic>> scaleOut({
    Duration duration = const Duration(milliseconds: 300),
    double endScale = 0.8,
  }) {
    return [
      ScaleEffect(
        duration: duration,
        curve: AnimationCurves.exit,
        end: Offset(endScale, endScale),
      ),
      FadeEffect(duration: duration, curve: AnimationCurves.exit),
    ];
  }

  /// Slide in from left
  static List<Effect<dynamic>> slideInFromLeft({
    Duration duration = const Duration(milliseconds: 300),
  }) {
    return [
      SlideEffect(
        duration: duration,
        curve: AnimationCurves.entrance,
        begin: const Offset(-1, 0),
        end: Offset.zero,
      ),
      FadeEffect(duration: duration, curve: AnimationCurves.entrance),
    ];
  }

  /// Slide in from right
  static List<Effect<dynamic>> slideInFromRight({
    Duration duration = const Duration(milliseconds: 300),
  }) {
    return [
      SlideEffect(
        duration: duration,
        curve: AnimationCurves.entrance,
        begin: const Offset(1, 0),
        end: Offset.zero,
      ),
      FadeEffect(duration: duration, curve: AnimationCurves.entrance),
    ];
  }

  /// Slide in from top
  static List<Effect<dynamic>> slideInFromTop({
    Duration duration = const Duration(milliseconds: 300),
  }) {
    return [
      SlideEffect(
        duration: duration,
        curve: AnimationCurves.entrance,
        begin: const Offset(0, -1),
        end: Offset.zero,
      ),
      FadeEffect(duration: duration, curve: AnimationCurves.entrance),
    ];
  }

  /// Slide in from bottom
  static List<Effect<dynamic>> slideInFromBottom({
    Duration duration = const Duration(milliseconds: 300),
  }) {
    return [
      SlideEffect(
        duration: duration,
        curve: AnimationCurves.entrance,
        begin: const Offset(0, 1),
        end: Offset.zero,
      ),
      FadeEffect(duration: duration, curve: AnimationCurves.entrance),
    ];
  }

  /// Bounce in animation
  static List<Effect<dynamic>> bounceIn({
    Duration duration = const Duration(milliseconds: 400),
  }) {
    return [
      ScaleEffect(
        duration: duration,
        curve: AnimationCurves.bounce,
        begin: Offset.zero,
      ),
      FadeEffect(duration: duration, curve: AnimationCurves.entrance),
    ];
  }

  /// Rotate in animation
  static List<Effect<dynamic>> rotateIn({
    Duration duration = const Duration(milliseconds: 300),
  }) {
    return [
      RotateEffect(
        duration: duration,
        curve: AnimationCurves.entrance,
        begin: 0.5,
      ),
      FadeEffect(duration: duration, curve: AnimationCurves.entrance),
    ];
  }

  /// Pulse animation for continuous effect
  static List<Effect<dynamic>> pulse({
    Duration duration = const Duration(milliseconds: 1000),
  }) {
    return [
      ScaleEffect(
        duration: duration,
        curve: Curves.easeInOut,
        begin: const Offset(1, 1),
        end: const Offset(1.05, 1.05),
      ),
    ];
  }

  /// Shimmer loading animation
  static List<Effect<dynamic>> shimmer({
    Duration duration = const Duration(milliseconds: 1500),
  }) {
    return [
      ShimmerEffect(
        duration: duration,
        curve: Curves.linear,
        color: Colors.white,
        angle: 90,
      ),
    ];
  }
}

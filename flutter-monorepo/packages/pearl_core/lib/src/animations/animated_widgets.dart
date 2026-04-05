import 'package:flutter/material.dart';
import 'animation_utils.dart';

/// A wrapper widget that applies animations to its child
/// Use this widget to easily add animations to any UI element
class AnimatedContainer extends StatelessWidget {
  final Widget child;
  final List<dynamic> effects;
  final Duration delay;
  final bool onHover;
  final List<dynamic>? hoverEffects;

  const AnimatedContainer({
    Key? key,
    required this.child,
    required this.effects,
    this.delay = Duration.zero,
    this.onHover = false,
    this.hoverEffects,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final animated = TweenAnimationBuilder<double>(
      tween: Tween(begin: 0, end: 1),
      duration: const Duration(milliseconds: 300),
      child: child,
      builder: (_, value, innerChild) => Opacity(
        opacity: value,
        child: Transform.scale(
          scale: 0.98 + (0.02 * value),
          child: innerChild,
        ),
      ),
    );

    if (onHover && hoverEffects != null) {
      return MouseRegion(
        onEnter: (_) {},
        onExit: (_) {},
        child: animated,
      );
    }

    return animated;
  }
}

/// Animated button with built-in effects
class AnimatedButton extends StatefulWidget {
  final Widget child;
  final VoidCallback onPressed;
  final Duration duration;
  final Curve curve;
  final double scale;

  const AnimatedButton({
    Key? key,
    required this.child,
    required this.onPressed,
    this.duration = const Duration(milliseconds: 150),
    this.curve = Curves.easeInOut,
    this.scale = 0.95,
  }) : super(key: key);

  @override
  State<AnimatedButton> createState() => _AnimatedButtonState();
}

class _AnimatedButtonState extends State<AnimatedButton>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(duration: widget.duration, vsync: this);
    _scaleAnimation = Tween<double>(begin: 1.0, end: widget.scale).animate(
      CurvedAnimation(parent: _controller, curve: widget.curve),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _onTapDown(TapDownDetails details) {
    _controller.forward();
  }

  void _onTapUp(TapUpDetails details) {
    _controller.reverse();
    widget.onPressed();
  }

  void _onTapCancel() {
    _controller.reverse();
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: _onTapDown,
      onTapUp: _onTapUp,
      onTapCancel: _onTapCancel,
      child: ScaleTransition(
        scale: _scaleAnimation,
        child: widget.child,
      ),
    );
  }
}

/// Animated text widget with fade-in effect
class AnimatedText extends StatelessWidget {
  final String text;
  final TextStyle? style;
  final List<dynamic>? customEffects;
  final Duration delay;

  const AnimatedText(
    this.text, {
    Key? key,
    this.style,
    this.customEffects,
    this.delay = Duration.zero,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return AnimatedOpacity(
      opacity: 1,
      duration: const Duration(milliseconds: 300),
      child: Text(text, style: style),
    );
  }
}

/// Animated list view with staggered animations
class AnimatedListView extends StatelessWidget {
  final List<Widget> children;
  final ScrollPhysics? physics;
  final EdgeInsets padding;
  final Duration staggerDelay;
  final List<dynamic>? itemEffects;

  const AnimatedListView({
    Key? key,
    required this.children,
    this.physics,
    this.padding = const EdgeInsets.all(0),
    this.staggerDelay = const Duration(milliseconds: 50),
    this.itemEffects,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      physics: physics,
      padding: padding,
      itemCount: children.length,
      itemBuilder: (context, index) {
        return AnimatedOpacity(
          opacity: 1,
          duration: const Duration(milliseconds: 300),
          child: children[index],
        );
      },
    );
  }
}

/// Animated card widget
class AnimatedCard extends StatelessWidget {
  final Widget child;
  final EdgeInsets padding;
  final Color backgroundColor;
  final double borderRadius;
  final List<BoxShadow>? shadows;
  final List<dynamic>? effects;
  final VoidCallback? onTap;

  const AnimatedCard({
    Key? key,
    required this.child,
    this.padding = const EdgeInsets.all(16),
    this.backgroundColor = Colors.white,
    this.borderRadius = 12,
    this.shadows,
    this.effects,
    this.onTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: backgroundColor,
          borderRadius: BorderRadius.circular(borderRadius),
          boxShadow: shadows,
        ),
        child: Padding(
          padding: padding,
          child: child,
        ),
      ),
    );
  }
}

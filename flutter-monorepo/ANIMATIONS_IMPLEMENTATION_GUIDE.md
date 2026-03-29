# Flutter Animations Implementation Guide

## Overview

This guide explains how to integrate animations into the PearlHub Flutter applications using the animation utilities and widgets provided in the `pearl_core` package.

## Getting Started

### 1. Import Animations

All animations are exported from `pearl_core`:

```dart
import 'package:pearl_core/pearl_core.dart';
```

### 2. Available Resources

- **AnimationDurations**: Constants for consistent timing (fast, normal, slow, verySlow)
- **AnimationCurves**: Predefined easing curves (standard, entrance, exit, bounce)
- **AnimationEffects**: Pre-built animation effect methods
- **Animated Widgets**: Ready-to-use widgets (AnimatedButton, AnimatedCard, AnimatedText, etc.)

## Customer App Implementation

### Example: Animated Product List

```dart
import 'package:pearl_core/pearl_core.dart';

class ProductListPage extends StatelessWidget {
  final List<Product> products = [];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Products')),
      body: AnimatedListView(
        children: products
            .map((p) => AnimatedCard(
              effects: AnimationEffects.scaleIn(),
              child: ListTile(
                title: Text(p.name),
                subtitle: Text('\$${p.price}'),
              ),
            ))
            .toList(),
        staggerDelay: Duration(milliseconds: 50),
      ),
    );
  }
}
```

### Example: Animated Checkout Button

```dart
AnimatedButton(
  onPressed: () => _checkout(),
  scale: 0.92,
  child: Container(
    height: 56,
    decoration: BoxDecoration(
      color: Colors.green,
      borderRadius: BorderRadius.circular(12),
    ),
    child: Center(
      child: Text('Complete Purchase'),
    ),
  ),
)
```

### Example: Animated Hero Banner with Page Transition

```dart
Container(
  height: 200,
  child: AnimatedCard(
    effects: AnimationEffects.slideInFromTop(),
    child: Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [Colors.blue.shade400, Colors.blue.shade700],
        ),
      ),
      child: AnimatedText(
        'Welcome to PearlHub',
        style: TextStyle(fontSize: 28, color: Colors.white),
      ),
    ),
  ),
)
```

## Provider App Implementation

### Example: Animated Earnings Dashboard

```dart
import 'package:pearl_core/pearl_core.dart';

class EarningsDashboard extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return AnimatedCard(
      effects: AnimationEffects.scaleIn(),
      child: Column(
        children: [
          AnimatedText(
            'Total Earnings',
            style: TextStyle(fontSize: 14, color: Colors.grey.shade600),
          ),
          SizedBox(height: 8),
          AnimatedText(
            '\$2,450.50',
            style: TextStyle(
              fontSize: 32,
              fontWeight: FontWeight.bold,
              color: Colors.green,
            ),
          ),
        ],
      ),
    );
  }
}
```

### Example: Animated Request Notifications

```dart
AnimatedCard(
  effects: AnimationEffects.slideInFromBottom(),
  backgroundColor: Colors.orange.shade50,
  child: Row(
    children: [
      Icon(Icons.notification_important, color: Colors.orange),
      SizedBox(width: 12),
      Expanded(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            AnimatedText(
              'New Service Request',
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
            Text('John D. requested your premium service'),
          ],
        ),
      ),
    ],
  ),
)
```

### Example: Animated Status Indicator

```dart
bool isOnline = true;

Container(
  child: Row(
    children: [
      Container(
        width: 12,
        height: 12,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          color: isOnline ? Colors.green : Colors.grey,
        ),
      ).animate().pulse(duration: Duration(milliseconds: 800)),
      SizedBox(width: 12),
      AnimatedText(
        isOnline ? 'You are Online' : 'You are Offline',
        style: TextStyle(
          color: isOnline ? Colors.green : Colors.grey,
        ),
      ),
    ],
  ),
)
```

## Admin App Implementation

### Example: Animated Statistics Dashboard

```dart
import 'package:pearl_core/pearl_core.dart';

class StatCard extends StatelessWidget {
  final String title;
  final String value;
  final String change;
  final bool isPositive;

  @override
  Widget build(BuildContext context) {
    return AnimatedCard(
      effects: AnimationEffects.scaleIn(),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(title),
              Icon(
                isPositive ? Icons.trending_up : Icons.trending_down,
                color: isPositive ? Colors.green : Colors.red,
              ),
            ],
          ),
          SizedBox(height: 8),
          AnimatedText(
            value,
            style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold),
          ),
          SizedBox(height: 8),
          Text(
            change,
            style: TextStyle(
              color: isPositive ? Colors.green : Colors.red,
            ),
          ),
        ],
      ),
    );
  }
}
```

### Example: Animated Alerts

```dart
AnimatedCard(
  effects: AnimationEffects.slideInFromTop(),
  backgroundColor: Colors.red.shade100,
  child: Row(
    children: [
      Container(
        width: 4,
        height: 60,
        color: Colors.red,
      ),
      SizedBox(width: 12),
      Expanded(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            AnimatedText(
              'Critical Alert',
              style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold),
            ),
            Text('System health below threshold'),
          ],
        ),
      ),
    ],
  ),
)
```

## Animation Timing Guidelines

### Use Cases for Different Durations

| Duration | Use Case |
|----------|----------|
| `fast` (200ms) | Button presses, toggle switches, quick interactions |
| `normal` (300ms) | Card transitions, page entrance, standard animations |
| `slow` (500ms) | Important transitions, modals, user attention-needed |
| `verySlow` (800ms) | Loading sequences, transitions between major sections |

### Stagger Delays for Lists

- **Rapid list**: 30-50ms between items
- **Standard list**: 50-100ms between items
- **Slow reveal**: 100-150ms between items

```dart
// Fast cascade effect
AnimatedListView(
  children: items,
  staggerDelay: Duration(milliseconds: 30),
)

// Standard effect
AnimatedListView(
  children: items,
  staggerDelay: Duration(milliseconds: 75),
)
```

## Performance Optimization

### Tips for Smooth Animations

1. **Limit simultaneous animations**: Avoid animating more than 5-6 elements at once
2. **Use appropriate curves**: Match animation curve to action type
3. **Test on low-end devices**: Always test animations on lower-spec devices
4. **Consider accessibility**: Add settings to disable animations for users with motion sensitivity

```dart
// Disable animations based on user preference
if (userPrefersReducedMotion) {
  // Use instant transitions instead of animations
  child.build(context);
} else {
  // Apply animations
  AnimatedCard(effects: AnimationEffects.scaleIn(), child: child)
}
```

## Custom Animations

For animations not covered by the pre-built effects, use flutter_animate directly:

```dart
import 'package:flutter_animate/flutter_animate.dart';

widget
  .animate()
  .fadeIn(duration: Duration(milliseconds: 300))
  .scale(begin: Offset(0.5, 0.5))
  .move(begin: Offset(-100, 0), duration: Duration(milliseconds: 300))
```

## Animation Testing

### Testing Animated Widgets

```dart
testWidgets('Animated button scales on press', (WidgetTester tester) async {
  await tester.pumpWidget(
    MaterialApp(
      home: Scaffold(
        body: AnimatedButton(
          onPressed: () {},
          child: Text('Press me'),
        ),
      ),
    ),
  );

  await tester.tap(find.text('Press me'));
  await tester.pumpAndSettle();

  // Verify animation completed
  expect(find.text('Press me'), findsOneWidget);
});
```

## Common Patterns

### Loading State with Animation

```dart
if (isLoading) {
  return Center(
    child: AnimatedLoadingIndicator(),
  );
} else {
  return AnimatedCard(
    effects: AnimationEffects.fadeIn(),
    child: content,
  );
}
```

### Success/Error Notifications

```dart
if (showSuccess) {
  return AnimatedCard(
    effects: AnimationEffects.slideInFromTop(),
    backgroundColor: Colors.green.shade100,
    child: Row(
      children: [
        Icon(Icons.check_circle, color: Colors.green),
        SizedBox(width: 12),
        AnimatedText('Operation successful'),
      ],
    ),
  );
}
```

### Page Transitions

```dart
// Existing page fades out while new page fades in
child.animate().fadeOut(duration: Duration(milliseconds: 300));

// OR use with go_router for automatic transitions
context.go('/next-page');
```

## References

- **Flutter Animate**: https://pub.dev/packages/flutter_animate
- **Flutter Documentation**: https://flutter.dev
- **Material Motion**: https://material.io/design/motion/

## Support

For issues or questions about animations:
1. Check the animation examples in each app
2. Refer to the `pearl_core/lib/src/animations/README.md` for detailed API docs
3. Review the example implementation files in each app's `utils/` directory

## Changelog

- **v1.0.0** - Initial animation utilities and widgets
- **v1.1.0** - Added animation examples for all three apps
- **v1.2.0** - Added performance optimization guidelines

# Animations Guide

This module provides a comprehensive set of animation utilities and widgets for the PearlHub Flutter apps.

## Available Animation Utilities

### AnimationDurations
Predefined duration constants for consistent animation timing:
- `fast`: 200ms
- `normal`: 300ms
- `slow`: 500ms
- `verySlow`: 800ms

### AnimationCurves
Predefined animation curve/easing options:
- `standard`: easeInOut
- `entrance`: easeOut
- `exit`: easeIn
- `bounce`: elasticOut

### AnimationEffects
Pre-built animation effect methods:

#### Fade Effects
```dart
// Fade in
AnimationEffects.fadeIn(duration: Duration(milliseconds: 300))

// Fade out
AnimationEffects.fadeOut(duration: Duration(milliseconds: 300))
```

#### Scale Effects
```dart
// Scale in with fade
AnimationEffects.scaleIn(
  duration: Duration(milliseconds: 300),
  beginScale: 0.8
)

// Scale out with fade
AnimationEffects.scaleOut(
  duration: Duration(milliseconds: 300),
  endScale: 0.8
)
```

#### Slide Effects
```dart
AnimationEffects.slideInFromLeft()
AnimationEffects.slideInFromRight()
AnimationEffects.slideInFromTop()
AnimationEffects.slideInFromBottom()
```

#### Special Effects
```dart
// Bounce in
AnimationEffects.bounceIn()

// Rotate in
AnimationEffects.rotateIn()

// Pulse (continuous)
AnimationEffects.pulse()

// Shimmer (loading effect)
AnimationEffects.shimmer()
```

## Animated Widgets

### AnimatedButton
Button with built-in press animation:
```dart
AnimatedButton(
  onPressed: () => print('Pressed'),
  child: Text('Click Me'),
  scale: 0.95,
  duration: Duration(milliseconds: 150),
)
```

### AnimatedText
Text with fade-in animation:
```dart
AnimatedText(
  'Hello World',
  style: TextStyle(fontSize: 20),
  delay: Duration(milliseconds: 100),
)
```

### AnimatedCard
Card with scale and fade animation:
```dart
AnimatedCard(
  child: Column(
    children: [
      Text('Card Content'),
    ],
  ),
  onTap: () => print('Tapped'),
  backgroundColor: Colors.white,
  borderRadius: 12,
)
```

### AnimatedListView
List view with staggered animations for items:
```dart
AnimatedListView(
  children: List.generate(
    10,
    (index) => ListTile(title: Text('Item $index')),
  ),
  staggerDelay: Duration(milliseconds: 50),
)
```

## Usage Examples

### Basic Widget Animation
```dart
import 'package:pearl_core/pearl_core.dart';

class MyWidget extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return AnimatedCard(
      child: Text('Animated Content'),
      effects: AnimationEffects.scaleIn(),
    );
  }
}
```

### Page Transition with Animation
```dart
class MyPage extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: ListView(
        children: [
          AnimatedText('Title', style: TextStyle(fontSize: 24)),
          SizedBox(height: 16),
          AnimatedCard(
            child: Text('Content Card'),
          ),
        ],
      ),
    );
  }
}
```

### List with Staggered Animation
```dart
class MyListPage extends StatelessWidget {
  final List<String> items = ['Item 1', 'Item 2', 'Item 3'];

  @override
  Widget build(BuildContext context) {
    return AnimatedListView(
      children: items
          .map((item) => ListTile(title: Text(item)))
          .toList(),
      staggerDelay: Duration(milliseconds: 100),
    );
  }
}
```

## Best Practices

1. **Use consistent durations**: Use the predefined `AnimationDurations` constants to maintain consistency across your app.

2. **Avoid over-animating**: Don't animate everything - focus on key user interactions and important content.

3. **Stagger list animations**: Use the `staggerDelay` parameter in `AnimatedListView` to create a cascading effect.

4. **Test performance**: Monitor app performance when using multiple animations, especially on lower-end devices.

5. **Combine effects thoughtfully**: Use fade + scale/slide combinations for professional-looking animations.

6. **Respect accessibility**: Consider users who prefer reduced motion - consider adding a setting to disable animations for users with motion sensitivity.

## Performance Tips

- Use `AnimatedButton` for interactive elements to provide immediate visual feedback
- Limit simultaneous animations to 3-5 elements for smooth performance
- Use `AnimationEffects.shimmer()` only for loading states, not constantly
- Consider using `SingleChildScrollView` instead of `AnimatedListView` for very large lists

## Customization

To create custom animations, use the Flutter Animate library directly:

```dart
import 'package:flutter_animate/flutter_animate.dart';

widget
  .animate()
  .fadeIn(duration: Duration(milliseconds: 300))
  .scale(begin: Offset(0.5, 0.5))
  .move(duration: Duration(milliseconds: 300), begin: Offset(-100, 0))
```

For more information, see: https://pub.dev/packages/flutter_animate

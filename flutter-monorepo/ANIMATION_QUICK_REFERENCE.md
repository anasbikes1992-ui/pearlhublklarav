# Quick Animation Reference

## Import
```dart
import 'package:pearl_core/pearl_core.dart';
```

## Common Animation Patterns

### Fade In Text
```dart
AnimatedText('Hello World', style: TextStyle(fontSize: 20))
```

### Scale In Card
```dart
AnimatedCard(
  child: YourContent(),
  effects: AnimationEffects.scaleIn(),
)
```

### Slide In From Bottom
```dart
Container(
  child: YourWidget(),
)
.animate()
.slideInFromBottom(duration: AnimationDurations.normal)
```

### Animated Button with Press Effect
```dart
AnimatedButton(
  onPressed: () => handlePress(),
  child: ElevatedButton(...),
)
```

### List with Staggered Animations
```dart
AnimatedListView(
  children: items.map((item) => ItemWidget(item)).toList(),
  staggerDelay: Duration(milliseconds: 50),
)
```

### Pulse Loading Indicator
```dart
const SizedBox(width: 50)
    .animate()
    .pulse(duration: Duration(milliseconds: 1000))
```

## Available Durations
- `AnimationDurations.fast` - 200ms
- `AnimationDurations.normal` - 300ms (default)
- `AnimationDurations.slow` - 500ms
- `AnimationDurations.verySlow` - 800ms

## Available Effects

### Fade
- `AnimationEffects.fadeIn()`
- `AnimationEffects.fadeOut()`

### Scale
- `AnimationEffects.scaleIn()`
- `AnimationEffects.scaleOut()`

### Slide
- `AnimationEffects.slideInFromLeft()`
- `AnimationEffects.slideInFromRight()`
- `AnimationEffects.slideInFromTop()`
- `AnimationEffects.slideInFromBottom()`

### Special
- `AnimationEffects.bounceIn()`
- `AnimationEffects.rotateIn()`
- `AnimationEffects.pulse()`
- `AnimationEffects.shimmer()`

## Animated Widgets

### AnimatedCard
```dart
AnimatedCard(
  child: Container(...),
  effects: AnimationEffects.scaleIn(),
  onTap: () => handleTap(),
  backgroundColor: Colors.white,
  borderRadius: 12,
)
```

### AnimatedButton
```dart
AnimatedButton(
  onPressed: () => {},
  scale: 0.92,
  duration: Duration(milliseconds: 150),
  child: Container(...),
)
```

### AnimatedText
```dart
AnimatedText(
  'Text content',
  style: TextStyle(...),
  delay: Duration(milliseconds: 100),
)
```

### AnimatedListView
```dart
AnimatedListView(
  children: widgets,
  staggerDelay: Duration(milliseconds: 75),
  padding: EdgeInsets.all(16),
)
```

## Pro Tips

1. **Combine effects**: Scale + Fade creates professional look
2. **Use stagger delays**: 50-100ms between list items
3. **Match animation to action**: Use `entrance` curve for appearing, `exit` for disappearing
4. **Test performance**: Always test on low-end devices
5. **Respect preferences**: Allow users to disable animations

## Custom Animation Example
```dart
widget
  .animate()
  .fadeIn(duration: Duration(milliseconds: 300))
  .scale(begin: Offset(0.8, 0.8))
  .move(begin: Offset(-50, 0))
```

## Disable Animations for Accessibility
```dart
if (MediaQuery.of(context).disableAnimations) {
  // Show without animation
  return YourWidget();
} else {
  // Show with animation
  return AnimatedCard(child: YourWidget());
}
```

## Links
- Full Guide: `ANIMATIONS_IMPLEMENTATION_GUIDE.md`
- API Docs: `pearl_core/lib/src/animations/README.md`
- Examples: Check `apps/{app}/lib/utils/animation_examples.dart`

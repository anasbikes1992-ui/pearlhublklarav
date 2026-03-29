import 'package:flutter/material.dart';
import 'package:pearl_core/pearl_core.dart';

/// Example animations for the customer app
/// This file demonstrates how to use animations throughout the app
class CustomerAnimationExamples {
  /// Example: Animated home page hero banner
  static Widget animatedHeroBanner() {
    return AnimatedCard(
      effects: AnimationEffects.slideInFromTop(),
      borderRadius: 20,
      child: Container(
        height: 200,
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [Colors.blue.shade400, Colors.blue.shade700],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: Center(
          child: AnimatedText(
            'Welcome to PearlHub',
            style: TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
        ),
      ),
    );
  }

  /// Example: Animated product card
  static Widget animatedProductCard(String productName, String price) {
    return AnimatedCard(
      effects: AnimationEffects.scaleIn(),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            height: 150,
            decoration: BoxDecoration(
              color: Colors.grey.shade200,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Center(
              child: Icon(Icons.shopping_bag, size: 60),
            ),
          ),
          SizedBox(height: 12),
          AnimatedText(
            productName,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
            ),
          ),
          SizedBox(height: 8),
          AnimatedText(
            price,
            style: TextStyle(
              fontSize: 14,
              color: Colors.blue,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
      onTap: () {
        // Handle product tap
      },
    );
  }

  /// Example: Animated checkout button
  static Widget animatedCheckoutButton(VoidCallback onPressed) {
    return AnimatedButton(
      onPressed: onPressed,
      duration: Duration(milliseconds: 150),
      scale: 0.92,
      child: Container(
        height: 56,
        decoration: BoxDecoration(
          color: Colors.green,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Center(
          child: Text(
            'Proceed to Checkout',
            style: TextStyle(
              color: Colors.white,
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
      ),
    );
  }

  /// Example: Animated order history list
  static Widget animatedOrderHistoryList(List<String> orders) {
    return AnimatedListView(
      staggerDelay: Duration(milliseconds: 50),
      children: orders
          .map((order) => Container(
                margin: EdgeInsets.symmetric(vertical: 8),
                padding: EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          order,
                          style: TextStyle(fontWeight: FontWeight.bold),
                        ),
                        SizedBox(height: 4),
                        Text(
                          'Order placed 2 days ago',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ],
                    ),
                    Icon(Icons.arrow_forward_ios, size: 16),
                  ],
                ),
              ))
          .toList(),
    );
  }

  /// Example: Animated loading indicator
  static Widget animatedLoadingIndicator() {
    return Center(
      child: Container(
        width: 60,
        height: 60,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          border: Border.all(
            color: Colors.blue.shade300,
            width: 4,
          ),
        ),
        child: Icon(
          Icons.shopping_bag,
          color: Colors.blue,
          size: 30,
        ),
      ).animate().pulse(),
    );
  }

  /// Example: Animated error message
  static Widget animatedErrorMessage(String message) {
    return AnimatedCard(
      effects: AnimationEffects.slideInFromBottom(),
      backgroundColor: Colors.red.shade100,
      child: Row(
        children: [
          Icon(Icons.error, color: Colors.red),
          SizedBox(width: 12),
          Expanded(
            child: AnimatedText(
              message,
              style: TextStyle(
                color: Colors.red,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }

  /// Example: Animated success notification
  static Widget animatedSuccessNotification(String message) {
    return AnimatedCard(
      effects: AnimationEffects.slideInFromTop(),
      backgroundColor: Colors.green.shade100,
      child: Row(
        children: [
          Icon(Icons.check_circle, color: Colors.green),
          SizedBox(width: 12),
          Expanded(
            child: AnimatedText(
              message,
              style: TextStyle(
                color: Colors.green,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// Demo page showing all animation examples
class AnimationDemoPage extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Animation Examples',
          style: TextStyle(color: Colors.white),
        ),
        backgroundColor: Colors.blue,
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            AnimatedText(
              'Hero Banner',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            CustomerAnimationExamples.animatedHeroBanner(),
            SizedBox(height: 24),
            AnimatedText(
              'Product Cards',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            CustomerAnimationExamples.animatedProductCard(
              'Premium Headphones',
              '\$149.99',
            ),
            SizedBox(height: 12),
            CustomerAnimationExamples.animatedProductCard(
              'Wireless Earbuds',
              '\$79.99',
            ),
            SizedBox(height: 24),
            AnimatedText(
              'Checkout Button',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            CustomerAnimationExamples.animatedCheckoutButton(
              () => ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('Checkout pressed')),
              ),
            ),
            SizedBox(height: 24),
            AnimatedText(
              'Order History',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            SingleChildScrollView(
              child: SizedBox(
                height: 300,
                child: CustomerAnimationExamples.animatedOrderHistoryList([
                  'Order #1001 - \$249.99',
                  'Order #1002 - \$89.99',
                  'Order #1003 - \$159.99',
                ]),
              ),
            ),
            SizedBox(height: 24),
            AnimatedText(
              'Status Messages',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            CustomerAnimationExamples.animatedSuccessNotification(
              'Order placed successfully!',
            ),
            SizedBox(height: 12),
            CustomerAnimationExamples.animatedErrorMessage(
              'Payment failed. Please try again.',
            ),
            SizedBox(height: 24),
          ],
        ),
      ),
    );
  }
}

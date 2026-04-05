import 'package:flutter/material.dart';
import 'package:pearl_core/pearl_core.dart';

/// Example animations for the provider app
/// This file demonstrates how to use animations throughout the app
class ProviderAnimationExamples {
  /// Example: Animated earnings card
  static Widget animatedEarningsCard(double totalEarnings, double todayEarnings) {
    return AnimatedCard(
      effects: AnimationEffects.scaleIn(),
      backgroundColor: Colors.green.shade50,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          AnimatedText(
            'Total Earnings',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey.shade600,
              fontWeight: FontWeight.w500,
            ),
          ),
          SizedBox(height: 8),
          AnimatedText(
            '\$${totalEarnings.toStringAsFixed(2)}',
            style: TextStyle(
              fontSize: 32,
              color: Colors.green,
              fontWeight: FontWeight.bold,
            ),
          ),
          SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Today',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey.shade600,
                    ),
                  ),
                  SizedBox(height: 4),
                  Text(
                    '\$${todayEarnings.toStringAsFixed(2)}',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors.green,
                    ),
                  ),
                ],
              ),
              Container(
                padding: EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.green.shade100,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(Icons.trending_up, color: Colors.green),
              ),
            ],
          ),
        ],
      ),
    );
  }

  /// Example: Animated service item
  static Widget animatedServiceItem(
    String serviceName,
    int activeRequests,
    double rating,
  ) {
    return AnimatedCard(
      effects: AnimationEffects.slideInFromLeft(),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                AnimatedText(
                  serviceName,
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                SizedBox(height: 4),
                Row(
                  children: [
                    Icon(Icons.star, size: 16, color: Colors.orange),
                    SizedBox(width: 4),
                    AnimatedText(
                      rating.toStringAsFixed(1),
                      style: TextStyle(fontSize: 14),
                    ),
                    SizedBox(width: 12),
                    Container(
                      padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.blue.shade100,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        '$activeRequests active',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.blue,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          Icon(Icons.arrow_forward_ios, size: 16, color: Colors.grey),
        ],
      ),
    );
  }

  /// Example: Animated accept request button
  static Widget animatedAcceptButton(VoidCallback onPressed) {
    return AnimatedButton(
      onPressed: onPressed,
      scale: 0.92,
      child: Container(
        height: 56,
        decoration: BoxDecoration(
          color: Colors.green,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Center(
          child: Text(
            'Accept Request',
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

  /// Example: Animated reject request button
  static Widget animatedRejectButton(VoidCallback onPressed) {
    return AnimatedButton(
      onPressed: onPressed,
      scale: 0.92,
      child: Container(
        height: 56,
        decoration: BoxDecoration(
          color: Colors.red,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Center(
          child: Text(
            'Reject Request',
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

  /// Example: Animated pending request list
  static Widget animatedPendingRequestsList(List<String> requests) {
    return AnimatedListView(
      staggerDelay: Duration(milliseconds: 75),
      children: requests
          .map((request) => Container(
                margin: EdgeInsets.symmetric(vertical: 8),
                padding: EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.orange.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.orange.shade200),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(
                            request,
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                            ),
                          ),
                        ),
                        Container(
                          padding: EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.orange,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            'Pending',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                    SizedBox(height: 8),
                    Text(
                      'Requested 5 minutes ago',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ))
          .toList(),
    );
  }

  /// Example: Animated active status indicator
  static Widget animatedActiveStatusIndicator(bool isActive) {
    return AnimatedCard(
      backgroundColor: isActive ? Colors.green.shade50 : Colors.grey.shade100,
      child: Row(
        children: [
          Container(
            width: 12,
            height: 12,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: isActive ? Colors.green : Colors.grey,
            ),
          ),
          SizedBox(width: 12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              AnimatedText(
                isActive ? 'You are Online' : 'You are Offline',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: isActive ? Colors.green : Colors.grey,
                ),
              ),
              Text(
                isActive
                    ? 'Ready to accept requests'
                    : 'Not receiving requests',
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey.shade600,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  /// Example: Animated completion notification
  static Widget animatedCompletionNotification(String message) {
    return AnimatedCard(
      effects: AnimationEffects.bounceIn(),
      backgroundColor: Colors.blue.shade100,
      child: Row(
        children: [
          Icon(Icons.check_circle, color: Colors.blue, size: 24),
          SizedBox(width: 12),
          Expanded(
            child: AnimatedText(
              message,
              style: TextStyle(
                color: Colors.blue,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          Icon(Icons.close, size: 20, color: Colors.grey),
        ],
      ),
    );
  }

  /// Example: Animated rating summary
  static Widget animatedRatingSummary(
    double averageRating,
    int totalReviews,
  ) {
    return AnimatedCard(
      effects: AnimationEffects.scaleIn(),
      backgroundColor: Colors.blue.shade50,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          AnimatedText(
            'Customer Rating',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey.shade600,
              fontWeight: FontWeight.w500,
            ),
          ),
          SizedBox(height: 12),
          Row(
            children: [
              AnimatedText(
                averageRating.toStringAsFixed(1),
                style: TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  color: Colors.blue,
                ),
              ),
              SizedBox(width: 8),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: List.generate(5, (index) {
                      return Icon(
                        Icons.star,
                        size: 16,
                        color: index < averageRating.toInt()
                            ? Colors.orange
                            : Colors.grey.shade300,
                      );
                    }),
                  ),
                  SizedBox(height: 4),
                  Text(
                    'from $totalReviews reviews',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey.shade600,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }
}

/// Demo page showing all animation examples for provider app
class ProviderAnimationDemoPage extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Provider Animations',
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
              'Earnings Overview',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            ProviderAnimationExamples.animatedEarningsCard(2450.50, 125.75),
            SizedBox(height: 24),
            AnimatedText(
              'Status',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            ProviderAnimationExamples.animatedActiveStatusIndicator(true),
            SizedBox(height: 24),
            AnimatedText(
              'Services',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            ProviderAnimationExamples.animatedServiceItem(
              'Premium Cleaning',
              5,
              4.8,
            ),
            SizedBox(height: 12),
            ProviderAnimationExamples.animatedServiceItem(
              'Regular Maintenance',
              3,
              4.5,
            ),
            SizedBox(height: 24),
            AnimatedText(
              'Pending Requests',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            SizedBox(
              height: 400,
              child: SingleChildScrollView(
                child:
                    ProviderAnimationExamples.animatedPendingRequestsList([
                  'Request #001 - John D.',
                  'Request #002 - Sarah M.',
                  'Request #003 - Mike L.',
                ]),
              ),
            ),
            SizedBox(height: 24),
            AnimatedText(
              'Rating Summary',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            ProviderAnimationExamples.animatedRatingSummary(4.7, 128),
            SizedBox(height: 24),
          ],
        ),
      ),
    );
  }
}

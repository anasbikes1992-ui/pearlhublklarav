import 'package:flutter/material.dart';
import 'package:pearl_core/pearl_core.dart';

/// Example animations for the admin app
/// This file demonstrates how to use animations throughout the app
class AdminAnimationExamples {
  /// Example: Animated statistics card
  static Widget animatedStatsCard(
    String title,
    String value,
    String change,
    bool isPositive,
  ) {
    return AnimatedCard(
      effects: AnimationEffects.scaleIn(),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              AnimatedText(
                title,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey.shade600,
                  fontWeight: FontWeight.w500,
                ),
              ),
              Icon(
                isPositive ? Icons.trending_up : Icons.trending_down,
                color: isPositive ? Colors.green : Colors.red,
                size: 18,
              ),
            ],
          ),
          SizedBox(height: 8),
          AnimatedText(
            value,
            style: TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.bold,
            ),
          ),
          SizedBox(height: 8),
          AnimatedText(
            change,
            style: TextStyle(
              fontSize: 12,
              color: isPositive ? Colors.green : Colors.red,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  /// Example: Animated user management card
  static Widget animatedUserManagementCard(
    String userName,
    String userEmail,
    String status,
    String joinDate,
  ) {
    return AnimatedCard(
      effects: AnimationEffects.slideInFromLeft(),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  AnimatedText(
                    userName,
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  SizedBox(height: 4),
                  Text(
                    userEmail,
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey.shade600,
                    ),
                  ),
                ],
              ),
              Container(
                padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: status == 'Active'
                      ? Colors.green.shade100
                      : Colors.orange.shade100,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  status,
                  style: TextStyle(
                    fontSize: 12,
                    color: status == 'Active' ? Colors.green : Colors.orange,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ),
          SizedBox(height: 12),
          Row(
            children: [
              Icon(Icons.calendar_today, size: 14, color: Colors.grey),
              SizedBox(width: 6),
              Text(
                joinDate,
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

  /// Example: Animated action button (block/unblock user)
  static Widget animatedActionButton(
    String label,
    VoidCallback onPressed,
    Color buttonColor,
  ) {
    return AnimatedButton(
      onPressed: onPressed,
      scale: 0.92,
      child: Container(
        height: 44,
        decoration: BoxDecoration(
          color: buttonColor,
          borderRadius: BorderRadius.circular(8),
        ),
        child: Center(
          child: Text(
            label,
            style: TextStyle(
              color: Colors.white,
              fontSize: 14,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
      ),
    );
  }

  /// Example: Animated system health indicator
  static Widget animatedSystemHealthCard(
    double systemHealth,
    bool isHealthy,
  ) {
    return AnimatedCard(
      effects: AnimationEffects.scaleIn(),
      backgroundColor: isHealthy ? Colors.green.shade50 : Colors.red.shade50,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          AnimatedText(
            'System Health',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey.shade600,
              fontWeight: FontWeight.w500,
            ),
          ),
          SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: LinearProgressIndicator(
                    value: systemHealth / 100,
                    minHeight: 10,
                    backgroundColor: Colors.grey.shade300,
                    valueColor: AlwaysStoppedAnimation<Color>(
                      isHealthy ? Colors.green : Colors.red,
                    ),
                  ),
                ),
              ),
              SizedBox(width: 12),
              AnimatedText(
                '${systemHealth.toStringAsFixed(0)}%',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: isHealthy ? Colors.green : Colors.red,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  /// Example: Animated reports list
  static Widget animatedReportsList(List<String> reports) {
    return AnimatedListView(
      staggerDelay: Duration(milliseconds: 60),
      children: reports
          .map((report) => Container(
                margin: EdgeInsets.symmetric(vertical: 8),
                padding: EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.blue.shade50,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(
                            report,
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                            ),
                          ),
                        ),
                        Icon(Icons.file_download, size: 18, color: Colors.blue),
                      ],
                    ),
                    SizedBox(height: 8),
                    Text(
                      'Generated today',
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

  /// Example: Animated alert card
  static Widget animatedAlertCard(
    String title,
    String message,
    String severity,
  ) {
    final Color alertColor = severity == 'Critical'
        ? Colors.red
        : severity == 'Warning'
            ? Colors.orange
            : Colors.yellow;

    return AnimatedCard(
      effects: AnimationEffects.slideInFromTop(),
      backgroundColor: alertColor.withOpacity(0.1),
      child: Row(
        children: [
          Container(
            width: 4,
            height: 60,
            decoration: BoxDecoration(
              color: alertColor,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                AnimatedText(
                  title,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: alertColor,
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  message,
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey.shade600,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
          Icon(Icons.close, size: 20, color: Colors.grey),
        ],
      ),
    );
  }

  /// Example: Animated activity log
  static Widget animatedActivityLog(List<String> activities) {
    return AnimatedListView(
      staggerDelay: Duration(milliseconds: 50),
      children: activities
          .asMap()
          .entries
          .map((entry) {
            int index = entry.key;
            String activity = entry.value;
            return Container(
              margin: EdgeInsets.symmetric(vertical: 8),
              child: Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: Colors.blue.shade100,
                    ),
                    child: Icon(
                      Icons.check,
                      color: Colors.blue,
                      size: 20,
                    ),
                  ),
                  SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          activity,
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                        SizedBox(height: 4),
                        Text(
                          '${(5 - index) * 10} minutes ago',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            );
          })
          .toList(),
    );
  }

  /// Example: Animated analytics summary
  static Widget animatedAnalyticsSummary(
    int totalUsers,
    int activeUsers,
    int totalTransactions,
    double totalRevenue,
  ) {
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: animatedStatsCard(
                'Total Users',
                totalUsers.toString(),
                '+12% vs last month',
                true,
              ),
            ),
            SizedBox(width: 12),
            Expanded(
              child: animatedStatsCard(
                'Active Users',
                activeUsers.toString(),
                '+8% vs last month',
                true,
              ),
            ),
          ],
        ),
        SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: animatedStatsCard(
                'Transactions',
                totalTransactions.toString(),
                '+24% vs last month',
                true,
              ),
            ),
            SizedBox(width: 12),
            Expanded(
              child: animatedStatsCard(
                'Revenue',
                '\$${totalRevenue.toStringAsFixed(0)}k',
                '+18% vs last month',
                true,
              ),
            ),
          ],
        ),
      ],
    );
  }
}

/// Demo page showing all animation examples for admin app
class AdminAnimationDemoPage extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Admin Animations',
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
              'Analytics Summary',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            AdminAnimationExamples.animatedAnalyticsSummary(
              15430,
              8920,
              5230,
              182.5,
            ),
            SizedBox(height: 24),
            AnimatedText(
              'System Health',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            AdminAnimationExamples.animatedSystemHealthCard(94, true),
            SizedBox(height: 24),
            AnimatedText(
              'Alerts',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            AdminAnimationExamples.animatedAlertCard(
              'Database Connection',
              'Secondary database connection lost',
              'Warning',
            ),
            SizedBox(height: 12),
            AdminAnimationExamples.animatedAlertCard(
              'API Error Rate',
              'Error rate exceeded threshold',
              'Critical',
            ),
            SizedBox(height: 24),
            AnimatedText(
              'Recent Activity',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            SizedBox(
              height: 300,
              child: SingleChildScrollView(
                child: AdminAnimationExamples.animatedActivityLog([
                  'Admin login from 192.168.1.1',
                  'User blocked: user123@example.com',
                  'System backup completed',
                  'Security patch applied',
                  'User report reviewed',
                ]),
              ),
            ),
            SizedBox(height: 24),
          ],
        ),
      ),
    );
  }
}

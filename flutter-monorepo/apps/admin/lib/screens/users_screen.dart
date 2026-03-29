import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/models.dart';
import '../services/admin_service.dart';

class UsersScreen extends StatefulWidget {
  const UsersScreen({super.key});

  @override
  State<UsersScreen> createState() => _UsersScreenState();
}

class _UsersScreenState extends State<UsersScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  static const _roles = ['all', 'customer', 'provider'];

  List<AdminUser> _users = [];
  bool _loading = true;
  String? _error;
  int _selectedTab = 0;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(
      length: _roles.length,
      vsync: this,
    )..addListener(() {
        if (!_tabController.indexIsChanging) {
          setState(() => _selectedTab = _tabController.index);
          _loadUsers();
        }
      });
    _loadUsers();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadUsers() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final service = context.read<AdminApiService>();
      final role = _roles[_selectedTab] == 'all' ? null : _roles[_selectedTab];
      final data = await service.getUsers(role: role);
      setState(() {
        _users = data;
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Failed to load users.';
        _loading = false;
      });
    }
  }

  Future<void> _toggleStatus(AdminUser user) async {
    try {
      await context
          .read<AdminApiService>()
          .toggleUserStatus(user.id, !user.isActive);
      await _loadUsers();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
                '${user.fullName} ${user.isActive ? 'deactivated' : 'activated'}.'),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('Update failed: $e')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0a0e27),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1a232f),
        title: const Text('Users',
            style: TextStyle(
                color: Colors.white, fontWeight: FontWeight.bold)),
        bottom: TabBar(
          controller: _tabController,
          labelColor: const Color(0xFF00d4ff),
          unselectedLabelColor: const Color(0xFF8899aa),
          indicatorColor: const Color(0xFF00d4ff),
          tabs: _roles
              .map((r) => Tab(text: r[0].toUpperCase() + r.substring(1)))
              .toList(),
        ),
      ),
      body: _loading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFF00d4ff)))
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_error!,
                          style: const TextStyle(color: Colors.red)),
                      const SizedBox(height: 12),
                      ElevatedButton(
                          onPressed: _loadUsers,
                          child: const Text('Retry')),
                    ],
                  ),
                )
              : _users.isEmpty
                  ? const Center(
                      child: Text('No users found.',
                          style: TextStyle(color: Color(0xFF8899aa))))
                  : RefreshIndicator(
                      onRefresh: _loadUsers,
                      color: const Color(0xFF00d4ff),
                      child: ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemCount: _users.length,
                        separatorBuilder: (_, __) =>
                            const SizedBox(height: 8),
                        itemBuilder: (_, i) => _UserTile(
                          user: _users[i],
                          onToggle: () => _toggleStatus(_users[i]),
                        ),
                      ),
                    ),
    );
  }
}

class _UserTile extends StatelessWidget {
  final AdminUser user;
  final VoidCallback onToggle;

  const _UserTile({required this.user, required this.onToggle});

  Color _roleColor(String role) => switch (role) {
        'admin' => const Color(0xFFd4af37),
        'provider' => const Color(0xFF00d4ff),
        _ => const Color(0xFF8899aa),
      };

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFF1a232f),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFF2a3545)),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      child: Row(
        children: [
          CircleAvatar(
            radius: 22,
            backgroundColor:
                _roleColor(user.role).withOpacity(0.15),
            child: Text(
              user.fullName.isNotEmpty
                  ? user.fullName[0].toUpperCase()
                  : '?',
              style: TextStyle(
                  color: _roleColor(user.role),
                  fontWeight: FontWeight.bold),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(user.fullName,
                    style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w600)),
                const SizedBox(height: 2),
                Text(user.email,
                    style: const TextStyle(
                        color: Color(0xFF8899aa), fontSize: 12),
                    overflow: TextOverflow.ellipsis),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 6, vertical: 1),
                  decoration: BoxDecoration(
                    color:
                        _roleColor(user.role).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(user.role,
                      style: TextStyle(
                          color: _roleColor(user.role),
                          fontSize: 11,
                          fontWeight: FontWeight.w600)),
                ),
              ],
            ),
          ),
          Switch(
            value: user.isActive,
            onChanged: (_) => onToggle(),
            activeColor: const Color(0xFF00c896),
            inactiveThumbColor: const Color(0xFF8899aa),
          ),
        ],
      ),
    );
  }
}

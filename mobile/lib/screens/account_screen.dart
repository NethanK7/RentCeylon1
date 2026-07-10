import 'package:flutter/material.dart';
import '../api.dart';
import '../theme.dart';
import '../wishlist_store.dart';
import 'login_screen.dart';

class AccountScreen extends StatefulWidget {
  const AccountScreen({super.key});
  @override
  State<AccountScreen> createState() => _AccountScreenState();
}

class _AccountScreenState extends State<AccountScreen> {
  Map<String, dynamic>? _me;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final me = ApiService.instance.isLoggedIn ? await ApiService.instance.me() : null;
    if (!mounted) return;
    setState(() {
      _me = me;
      _loading = false;
    });
  }

  Future<void> _logout() async {
    await ApiService.instance.logout();
    WishlistStore.instance.clear();
    _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Account', style: TextStyle(fontWeight: FontWeight.w700))),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _me == null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const CircleAvatar(radius: 36, backgroundColor: AppColors.navy50, child: Icon(Icons.person, size: 36, color: AppColors.navy700)),
                        const SizedBox(height: 16),
                        const Text('Log in or sign up', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
                        const SizedBox(height: 6),
                        Text('Manage bookings, save favourites and list your own items.',
                            textAlign: TextAlign.center, style: TextStyle(color: Colors.grey.shade600)),
                        const SizedBox(height: 20),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton(
                            onPressed: () async {
                              await Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
                              _load();
                            },
                            child: const Text('Log in'),
                          ),
                        ),
                      ],
                    ),
                  ),
                )
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 30,
                          backgroundColor: AppColors.navy800,
                          child: Text(
                            (_me!['name'] as String? ?? '?').substring(0, 1).toUpperCase(),
                            style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.w700),
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(_me!['name'] ?? '', style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
                              Text(_me!['email'] ?? '', style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                              const SizedBox(height: 4),
                              _RoleChip(role: (_me!['role'] as String? ?? 'renter')),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),
                    const _SectionLabel('Referral'),
                    _Tile(icon: Icons.card_giftcard, title: 'Your referral code', subtitle: _me!['referral_code'] ?? '—'),
                    const SizedBox(height: 20),
                    const _SectionLabel('Account'),
                    _Tile(icon: Icons.badge_outlined, title: 'ID verification', subtitle: (_me!['id_verification_status'] as String? ?? 'unsubmitted')),
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton(onPressed: _logout, child: const Text('Log out')),
                    ),
                  ],
                ),
    );
  }
}

class _RoleChip extends StatelessWidget {
  final String role;
  const _RoleChip({required this.role});
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(color: AppColors.navy50, borderRadius: BorderRadius.circular(20)),
      child: Text(role[0].toUpperCase() + role.substring(1),
          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.navy700)),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  final String text;
  const _SectionLabel(this.text);
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Text(text.toUpperCase(), style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: Colors.grey.shade500, letterSpacing: 0.5)),
    );
  }
}

class _Tile extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  const _Tile({required this.icon, required this.title, required this.subtitle});
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(border: Border.all(color: const Color(0xFFE5E7EB)), borderRadius: BorderRadius.circular(14)),
      child: Row(
        children: [
          Icon(icon, color: AppColors.navy700),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(fontWeight: FontWeight.w600)),
                Text(subtitle, style: TextStyle(fontSize: 12.5, color: Colors.grey.shade600)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

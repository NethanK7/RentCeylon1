import 'package:flutter/material.dart';
import '../api.dart';
import '../theme.dart';
import '../wishlist_store.dart';

/// A pure login form, always pushed as a route (from the Account tab, or
/// from a "log in to continue" prompt on Wishlists/Trips).
class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _email = TextEditingController(text: 'amaya@example.com');
  final _password = TextEditingController(text: 'password');
  bool _busy = false;
  String? _error;

  final _api = ApiService.instance;

  @override
  void initState() {
    super.initState();
    if (_api.isLoggedIn) {
      WidgetsBinding.instance.addPostFrameCallback((_) => Navigator.pop(context));
    }
  }

  Future<void> _submit() async {
    setState(() { _busy = true; _error = null; });
    try {
      final res = await _api.login(_email.text.trim(), _password.text);
      if (_api.isLoggedIn) {
        await WishlistStore.instance.refresh();
        if (mounted) Navigator.pop(context);
      } else {
        setState(() => _error = res['message'] ?? 'Invalid credentials.');
      }
    } catch (e) {
      setState(() => _error = 'Could not connect. Is the API running?');
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Log in to RentCeylon', style: TextStyle(fontWeight: FontWeight.w700))),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            TextField(
              controller: _email,
              decoration: const InputDecoration(labelText: 'Email'),
              keyboardType: TextInputType.emailAddress,
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _password,
              obscureText: true,
              decoration: const InputDecoration(labelText: 'Password'),
            ),
            if (_error != null) ...[
              const SizedBox(height: 12),
              Text(_error!, style: const TextStyle(color: AppColors.rose600)),
            ],
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _busy ? null : _submit,
                child: _busy
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Text('Log in'),
              ),
            ),
            const SizedBox(height: 12),
            const Text('Demo: amaya@example.com / password', style: TextStyle(fontSize: 12, color: Colors.grey)),
          ],
        ),
      ),
    );
  }
}

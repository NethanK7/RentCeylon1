import 'package:flutter/material.dart';
import '../api.dart';

const _brand = Color(0xFFFF385C);

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

  Future<void> _submit() async {
    setState(() { _busy = true; _error = null; });
    try {
      final res = await _api.login(_email.text.trim(), _password.text);
      if (_api.isLoggedIn) {
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
    if (_api.isLoggedIn) {
      return Scaffold(
        appBar: AppBar(title: const Text('Account')),
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.check_circle, color: _brand, size: 64),
              const SizedBox(height: 12),
              const Text('You are logged in.'),
              const SizedBox(height: 16),
              OutlinedButton(
                onPressed: () async {
                  await _api.logout();
                  setState(() {});
                },
                child: const Text('Log out'),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Log in to RentCeylon')),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            TextField(
              controller: _email,
              decoration: const InputDecoration(labelText: 'Email', border: OutlineInputBorder()),
              keyboardType: TextInputType.emailAddress,
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _password,
              obscureText: true,
              decoration: const InputDecoration(labelText: 'Password', border: OutlineInputBorder()),
            ),
            if (_error != null) ...[
              const SizedBox(height: 12),
              Text(_error!, style: const TextStyle(color: _brand)),
            ],
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                    backgroundColor: _brand, foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14)),
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

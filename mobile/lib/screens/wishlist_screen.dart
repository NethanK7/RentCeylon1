import 'package:flutter/material.dart';
import '../api.dart';
import '../models.dart';
import '../wishlist_store.dart';
import '../widgets/listing_card.dart';
import 'login_screen.dart';

class WishlistScreen extends StatefulWidget {
  const WishlistScreen({super.key});
  @override
  State<WishlistScreen> createState() => _WishlistScreenState();
}

class _WishlistScreenState extends State<WishlistScreen> {
  List<ListingCard>? _listings;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    if (!ApiService.instance.isLoggedIn) {
      setState(() => _loading = false);
      return;
    }
    setState(() => _loading = true);
    try {
      final listings = await ApiService.instance.wishlist();
      if (!mounted) return;
      setState(() {
        _listings = listings;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Wishlists', style: TextStyle(fontWeight: FontWeight.w700))),
      body: !ApiService.instance.isLoggedIn
          ? _SignInPrompt(onSignedIn: _load)
          : RefreshIndicator(
              onRefresh: () async {
                await WishlistStore.instance.refresh();
                await _load();
              },
              child: _loading
                  ? const Center(child: CircularProgressIndicator())
                  : (_listings == null || _listings!.isEmpty)
                      ? ListView(children: const [
                          SizedBox(height: 100),
                          Icon(Icons.favorite_border, size: 56, color: Colors.grey),
                          SizedBox(height: 12),
                          Center(child: Text('Nothing saved yet.\nTap the heart on any listing to save it here.',
                              textAlign: TextAlign.center, style: TextStyle(color: Colors.grey))),
                        ])
                      : GridView.builder(
                          padding: const EdgeInsets.fromLTRB(14, 12, 14, 20),
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            childAspectRatio: 0.68,
                            crossAxisSpacing: 14,
                            mainAxisSpacing: 18,
                          ),
                          itemCount: _listings!.length,
                          itemBuilder: (_, i) => ListingCardWidget(listing: _listings![i]),
                        ),
            ),
    );
  }
}

class _SignInPrompt extends StatelessWidget {
  final VoidCallback onSignedIn;
  const _SignInPrompt({required this.onSignedIn});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.favorite_border, size: 56, color: Colors.grey),
            const SizedBox(height: 12),
            const Text('Log in to see your saved items', textAlign: TextAlign.center),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () async {
                await Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
                onSignedIn();
              },
              child: const Text('Log in'),
            ),
          ],
        ),
      ),
    );
  }
}

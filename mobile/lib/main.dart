import 'package:flutter/material.dart';
import 'api.dart';
import 'config.dart';
import 'models.dart';
import 'screens/listing_screen.dart';
import 'screens/login_screen.dart';

const brand = Color(0xFFFF385C);
const ceylon = Color(0xFF0D9488);

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await ApiService.instance.loadToken();
  runApp(const RentCeylonApp());
}

class RentCeylonApp extends StatelessWidget {
  const RentCeylonApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: Config.brandName,
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(seedColor: brand, primary: brand),
        scaffoldBackgroundColor: Colors.white,
      ),
      home: const BrowseScreen(),
    );
  }
}

class BrowseScreen extends StatefulWidget {
  const BrowseScreen({super.key});
  @override
  State<BrowseScreen> createState() => _BrowseScreenState();
}

class _BrowseScreenState extends State<BrowseScreen> {
  final _api = ApiService.instance;
  List<Category> _categories = [];
  List<ListingCard> _listings = [];
  String? _activeCategory;
  String _query = '';
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final cats = await _api.categories();
      final listings = await _api.listings(category: _activeCategory, q: _query);
      setState(() {
        _categories = cats;
        _listings = listings;
        _loading = false;
      });
    } catch (e) {
      setState(() => _loading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Could not reach API. Is Laravel running? ($e)')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text.rich(TextSpan(children: [
          TextSpan(text: 'Rent', style: TextStyle(color: brand, fontWeight: FontWeight.w800)),
          TextSpan(text: 'Ceylon', style: TextStyle(color: Colors.black87, fontWeight: FontWeight.w800)),
        ])),
        actions: [
          IconButton(
            icon: Icon(_api.isLoggedIn ? Icons.person : Icons.login),
            onPressed: () async {
              await Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
              setState(() {});
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(12),
              child: TextField(
                decoration: InputDecoration(
                  hintText: 'Search anything to rent…',
                  prefixIcon: const Icon(Icons.search),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(30)),
                  contentPadding: const EdgeInsets.symmetric(vertical: 0),
                ),
                onSubmitted: (v) {
                  _query = v;
                  _load();
                },
              ),
            ),
            SizedBox(
              height: 44,
              child: ListView(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 8),
                children: [
                  _chip('All', null),
                  ..._categories.map((c) => _chip(c.name, c.slug)),
                ],
              ),
            ),
            Expanded(
              child: _loading
                  ? const Center(child: CircularProgressIndicator())
                  : _listings.isEmpty
                      ? const Center(child: Text('No listings found.'))
                      : GridView.builder(
                          padding: const EdgeInsets.all(12),
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            childAspectRatio: 0.72,
                            crossAxisSpacing: 12,
                            mainAxisSpacing: 12,
                          ),
                          itemCount: _listings.length,
                          itemBuilder: (_, i) => _card(_listings[i]),
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _chip(String label, String? slug) {
    final active = _activeCategory == slug;
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4),
      child: ChoiceChip(
        label: Text(label),
        selected: active,
        selectedColor: brand.withOpacity(0.15),
        onSelected: (_) {
          _activeCategory = slug;
          _load();
        },
      ),
    );
  }

  Widget _card(ListingCard l) {
    return InkWell(
      onTap: () => Navigator.push(context,
          MaterialPageRoute(builder: (_) => ListingScreen(slug: l.slug))),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Stack(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(14),
                  child: l.photo != null
                      ? Image.network(l.photo!, width: double.infinity, height: double.infinity, fit: BoxFit.cover)
                      : Container(color: Colors.grey.shade200),
                ),
                if (l.promotedBadges.isNotEmpty)
                  Positioned(
                    left: 8, top: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(color: Colors.amber, borderRadius: BorderRadius.circular(20)),
                      child: Text(l.promotedBadges.first, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                    ),
                  ),
                if (l.earnedBadges.isNotEmpty)
                  Positioned(
                    right: 8, top: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: ceylon),
                      ),
                      child: Text(l.earnedBadges.first, style: const TextStyle(fontSize: 11, color: ceylon, fontWeight: FontWeight.bold)),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 6),
          Row(
            children: [
              Expanded(child: Text(l.title, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w600))),
              if (l.ratingCount > 0) ...[
                const Icon(Icons.star, size: 14),
                Text(' ${l.rating.toStringAsFixed(1)}', style: const TextStyle(fontSize: 12)),
              ],
            ],
          ),
          Text('${l.city} · ${l.category}', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
          Text('${l.currency} ${l.dailyRate.toStringAsFixed(0)} / day', style: const TextStyle(fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }
}

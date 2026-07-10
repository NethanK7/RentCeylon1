import 'package:flutter/material.dart';
import '../api.dart';
import '../models.dart';
import '../wishlist_store.dart';
import '../widgets/listing_card.dart';

class ExploreScreen extends StatefulWidget {
  const ExploreScreen({super.key});
  @override
  State<ExploreScreen> createState() => _ExploreScreenState();
}

class _ExploreScreenState extends State<ExploreScreen> {
  final _api = ApiService.instance;
  List<Category> _categories = [];
  List<ListingCard> _listings = [];
  String? _activeCategory;
  String _query = '';
  bool _loading = true;
  final _searchController = TextEditingController();

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
      if (!mounted) return;
      setState(() {
        _categories = cats;
        _listings = listings;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _loading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Could not reach RentCeylon. Is the API running? ($e)')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text.rich(TextSpan(children: [
          TextSpan(text: 'Rent', style: TextStyle(color: Color(0xFF123063), fontWeight: FontWeight.w800, fontSize: 20)),
          TextSpan(text: 'Ceylon', style: TextStyle(color: Color(0xFFC6900F), fontWeight: FontWeight.w800, fontSize: 20)),
        ])),
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await _load();
          await WishlistStore.instance.refresh();
        },
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
              child: TextField(
                controller: _searchController,
                decoration: InputDecoration(
                  hintText: 'Search anything to rent…',
                  prefixIcon: const Icon(Icons.search, size: 20),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(30), borderSide: BorderSide.none),
                  filled: true,
                  fillColor: const Color(0xFFF3F4F6),
                ),
                onSubmitted: (v) {
                  _query = v;
                  _load();
                },
              ),
            ),
            SizedBox(
              height: 40,
              child: ListView(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 12),
                children: [
                  _chip('All', null),
                  ..._categories.map((c) => _chip(c.name, c.slug)),
                ],
              ),
            ),
            const SizedBox(height: 4),
            Expanded(
              child: _loading
                  ? const Center(child: CircularProgressIndicator())
                  : _listings.isEmpty
                      ? const Center(child: Text('No listings found.'))
                      : GridView.builder(
                          padding: const EdgeInsets.fromLTRB(14, 6, 14, 20),
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            childAspectRatio: 0.68,
                            crossAxisSpacing: 14,
                            mainAxisSpacing: 18,
                          ),
                          itemCount: _listings.length,
                          itemBuilder: (_, i) => ListingCardWidget(listing: _listings[i]),
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
        showCheckmark: false,
        selectedColor: const Color(0xFF123063),
        labelStyle: TextStyle(color: active ? Colors.white : Colors.black87, fontSize: 12.5, fontWeight: FontWeight.w600),
        backgroundColor: const Color(0xFFF3F4F6),
        side: BorderSide.none,
        onSelected: (_) {
          setState(() => _activeCategory = slug);
          _load();
        },
      ),
    );
  }
}

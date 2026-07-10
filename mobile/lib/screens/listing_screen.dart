import 'package:flutter/material.dart';
import '../api.dart';
import '../models.dart';
import '../theme.dart';
import '../wishlist_store.dart';

class ListingScreen extends StatefulWidget {
  final String slug;
  const ListingScreen({super.key, required this.slug});
  @override
  State<ListingScreen> createState() => _ListingScreenState();
}

class _ListingScreenState extends State<ListingScreen> {
  ListingDetail? _listing;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final l = await ApiService.instance.listing(widget.slug);
      WishlistStore.instance.seedIfUnloaded(l.id, l.isWishlisted);
      if (!mounted) return;
      setState(() {
        _listing = l;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loading = false);
    }
  }

  double _feeRate(double subtotal) =>
      subtotal <= 10000 ? 0.10 : (subtotal <= 50000 ? 0.07 : 0.05);

  @override
  Widget build(BuildContext context) {
    final l = _listing;
    return Scaffold(
      extendBodyBehindAppBar: true,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        foregroundColor: Colors.white,
        title: const SizedBox.shrink(),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [Colors.black45, Colors.transparent]),
          ),
        ),
        actions: l == null ? null : [
          Padding(
            padding: const EdgeInsets.only(right: 8),
            child: ListenableBuilder(
              listenable: WishlistStore.instance,
              builder: (context, _) => IconButton(
                icon: Icon(
                  WishlistStore.instance.isSaved(l.id) ? Icons.favorite : Icons.favorite_border,
                  color: WishlistStore.instance.isSaved(l.id) ? AppColors.rose600 : Colors.white,
                  shadows: const [Shadow(color: Colors.black45, blurRadius: 4)],
                ),
                onPressed: () => WishlistStore.instance.toggle(l.id),
              ),
            ),
          ),
        ],
      ),
      bottomNavigationBar: l == null ? null : _bookingBar(l),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : l == null
              ? const Center(child: Text('Could not load listing.'))
              : ListView(
                  padding: EdgeInsets.zero,
                  children: [
                    if (l.photos.isNotEmpty)
                      SizedBox(
                        height: 300,
                        child: PageView(
                          children: l.photos
                              .map((p) => Image.network(p, fit: BoxFit.cover))
                              .toList(),
                        ),
                      ),
                    Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(l.title, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 4),
                          Row(children: [
                            const Icon(Icons.location_on, size: 16, color: Colors.grey),
                            const SizedBox(width: 2),
                            Text('${l.city} · ${l.category}', style: TextStyle(color: Colors.grey.shade700)),
                            const Spacer(),
                            if (l.ratingCount > 0) ...[
                              const Icon(Icons.star, size: 16),
                              Text(' ${l.rating.toStringAsFixed(1)} (${l.ratingCount})'),
                            ],
                          ]),
                          const SizedBox(height: 12),

                          // Badge zones kept separate (Constraint 01)
                          if (l.earnedBadges.isNotEmpty || l.promotedBadges.isNotEmpty)
                            Wrap(spacing: 8, runSpacing: 8, children: [
                              ...l.earnedBadges.map((b) => _badge(b, earned: true)),
                              ...l.promotedBadges.map((b) => _badge(b, earned: false)),
                            ]),
                          if (l.earnedBadges.isNotEmpty || l.promotedBadges.isNotEmpty) const SizedBox(height: 16),

                          if (l.attributes.isNotEmpty) ...[
                            const Text('Details', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                            const SizedBox(height: 8),
                            Wrap(
                              spacing: 8, runSpacing: 8,
                              children: l.attributes.map((a) {
                                final unit = a['unit'] != null ? ' ${a['unit']}' : '';
                                return Chip(
                                  label: Text('${a['label']}: ${a['value']}$unit'),
                                  backgroundColor: const Color(0xFFF3F4F6),
                                  side: BorderSide.none,
                                );
                              }).toList(),
                            ),
                            const SizedBox(height: 16),
                          ],

                          const Text('About this item', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                          const SizedBox(height: 4),
                          Text(l.description, style: const TextStyle(height: 1.4)),
                          const SizedBox(height: 16),
                          Row(
                            children: [
                              CircleAvatar(radius: 18, backgroundColor: AppColors.navy800, child: Text(l.listerName.isNotEmpty ? l.listerName[0] : '?', style: const TextStyle(color: Colors.white))),
                              const SizedBox(width: 10),
                              Text('Listed by ${l.listerName}', style: TextStyle(color: Colors.grey.shade700, fontWeight: FontWeight.w500)),
                            ],
                          ),
                          const SizedBox(height: 90),
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }

  Widget _badge(String name, {required bool earned}) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: earned ? Colors.white : AppColors.gold300,
        borderRadius: BorderRadius.circular(20),
        border: earned ? Border.all(color: AppColors.ceylon600) : null,
      ),
      child: Text(name,
          style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.bold,
              color: earned ? AppColors.ceylon600 : const Color(0xFF4A370D))),
    );
  }

  Widget _bookingBar(ListingDetail l) {
    final fee = (l.dailyRate * _feeRate(l.dailyRate)).round();
    return SafeArea(
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 10)],
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('${l.currency} ${l.dailyRate.toStringAsFixed(0)} / day',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  Text('Deposit ${l.currency} ${l.deposit.toStringAsFixed(0)} · fee ~$fee/day',
                      style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                ],
              ),
            ),
            ElevatedButton(
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Checkout continues on web — deposit held in escrow.')),
                );
              },
              child: const Text('Reserve'),
            ),
          ],
        ),
      ),
    );
  }
}

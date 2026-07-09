import 'package:flutter/material.dart';
import '../api.dart';
import '../models.dart';

const _brand = Color(0xFFFF385C);
const _ceylon = Color(0xFF0D9488);

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
      setState(() {
        _listing = l;
        _loading = false;
      });
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  double _feeRate(double subtotal) =>
      subtotal <= 10000 ? 0.10 : (subtotal <= 50000 ? 0.07 : 0.05);

  @override
  Widget build(BuildContext context) {
    final l = _listing;
    return Scaffold(
      appBar: AppBar(title: Text(l?.title ?? 'Listing')),
      bottomNavigationBar: l == null
          ? null
          : _bookingBar(l),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : l == null
              ? const Center(child: Text('Could not load listing.'))
              : ListView(
                  children: [
                    if (l.photos.isNotEmpty)
                      SizedBox(
                        height: 260,
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
                            const Icon(Icons.location_on, size: 16),
                            Text('${l.city} · ${l.category}'),
                            const Spacer(),
                            if (l.ratingCount > 0) ...[
                              const Icon(Icons.star, size: 16),
                              Text(' ${l.rating.toStringAsFixed(1)} (${l.ratingCount})'),
                            ],
                          ]),
                          const SizedBox(height: 12),

                          // Badge zones kept separate (Constraint 01)
                          Wrap(spacing: 8, runSpacing: 8, children: [
                            ...l.earnedBadges.map((b) => _badge(b, earned: true)),
                            ...l.promotedBadges.map((b) => _badge(b, earned: false)),
                          ]),
                          const SizedBox(height: 16),

                          if (l.attributes.isNotEmpty) ...[
                            const Text('Details', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                            const SizedBox(height: 8),
                            Wrap(
                              spacing: 8, runSpacing: 8,
                              children: l.attributes.map((a) {
                                final unit = a['unit'] != null ? ' ${a['unit']}' : '';
                                return Chip(label: Text('${a['label']}: ${a['value']}$unit'));
                              }).toList(),
                            ),
                            const SizedBox(height: 16),
                          ],

                          const Text('About this item', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                          const SizedBox(height: 4),
                          Text(l.description),
                          const SizedBox(height: 16),
                          Text('Listed by ${l.listerName}', style: TextStyle(color: Colors.grey.shade600)),
                          const SizedBox(height: 80),
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
        color: earned ? Colors.white : Colors.amber,
        borderRadius: BorderRadius.circular(20),
        border: earned ? Border.all(color: _ceylon) : null,
      ),
      child: Text(name,
          style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.bold,
              color: earned ? _ceylon : Colors.black87)),
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
              style: ElevatedButton.styleFrom(backgroundColor: _brand, foregroundColor: Colors.white),
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

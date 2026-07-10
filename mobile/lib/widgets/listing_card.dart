import 'package:flutter/material.dart';
import '../models.dart';
import '../theme.dart';
import '../wishlist_store.dart';
import '../screens/listing_screen.dart';

/// Mirrors the web `ListingCard` component: photo, promoted badge (top-left,
/// solid amber pill), heart (top-right), earned badge (bottom-left, outlined
/// pill) — never mixed with the promoted badge (Global Constraint 01).
class ListingCardWidget extends StatelessWidget {
  final ListingCard listing;
  const ListingCardWidget({super.key, required this.listing});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(16),
      onTap: () => Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => ListingScreen(slug: listing.slug)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          AspectRatio(
            aspectRatio: 4 / 3,
            child: Stack(
              fit: StackFit.expand,
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(16),
                  child: listing.photo != null
                      ? Image.network(listing.photo!, fit: BoxFit.cover,
                          loadingBuilder: (ctx, child, progress) =>
                              progress == null ? child : Container(color: const Color(0xFFF3F4F6)),
                          errorBuilder: (ctx, err, st) => Container(color: const Color(0xFFF3F4F6)))
                      : Container(color: const Color(0xFFF3F4F6)),
                ),
                if (listing.promotedBadges.isNotEmpty)
                  Positioned(left: 8, top: 8, child: _PromotedChip(label: listing.promotedBadges.first)),
                Positioned(right: 6, top: 6, child: _HeartButton(listingId: listing.id, initiallySaved: listing.isWishlisted)),
                if (listing.earnedBadges.isNotEmpty)
                  Positioned(left: 8, bottom: 8, child: _EarnedChip(label: listing.earnedBadges.first)),
              ],
            ),
          ),
          const SizedBox(height: 6),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Text(listing.title,
                    maxLines: 1, overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
              ),
              if (listing.ratingCount > 0) ...[
                const Icon(Icons.star, size: 13),
                const SizedBox(width: 2),
                Text(listing.rating.toStringAsFixed(1), style: const TextStyle(fontSize: 12.5)),
              ],
            ],
          ),
          Text('${listing.city} · ${listing.category}',
              style: TextStyle(fontSize: 12.5, color: Colors.grey.shade600)),
          RichText(
            text: TextSpan(
              style: const TextStyle(fontSize: 13.5, color: Colors.black87),
              children: [
                TextSpan(text: '${listing.currency} ${listing.dailyRate.toStringAsFixed(0)}', style: const TextStyle(fontWeight: FontWeight.w700)),
                const TextSpan(text: ' / day', style: TextStyle(color: Colors.black54)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _PromotedChip extends StatelessWidget {
  final String label;
  const _PromotedChip({required this.label});
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(color: AppColors.gold300, borderRadius: BorderRadius.circular(20), boxShadow: const [
        BoxShadow(color: Colors.black12, blurRadius: 3, offset: Offset(0, 1)),
      ]),
      child: Text(label, style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: Color(0xFF4A370D))),
    );
  }
}

class _EarnedChip extends StatelessWidget {
  final String label;
  const _EarnedChip({required this.label});
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.ceylon600),
      ),
      child: Text(label, style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: AppColors.ceylon600)),
    );
  }
}

class _HeartButton extends StatelessWidget {
  final int listingId;
  final bool initiallySaved;
  const _HeartButton({required this.listingId, required this.initiallySaved});

  @override
  Widget build(BuildContext context) {
    WishlistStore.instance.seedIfUnloaded(listingId, initiallySaved);

    return ListenableBuilder(
      listenable: WishlistStore.instance,
      builder: (context, _) {
        final saved = WishlistStore.instance.isSaved(listingId);
        return GestureDetector(
          onTap: () => WishlistStore.instance.toggle(listingId),
          child: Padding(
            padding: const EdgeInsets.all(4),
            child: Icon(
              saved ? Icons.favorite : Icons.favorite_border,
              color: saved ? AppColors.rose600 : Colors.white,
              size: 22,
              shadows: const [Shadow(color: Colors.black38, blurRadius: 4)],
            ),
          ),
        );
      },
    );
  }
}

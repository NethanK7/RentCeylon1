import 'package:flutter/foundation.dart';
import 'api.dart';

/// App-wide saved-listing ids, so the heart on a grid card, the detail
/// screen, and the Wishlists tab all stay in sync after a single tap.
class WishlistStore extends ChangeNotifier {
  WishlistStore._();
  static final WishlistStore instance = WishlistStore._();

  final Set<int> _ids = {};
  bool _loaded = false;

  /// True once we've fetched the server's saved-ids at least once this
  /// session. Until then, callers should fall back to whatever `is_wishlisted`
  /// the listing/card API response itself carried.
  bool get hasLoadedOnce => _loaded;

  bool isSaved(int listingId) => _ids.contains(listingId);

  /// Cards/detail screens call this once per listing before we've fetched
  /// the server's full saved-id list, so a later `toggle()` starts from the
  /// correct baseline instead of assuming "not saved". Idempotent, no notify.
  void seedIfUnloaded(int listingId, bool saved) {
    if (_loaded) return;
    if (saved) {
      _ids.add(listingId);
    }
  }

  Future<void> loadIfNeeded() async {
    if (_loaded || !ApiService.instance.isLoggedIn) return;
    await refresh();
  }

  Future<void> refresh() async {
    try {
      final listings = await ApiService.instance.wishlist();
      _ids
        ..clear()
        ..addAll(listings.map((l) => l.id));
      _loaded = true;
      notifyListeners();
    } catch (_) {
      // Silently ignore — heart state just won't be pre-filled this session.
    }
  }

  Future<void> toggle(int listingId) async {
    final wasSaved = _ids.contains(listingId);
    // Optimistic flip.
    wasSaved ? _ids.remove(listingId) : _ids.add(listingId);
    notifyListeners();

    try {
      final saved = await ApiService.instance.toggleWishlist(listingId);
      if (saved != !wasSaved) {
        // Server disagreed with our optimism — reconcile.
        saved ? _ids.add(listingId) : _ids.remove(listingId);
        notifyListeners();
      }
    } catch (_) {
      // Revert on failure.
      wasSaved ? _ids.add(listingId) : _ids.remove(listingId);
      notifyListeners();
    }
  }

  void clear() {
    _ids.clear();
    _loaded = false;
    notifyListeners();
  }
}

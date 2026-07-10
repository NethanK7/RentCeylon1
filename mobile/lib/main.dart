import 'package:flutter/material.dart';
import 'api.dart';
import 'config.dart';
import 'theme.dart';
import 'wishlist_store.dart';
import 'screens/explore_screen.dart';
import 'screens/wishlist_screen.dart';
import 'screens/trips_screen.dart';
import 'screens/account_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await ApiService.instance.loadToken();
  await WishlistStore.instance.loadIfNeeded();
  runApp(const RentCeylonApp());
}

class RentCeylonApp extends StatelessWidget {
  const RentCeylonApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: Config.brandName,
      debugShowCheckedModeBanner: false,
      theme: buildAppTheme(),
      home: const RootShell(),
    );
  }
}

/// The four-tab bottom navigation shell — Airbnb's signature mobile pattern:
/// Explore / Wishlists / Trips / Account, mirroring the website's own
/// mobile bottom tab bar (Components/site/BottomNav.tsx).
class RootShell extends StatefulWidget {
  const RootShell({super.key});
  @override
  State<RootShell> createState() => _RootShellState();
}

class _RootShellState extends State<RootShell> {
  int _index = 0;

  static const _screens = [
    ExploreScreen(),
    WishlistScreen(),
    TripsScreen(),
    AccountScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(index: _index, children: _screens),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _index,
        onTap: (i) => setState(() => _index = i),
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.explore_outlined), activeIcon: Icon(Icons.explore), label: 'Explore'),
          BottomNavigationBarItem(icon: Icon(Icons.favorite_border), activeIcon: Icon(Icons.favorite), label: 'Wishlists'),
          BottomNavigationBarItem(icon: Icon(Icons.card_travel_outlined), activeIcon: Icon(Icons.card_travel), label: 'Trips'),
          BottomNavigationBarItem(icon: Icon(Icons.person_outline), activeIcon: Icon(Icons.person), label: 'Account'),
        ],
      ),
    );
  }
}

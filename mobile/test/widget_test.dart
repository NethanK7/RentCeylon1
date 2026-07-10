import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:rentceylon_mobile/main.dart';

void main() {
  testWidgets('App boots to the four-tab shell', (WidgetTester tester) async {
    await tester.pumpWidget(const RentCeylonApp());

    // The bottom tab bar is the app's defining structural element — it
    // should always be present regardless of network/API availability.
    // (IndexedStack keeps every tab mounted, so text lookups must be scoped
    // to the bottom bar itself — several tabs' own AppBars repeat the label.)
    final bottomNav = find.byType(BottomNavigationBar);
    expect(bottomNav, findsOneWidget);

    for (final label in ['Explore', 'Wishlists', 'Trips', 'Account']) {
      expect(find.descendant(of: bottomNav, matching: find.text(label)), findsOneWidget);
    }

    // Explore (the initial tab) should be showing its search bar.
    expect(find.text('Search anything to rent…'), findsOneWidget);
  });
}

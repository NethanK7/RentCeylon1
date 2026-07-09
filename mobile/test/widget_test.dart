import 'package:flutter_test/flutter_test.dart';
import 'package:rentceylon_mobile/main.dart';

void main() {
  testWidgets('App boots to browse screen', (WidgetTester tester) async {
    await tester.pumpWidget(const RentCeylonApp());
    expect(find.text('Rent'), findsWidgets);
  });
}

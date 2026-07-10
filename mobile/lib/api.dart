import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'config.dart';
import 'models.dart';

class ApiService {
  static final ApiService instance = ApiService._();
  ApiService._();

  String? _token;

  Future<void> loadToken() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('token');
  }

  bool get isLoggedIn => _token != null;

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };

  Uri _uri(String path, [Map<String, dynamic>? query]) =>
      Uri.parse('${Config.apiBase}$path').replace(
        queryParameters: query?.map((k, v) => MapEntry(k, '$v')),
      );

  Future<Map<String, dynamic>> login(String email, String password) async {
    final res = await http.post(_uri('/login'),
        headers: _headers,
        body: jsonEncode({
          'email': email,
          'password': password,
          'device_name': 'flutter-app',
        }));
    final body = jsonDecode(res.body) as Map<String, dynamic>;
    if (res.statusCode == 200) {
      _token = body['token'];
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', _token!);
    }
    return body;
  }

  Future<void> logout() async {
    try {
      await http.post(_uri('/logout'), headers: _headers);
    } catch (_) {}
    _token = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
  }

  Future<Map<String, dynamic>?> me() async {
    if (!isLoggedIn) return null;
    final res = await http.get(_uri('/me'), headers: _headers);
    if (res.statusCode != 200) return null;
    final body = jsonDecode(res.body) as Map<String, dynamic>;
    return body['user'] as Map<String, dynamic>?;
  }

  Future<List<Category>> categories() async {
    final res = await http.get(_uri('/categories'), headers: _headers);
    final list = jsonDecode(res.body) as List;
    return list.map((e) => Category.fromJson(e)).toList();
  }

  Future<List<ListingCard>> listings({String? category, String? q, Map<String, String>? attrs}) async {
    final query = <String, dynamic>{};
    if (category != null) query['category'] = category;
    if (q != null && q.isNotEmpty) query['q'] = q;
    attrs?.forEach((k, v) => query['attrs[$k]'] = v);
    final res = await http.get(_uri('/listings', query), headers: _headers);
    final body = jsonDecode(res.body) as Map<String, dynamic>;
    final data = body['data'] as List;
    return data.map((e) => ListingCard.fromJson(e)).toList();
  }

  Future<ListingDetail> listing(String slug) async {
    final res = await http.get(_uri('/listings/$slug'), headers: _headers);
    return ListingDetail.fromJson(jsonDecode(res.body));
  }

  Future<List<ListingCard>> wishlist() async {
    final res = await http.get(_uri('/wishlist'), headers: _headers);
    final list = jsonDecode(res.body) as List;
    return list.map((e) => ListingCard.fromJson(e)).toList();
  }

  /// Returns the new saved state (true = now saved).
  Future<bool> toggleWishlist(int listingId) async {
    final res = await http.post(_uri('/listings/$listingId/wishlist'), headers: _headers);
    final body = jsonDecode(res.body) as Map<String, dynamic>;
    return body['saved'] == true;
  }

  Future<List<Trip>> myTrips() async {
    final res = await http.get(_uri('/bookings/mine'), headers: _headers);
    final list = jsonDecode(res.body) as List;
    return list.map((e) => Trip.fromJson(e)).toList();
  }
}

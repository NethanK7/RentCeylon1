class ListingCard {
  final int id;
  final String title;
  final String slug;
  final double dailyRate;
  final String currency;
  final String city;
  final double rating;
  final int ratingCount;
  final String category;
  final String? photo;
  final List<String> earnedBadges;
  final List<String> promotedBadges;

  ListingCard.fromJson(Map<String, dynamic> j)
      : id = j['id'],
        title = j['title'],
        slug = j['slug'],
        dailyRate = (j['daily_rate'] as num).toDouble(),
        currency = j['currency'] ?? 'LKR',
        city = j['city'] ?? '',
        rating = (j['rating_avg'] as num?)?.toDouble() ?? 0,
        ratingCount = j['rating_count'] ?? 0,
        category = j['category'] ?? '',
        photo = j['photo'],
        earnedBadges = List<String>.from(j['earned_badges'] ?? []),
        promotedBadges = List<String>.from(j['promoted_badges'] ?? []);
}

class ListingDetail {
  final int id;
  final String title;
  final String description;
  final double dailyRate;
  final double deposit;
  final String currency;
  final String city;
  final double rating;
  final int ratingCount;
  final List<String> photos;
  final String category;
  final List<Map<String, dynamic>> attributes;
  final String listerName;
  final List<String> earnedBadges;
  final List<String> promotedBadges;

  ListingDetail.fromJson(Map<String, dynamic> j)
      : id = j['id'],
        title = j['title'],
        description = j['description'] ?? '',
        dailyRate = (j['daily_rate'] as num).toDouble(),
        deposit = (j['security_deposit'] as num?)?.toDouble() ?? 0,
        currency = j['currency'] ?? 'LKR',
        city = j['city'] ?? '',
        rating = (j['rating_avg'] as num?)?.toDouble() ?? 0,
        ratingCount = j['rating_count'] ?? 0,
        photos = List<String>.from(j['photos'] ?? []),
        category = j['category'] ?? '',
        attributes = List<Map<String, dynamic>>.from(j['attributes'] ?? []),
        listerName = (j['lister']?['name']) ?? '',
        earnedBadges = List<String>.from(j['earned_badges'] ?? []),
        promotedBadges = List<String>.from(j['promoted_badges'] ?? []);
}

class Category {
  final String name;
  final String slug;
  Category.fromJson(Map<String, dynamic> j)
      : name = j['name'],
        slug = j['slug'];
}

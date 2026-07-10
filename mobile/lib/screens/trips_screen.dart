import 'package:flutter/material.dart';
import '../api.dart';
import '../models.dart';
import '../theme.dart';
import 'login_screen.dart';

class TripsScreen extends StatefulWidget {
  const TripsScreen({super.key});
  @override
  State<TripsScreen> createState() => _TripsScreenState();
}

const _statusColors = <String, Color>{
  'pending_confirmation': Color(0xFFFEF3C7),
  'confirmed': Color(0xFFDCEAFB),
  'active': Color(0xFFD1FAE5),
  'awaiting_return': Color(0xFFFEF3C7),
  'returned': Color(0xFFFAEDC9),
  'closed': Color(0xFFE5E7EB),
  'cancelled': Color(0xFFFFE4E6),
  'no_show': Color(0xFFFFE4E6),
  'disputed': Color(0xFFFFE4E6),
};

class _TripsScreenState extends State<TripsScreen> {
  List<Trip>? _trips;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    if (!ApiService.instance.isLoggedIn) {
      setState(() => _loading = false);
      return;
    }
    setState(() => _loading = true);
    try {
      final trips = await ApiService.instance.myTrips();
      if (!mounted) return;
      setState(() {
        _trips = trips;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Trips', style: TextStyle(fontWeight: FontWeight.w700))),
      body: !ApiService.instance.isLoggedIn
          ? Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.card_travel, size: 56, color: Colors.grey),
                    const SizedBox(height: 12),
                    const Text('Log in to see your bookings', textAlign: TextAlign.center),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: () async {
                        await Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
                        _load();
                      },
                      child: const Text('Log in'),
                    ),
                  ],
                ),
              ),
            )
          : RefreshIndicator(
              onRefresh: _load,
              child: _loading
                  ? const Center(child: CircularProgressIndicator())
                  : (_trips == null || _trips!.isEmpty)
                      ? ListView(children: const [
                          SizedBox(height: 100),
                          Icon(Icons.card_travel, size: 56, color: Colors.grey),
                          SizedBox(height: 12),
                          Center(child: Text('No trips yet.\nBook something from Explore.', textAlign: TextAlign.center, style: TextStyle(color: Colors.grey))),
                        ])
                      : ListView.separated(
                          padding: const EdgeInsets.all(14),
                          itemCount: _trips!.length,
                          separatorBuilder: (_, _) => const SizedBox(height: 10),
                          itemBuilder: (_, i) => _TripCard(trip: _trips![i]),
                        ),
            ),
    );
  }
}

class _TripCard extends StatelessWidget {
  final Trip trip;
  const _TripCard({required this.trip});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        border: Border.all(color: const Color(0xFFE5E7EB)),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: trip.listingPhoto != null
                ? Image.network(trip.listingPhoto!, width: 64, height: 64, fit: BoxFit.cover)
                : Container(width: 64, height: 64, color: const Color(0xFFF3F4F6)),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(child: Text(trip.listingTitle, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w700))),
                  ],
                ),
                const SizedBox(height: 3),
                Text('${trip.startDate} → ${trip.endDate}', style: TextStyle(fontSize: 12.5, color: Colors.grey.shade600)),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(color: _statusColors[trip.status] ?? const Color(0xFFE5E7EB), borderRadius: BorderRadius.circular(20)),
                  child: Text(trip.statusLabel, style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700)),
                ),
              ],
            ),
          ),
          Text('${trip.currency} ${trip.total.toStringAsFixed(0)}', style: const TextStyle(fontWeight: FontWeight.w700, color: AppColors.navy800)),
        ],
      ),
    );
  }
}

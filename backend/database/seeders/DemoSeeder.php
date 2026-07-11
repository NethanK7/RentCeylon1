<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingAttributeValue;
use App\Models\ListingBadge;
use App\Models\ListingPhoto;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    private array $cities = [
        ['Colombo', 'Western', 6.9271, 79.8612],
        ['Kandy', 'Central', 7.2906, 80.6337],
        ['Galle', 'Southern', 6.0535, 80.2210],
        ['Negombo', 'Western', 7.2083, 79.8358],
        ['Jaffna', 'Northern', 9.6615, 80.0255],
        ['Nuwara Eliya', 'Central', 6.9497, 80.7891],
    ];

    public function run(): void
    {
        // ── Admin ──
        User::updateOrCreate(['email' => 'admin@rentceylon.lk'], [
            'name' => 'RentCeylon Admin', 'role' => 'admin',
            'password' => Hash::make('password'), 'email_verified_at' => now(),
            'tos_accepted_at' => now(), 'id_verification_status' => 'approved',
            'city' => 'Colombo', 'district' => 'Western',
            'referral_code' => strtoupper(Str::random(8)),
        ]);

        // ── Property Manager ──
        User::updateOrCreate(['email' => 'manager@rentceylon.lk'], [
            'name' => 'Nimal Perera', 'role' => 'manager',
            'password' => Hash::make('password'), 'email_verified_at' => now(),
            'tos_accepted_at' => now(), 'id_verification_status' => 'approved',
            'city' => 'Colombo', 'district' => 'Western',
            'referral_code' => strtoupper(Str::random(8)),
        ]);

        // ── Renters ──
        $renters = [];
        foreach ([['Amaya Silva', 'amaya@example.com'], ['Kasun Fernando', 'kasun@example.com'], ['Dilani Jay', 'dilani@example.com']] as [$name, $email]) {
            $renters[] = User::updateOrCreate(['email' => $email], [
                'name' => $name, 'role' => 'renter',
                'password' => Hash::make('password'), 'email_verified_at' => now(),
                'tos_accepted_at' => now(), 'id_verification_status' => 'approved',
                'city' => 'Colombo', 'district' => 'Western',
                'rating_avg' => 4.7, 'rating_count' => 8,
                'referral_code' => strtoupper(Str::random(8)),
            ]);
        }

        // ── Listers (ID approved so listings are publicly visible) ──
        $listers = [];
        foreach ([
            ['Ravi Gear Rentals', 'ravi@example.com', 4.8, 24],
            ['Colombo Auto Hire', 'auto@example.com', 4.6, 41],
            ['Lanka Event Co', 'events@example.com', 4.9, 15],
        ] as [$name, $email, $avg, $count]) {
            $lister = User::updateOrCreate(['email' => $email], [
                'name' => $name, 'role' => 'lister',
                'password' => Hash::make('password'), 'email_verified_at' => now(),
                'tos_accepted_at' => now(), 'id_verification_status' => 'approved',
                'city' => 'Colombo', 'district' => 'Western',
                'rating_avg' => $avg, 'rating_count' => $count,
                'referral_code' => strtoupper(Str::random(8)),
            ]);
            Subscription::updateOrCreate(['user_id' => $lister->id], [
                'tier' => 'premium', 'price' => 5000, 'status' => 'active',
                'listing_limit' => 100, 'photo_slots' => 20, 'badge_eligible' => true,
                'current_period_end' => now()->addMonth(),
            ]);
            $listers[] = $lister;
        }

        $this->seedListings($listers);
    }

    private function seedListings(array $listers): void
    {
        $cats = Category::pluck('id', 'slug');
        $sponsored = Badge::where('key', 'sponsored')->first();
        $topRated = Badge::where('key', 'top_rated')->first();

        $items = [
            ['cameras', 'Sony A7 IV Mirrorless Camera Kit', 6500, 80000, ['gear_type' => 'Camera', 'brand' => 'Sony'], true],
            ['cameras', 'Canon EOS R6 + 24-105mm Lens', 5500, 70000, ['gear_type' => 'Camera', 'brand' => 'Canon'], false],
            ['drones', 'DJI Mavic 3 Pro Drone', 8000, 120000, ['gear_type' => 'Drone', 'brand' => 'DJI'], true],
            ['audio-dj', 'Pioneer DDJ-1000 DJ Controller', 7000, 90000, ['gear_type' => 'Audio', 'brand' => 'Pioneer'], false],
            ['gaming', 'PS5 Console + 2 Controllers', 2500, 40000, ['gear_type' => 'Gaming', 'brand' => 'Sony'], false],
            ['computers', 'MacBook Pro 16" M3 Max', 4500, 120000, ['gear_type' => 'Computer', 'brand' => 'Apple'], false],

            ['cars', 'Toyota Aqua Hybrid (Auto)', 8500, 50000, ['vehicle_type' => 'Car', 'transmission' => 'Automatic', 'fuel_type' => 'Hybrid', 'seats' => 5, 'brand' => 'Toyota', 'year' => 2019, 'ac' => true], true],
            ['suvs', 'Mitsubishi Montero Sport 4x4', 18000, 150000, ['vehicle_type' => 'SUV', 'transmission' => 'Automatic', 'fuel_type' => 'Diesel', 'seats' => 7, 'brand' => 'Mitsubishi', 'year' => 2021, 'with_driver' => true, 'ac' => true], false],
            ['vans', 'Toyota KDH Van (13 seats)', 15000, 120000, ['vehicle_type' => 'Van', 'transmission' => 'Manual', 'fuel_type' => 'Diesel', 'seats' => 13, 'brand' => 'Toyota', 'year' => 2018, 'with_driver' => true, 'ac' => true], false],
            ['motorbikes', 'Honda CB 300R Motorbike', 3500, 40000, ['vehicle_type' => 'Motorbike', 'transmission' => 'Manual', 'fuel_type' => 'Petrol', 'seats' => 2, 'brand' => 'Honda', 'year' => 2022], false],
            ['tuktuks', 'Bajaj Three-Wheeler (Tuk-tuk)', 2000, 25000, ['vehicle_type' => 'Tuk-tuk', 'transmission' => 'Manual', 'fuel_type' => 'Petrol', 'seats' => 3, 'brand' => 'Bajaj', 'year' => 2020], false],
            ['bicycles', 'Trek Mountain Bike', 1200, 15000, ['vehicle_type' => 'Bicycle', 'brand' => 'Trek'], false],

            ['tools', 'Honda 3.5kVA Petrol Generator', 3000, 30000, ['tool_type' => 'Generator', 'power_source' => 'Petrol'], false],
            ['tools', 'Bosch Rotary Hammer Drill', 1500, 15000, ['tool_type' => 'Power tool', 'power_source' => 'Electric'], false],
            ['events', 'Full PA Sound System (2000W)', 12000, 80000, ['event_type' => 'Sound system', 'capacity' => 300], true],
            ['events', 'Wedding Marquee Tent 10x20m', 25000, 100000, ['event_type' => 'Tent / Marquee', 'capacity' => 200], false],
            ['sports', '4-Person Camping Tent + Gear', 2000, 15000, ['activity' => 'Camping'], false],
            ['home', 'Portable Air Conditioner 12000 BTU', 2500, 30000, ['appliance_type' => 'Cooling'], false],
            ['fashion', 'Designer Bridal Saree (Kandyan)', 8000, 60000, ['fashion_type' => 'Wedding / Bridal'], false],
            ['music', 'Yamaha Stage Piano P-125', 3000, 35000, ['instrument' => 'Keyboard / Piano', 'brand' => 'Yamaha'], false],
            ['spaces', 'Rooftop Event Hall — Colombo 03', 45000, 200000, ['space_type' => 'Event hall', 'area_sqft' => 2500], true],
        ];

        foreach ($items as $i => [$slug, $title, $rate, $deposit, $attrs, $isSponsored]) {
            $catId = $cats[$slug] ?? null;
            if (! $catId) continue;
            $lister = $listers[$i % count($listers)];
            [$city, $district, $lat, $lng] = $this->cities[$i % count($this->cities)];

            $listing = Listing::updateOrCreate(
                ['slug' => Str::slug($title) . '-' . ($i + 1)],
                [
                    'user_id' => $lister->id,
                    'category_id' => $catId,
                    'title' => $title,
                    'description' => "High-quality $title available for rent in $city. Well maintained, ready to use, and fully insured through RentCeylon deposit protection. Message the lister to check availability.",
                    'condition' => ['new', 'like_new', 'good'][$i % 3],
                    'daily_rate' => $rate,
                    'security_deposit' => $deposit,
                    'currency' => 'LKR',
                    'city' => $city, 'district' => $district, 'lat' => $lat, 'lng' => $lng,
                    'status' => 'active',
                    'rating_avg' => 4.3 + ($i % 6) / 10,
                    'rating_count' => 3 + ($i * 2) % 20,
                    'views' => 40 + $i * 13,
                    'bookings_count' => $i % 9,
                    'published_at' => now()->subDays($i),
                ]
            );

            // Photos — deterministic placeholder images.
            $listing->photos()->delete();
            for ($p = 0; $p < 4; $p++) {
                $seed = urlencode(Str::slug($title)) . "-$p";
                ListingPhoto::create([
                    'listing_id' => $listing->id,
                    'path' => "https://picsum.photos/seed/$seed/800/600",
                    'sort_order' => $p,
                ]);
            }

            // Typed attribute values (drive filters). Resolve attributes from
            // the category AND its ancestors so child categories inherit filters.
            $listing->attributeValues()->delete();
            foreach ($listing->category->resolvedAttributes() as $attrDef) {
                if (! array_key_exists($attrDef->key, $attrs)) continue;
                $val = $attrs[$attrDef->key];
                ListingAttributeValue::create([
                    'listing_id' => $listing->id,
                    'category_attribute_id' => $attrDef->id,
                    'value' => is_bool($val) ? ($val ? '1' : '0') : (string) $val,
                    'value_number' => is_numeric($val) ? (float) $val : null,
                ]);
            }

            // Badges — earned (top rated) if qualifies, paid (sponsored) as flagged.
            $listing->badges()->delete();
            if ($listing->rating_avg >= 4.5 && $listing->rating_count >= 10 && $topRated) {
                ListingBadge::create(['listing_id' => $listing->id, 'badge_id' => $topRated->id, 'class' => 'earned']);
            }
            if ($isSponsored && $sponsored) {
                ListingBadge::create(['listing_id' => $listing->id, 'badge_id' => $sponsored->id, 'class' => 'paid', 'expires_at' => now()->addDays(30)]);
            }
        }
    }
}

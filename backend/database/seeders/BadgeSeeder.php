<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

/**
 * Badge System Reference (spec). Earned vs Paid are kept strictly separate
 * (Global Constraint 01) — the `class` column drives distinct UI zones.
 */
class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['key' => 'top_rated', 'name' => 'Top Rated', 'class' => 'earned',
                'icon' => 'Star', 'color' => 'emerald', 'label' => 'Earned',
                'criteria' => 'Avg ≥ 4.5 across ≥ 10 reviews'],
            ['key' => 'verified_item', 'name' => 'Verified Item', 'class' => 'earned',
                'icon' => 'ShieldCheck', 'color' => 'blue', 'label' => 'Verified',
                'criteria' => 'Admin-verified photos & specs'],
            ['key' => 'fast_responder', 'name' => 'Fast Responder', 'class' => 'earned',
                'icon' => 'Zap', 'color' => 'teal', 'label' => 'Earned',
                'criteria' => '<1hr response on 80% of messages'],
            ['key' => 'sponsored', 'name' => 'Sponsored', 'class' => 'paid',
                'icon' => 'BadgeDollarSign', 'color' => 'amber', 'label' => 'Sponsored',
                'criteria' => 'Purchased add-on by lister'],
            ['key' => 'featured', 'name' => 'Featured', 'class' => 'paid',
                'icon' => 'Sparkles', 'color' => 'amber', 'label' => 'Featured',
                'criteria' => 'Premium boost purchased'],
        ];

        foreach ($badges as $b) {
            Badge::updateOrCreate(['key' => $b['key']], $b);
        }
    }
}

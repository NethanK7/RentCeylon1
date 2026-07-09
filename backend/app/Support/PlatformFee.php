<?php

namespace App\Support;

/**
 * Tiered platform fee (Amendment 08 / Reference table):
 *   ≤ LKR 10,000        → 10%
 *   LKR 10,001–50,000   → 7%
 *   LKR 50,001+         → 5%
 *
 * The fee is computed on the rental subtotal (daily_rate * days), NOT the deposit.
 * Shown transparently at checkout before payment (Constraint / Page 09).
 */
class PlatformFee
{
    /** Return the fee rate (as a fraction) for a given rental subtotal. */
    public static function rateFor(float $subtotal): float
    {
        return match (true) {
            $subtotal <= 10_000 => 0.10,
            $subtotal <= 50_000 => 0.07,
            default => 0.05,
        };
    }

    /** Fee amount, rounded to 2dp. */
    public static function amountFor(float $subtotal): float
    {
        return round($subtotal * self::rateFor($subtotal), 2);
    }

    /** Full transparent breakdown for the checkout UI. */
    public static function breakdown(float $dailyRate, int $days, float $deposit): array
    {
        $subtotal = round($dailyRate * $days, 2);
        $rate = self::rateFor($subtotal);
        $fee = round($subtotal * $rate, 2);

        return [
            'daily_rate' => $dailyRate,
            'days' => $days,
            'subtotal' => $subtotal,
            'fee_rate' => $rate,
            'fee_rate_label' => (int) round($rate * 100) . '%',
            'platform_fee' => $fee,
            'deposit' => round($deposit, 2),
            'total' => round($subtotal + $fee + $deposit, 2),
        ];
    }
}

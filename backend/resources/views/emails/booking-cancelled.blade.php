<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f9fafb;margin:0;padding:24px}
.card{background:#fff;border-radius:16px;max-width:520px;margin:0 auto;padding:32px;border:1px solid #e5e7eb}
.logo{font-size:22px;font-weight:800;color:#111;margin-bottom:24px}
.logo span{color:#ff385c}
h2{font-size:18px;font-weight:700;color:#111;margin:0 0 8px}
p{color:#6b7280;font-size:15px;line-height:1.6;margin:0 0 16px}
.row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f3f4f6;font-size:14px}
.row .label{color:#6b7280}.row .value{font-weight:600;color:#111}
.footer{text-align:center;color:#9ca3af;font-size:12px;margin-top:24px}
</style></head>
<body>
<div class="card">
    <div class="logo"><span>Rent</span>Ceylon</div>
    <h2>Booking cancelled</h2>
    <p>
        @if($recipientRole === 'renter')
            Hi {{ $booking->renter->name }}, your booking has been cancelled.
        @else
            Hi {{ $booking->lister->name }}, the booking for <strong>{{ $booking->listing->title }}</strong> has been cancelled.
        @endif
    </p>

    <div class="row"><span class="label">Reference</span><span class="value">{{ $booking->reference }}</span></div>
    <div class="row"><span class="label">Item</span><span class="value">{{ $booking->listing->title }}</span></div>
    <div class="row"><span class="label">Dates</span><span class="value">{{ $booking->start_date->format('d M Y') }} → {{ $booking->end_date->format('d M Y') }}</span></div>
    @if($booking->cancellation && $recipientRole === 'renter')
    <div class="row"><span class="label">Refund amount</span><span class="value">{{ $booking->currency }} {{ number_format($booking->cancellation->rental_refund + $booking->cancellation->deposit_refund) }}</span></div>
    @endif
</div>
<div class="footer">RentCeylon · rentceylon.com</div>
</body>
</html>

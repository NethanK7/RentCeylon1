<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Booking</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { background: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; color: #111827; }
  .wrapper { max-width: 560px; margin: 32px auto; padding: 0 16px 40px; }
  .header { background: #111827; border-radius: 16px 16px 0 0; padding: 24px 32px; }
  .logo { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
  .logo-rent { color: #f87171; }
  .logo-ceylon { color: #ffffff; }
  .header-tag { margin-top: 4px; font-size: 12px; color: #9ca3af; letter-spacing: 0.5px; text-transform: uppercase; }
  .body { background: #ffffff; padding: 32px; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; }
  .status-badge { display: inline-flex; align-items: center; gap: 6px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; border-radius: 999px; padding: 6px 14px; font-size: 13px; font-weight: 600; margin-bottom: 20px; }
  h1 { font-size: 22px; font-weight: 700; color: #111827; margin-bottom: 6px; }
  .subtitle { font-size: 15px; color: #6b7280; margin-bottom: 24px; line-height: 1.5; }
  .money-block { background: #111827; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
  .money-label { font-size: 13px; color: #9ca3af; }
  .money-amount { font-size: 22px; font-weight: 800; color: #ffffff; }
  .money-sub { font-size: 12px; color: #6b7280; margin-top: 2px; }
  .detail-block { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-bottom: 20px; }
  .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; }
  .detail-row:last-child { border-bottom: none; }
  .detail-label { font-size: 13px; color: #6b7280; }
  .detail-value { font-size: 14px; font-weight: 600; color: #111827; text-align: right; }
  .info-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 14px 16px; font-size: 13px; color: #1d4ed8; line-height: 1.6; margin-bottom: 20px; }
  .btn-wrap { text-align: center; margin-bottom: 20px; }
  .btn { display: inline-block; background: #111827; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 14px; padding: 13px 28px; border-radius: 10px; }
  .footer { background: #f9fafb; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 16px 16px; padding: 20px 32px; text-align: center; }
  .footer p { font-size: 12px; color: #9ca3af; line-height: 1.6; }
  .footer a { color: #6b7280; text-decoration: none; }
</style>
</head>
<body>
<div class="wrapper">

  <div class="header">
    <div class="logo"><span class="logo-rent">Rent</span><span class="logo-ceylon">Ceylon</span></div>
    <div class="header-tag">New Booking</div>
  </div>

  <div class="body">

    <div class="status-badge">🎉 New Booking Received</div>

    <h1>Someone booked your item!</h1>
    <p class="subtitle">Hi {{ $booking->lister->name }}, <strong>{{ $booking->renter->name }}</strong> has booked your <strong>{{ $booking->listing->title }}</strong>.</p>

    <div class="money-block">
      <div>
        <div class="money-label">You earn</div>
        <div class="money-amount">{{ $booking->currency }} {{ number_format($booking->subtotal - $booking->platform_fee) }}</div>
        <div class="money-sub">After {{ number_format($booking->fee_rate * 100, 0) }}% platform fee</div>
      </div>
      <div style="text-align:right">
        <div class="money-label">{{ $booking->days }} day{{ $booking->days > 1 ? 's' : '' }}</div>
        <div style="font-size:14px;color:#e5e7eb;font-weight:600;margin-top:4px">{{ $booking->currency }} {{ number_format($booking->listing->daily_rate) }}/day</div>
      </div>
    </div>

    <div class="detail-block">
      <div class="detail-row">
        <span class="detail-label">Reference</span>
        <span class="detail-value" style="font-family:'Courier New',monospace;letter-spacing:1px;">{{ $booking->reference }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Renter</span>
        <span class="detail-value">{{ $booking->renter->name }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Pickup date</span>
        <span class="detail-value">{{ $booking->start_date->format('D, d M Y') }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Return date</span>
        <span class="detail-value">{{ $booking->end_date->format('D, d M Y') }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Total received</span>
        <span class="detail-value">{{ $booking->currency }} {{ number_format($booking->total) }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Deposit held</span>
        <span class="detail-value">{{ $booking->currency }} {{ number_format($booking->deposit_amount) }} (escrow)</span>
      </div>
    </div>

    <div class="info-box">
      📷 <strong>At pickup:</strong> The renter will show you a QR code on their phone. Scan it with your camera to verify their booking details before handing over the item.
    </div>

    <div class="btn-wrap">
      <a href="{{ config('app.url') }}/lister/bookings" class="btn">View in Dashboard</a>
    </div>

  </div>

  <div class="footer">
    <p>
      <strong>RentCeylon</strong> · Colombo, Sri Lanka<br>
      <a href="{{ config('app.url') }}">rentceylon.com</a> · <a href="mailto:support@rentceylon.com">support@rentceylon.com</a>
    </p>
  </div>

</div>
</body>
</html>

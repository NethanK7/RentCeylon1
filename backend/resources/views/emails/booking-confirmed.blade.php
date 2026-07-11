<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking Confirmed</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { background: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; color: #111827; }
  .wrapper { max-width: 560px; margin: 32px auto; padding: 0 16px 40px; }

  /* Header */
  .header { background: #111827; border-radius: 16px 16px 0 0; padding: 24px 32px; }
  .logo { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
  .logo-rent { color: #f87171; }
  .logo-ceylon { color: #ffffff; }
  .header-tag { margin-top: 4px; font-size: 12px; color: #9ca3af; letter-spacing: 0.5px; text-transform: uppercase; }

  /* Body */
  .body { background: #ffffff; padding: 32px; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; }

  .status-badge { display: inline-flex; align-items: center; gap: 6px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; border-radius: 999px; padding: 6px 14px; font-size: 13px; font-weight: 600; margin-bottom: 20px; }
  .status-dot { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; display: inline-block; }

  h1 { font-size: 22px; font-weight: 700; color: #111827; margin-bottom: 6px; }
  .subtitle { font-size: 15px; color: #6b7280; margin-bottom: 24px; line-height: 1.5; }

  /* Detail rows */
  .detail-block { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-bottom: 20px; }
  .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; }
  .detail-row:last-child { border-bottom: none; }
  .detail-label { font-size: 13px; color: #6b7280; }
  .detail-value { font-size: 14px; font-weight: 600; color: #111827; text-align: right; max-width: 60%; }

  /* Money highlight */
  .money-block { background: #111827; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
  .money-label { font-size: 13px; color: #9ca3af; }
  .money-amount { font-size: 22px; font-weight: 800; color: #ffffff; }
  .money-deposit { font-size: 12px; color: #6b7280; margin-top: 2px; }

  /* QR section */
  .qr-section { border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px 20px; margin-bottom: 20px; text-align: center; }
  .qr-title { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 4px; }
  .qr-subtitle { font-size: 13px; color: #6b7280; margin-bottom: 20px; line-height: 1.5; }
  .qr-image-wrap { display: inline-block; background: #ffffff; border: 2px solid #e5e7eb; border-radius: 12px; padding: 12px; margin-bottom: 14px; }
  .qr-image { display: block; width: 180px; height: 180px; }
  .qr-ref { font-family: 'Courier New', monospace; font-size: 18px; font-weight: 700; letter-spacing: 4px; color: #111827; background: #f3f4f6; border-radius: 8px; padding: 8px 16px; display: inline-block; }
  .qr-note { font-size: 12px; color: #9ca3af; margin-top: 12px; line-height: 1.5; }

  /* CTA button */
  .btn-wrap { text-align: center; margin-bottom: 20px; }
  .btn { display: inline-block; background: #111827; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 14px; padding: 13px 28px; border-radius: 10px; }

  /* Info box */
  .info-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 14px 16px; font-size: 13px; color: #1d4ed8; line-height: 1.6; margin-bottom: 20px; }

  /* Footer */
  .footer { background: #f9fafb; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 16px 16px; padding: 20px 32px; text-align: center; }
  .footer p { font-size: 12px; color: #9ca3af; line-height: 1.6; }
  .footer a { color: #6b7280; text-decoration: none; }
</style>
</head>
<body>
<div class="wrapper">

  <!-- Header -->
  <div class="header">
    <div class="logo">
      <span class="logo-rent">Rent</span><span class="logo-ceylon">Ceylon</span>
    </div>
    <div class="header-tag">Booking Confirmation</div>
  </div>

  <!-- Body -->
  <div class="body">

    <div class="status-badge">
      <span class="status-dot"></span> Booking Confirmed
    </div>

    @if($recipientRole === 'renter')
      <h1>You're all set, {{ $booking->renter->name }}!</h1>
      <p class="subtitle">Your booking for <strong>{{ $booking->listing->title }}</strong> has been confirmed. Show the QR code below to the lister when you arrive.</p>
    @else
      <h1>New booking, {{ $booking->lister->name }}!</h1>
      <p class="subtitle"><strong>{{ $booking->renter->name }}</strong> has booked your <strong>{{ $booking->listing->title }}</strong>. They'll show you a QR code at pickup — scan it to verify.</p>
    @endif

    <!-- Money -->
    <div class="money-block">
      <div>
        <div class="money-label">Total paid</div>
        <div class="money-amount">{{ $booking->currency }} {{ number_format($booking->total) }}</div>
        <div class="money-deposit">Deposit {{ $booking->currency }} {{ number_format($booking->deposit_amount) }} held in escrow</div>
      </div>
      <div style="text-align:right">
        <div class="money-label">{{ $booking->days }} day{{ $booking->days > 1 ? 's' : '' }}</div>
        <div style="font-size:14px;color:#e5e7eb;font-weight:600;margin-top:4px">{{ $booking->currency }} {{ number_format($booking->listing->daily_rate) }}/day</div>
      </div>
    </div>

    <!-- Booking details -->
    <div class="detail-block">
      <div class="detail-row">
        <span class="detail-label">Reference</span>
        <span class="detail-value" style="font-family:'Courier New',monospace;letter-spacing:1px;">{{ $booking->reference }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Item</span>
        <span class="detail-value">{{ $booking->listing->title }}</span>
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
        <span class="detail-label">Location</span>
        <span class="detail-value">{{ $booking->listing->city }}</span>
      </div>
      @if($recipientRole === 'renter')
      <div class="detail-row">
        <span class="detail-label">Lister</span>
        <span class="detail-value">{{ $booking->lister->name }}{{ $booking->lister->phone ? ' · '.$booking->lister->phone : '' }}</span>
      </div>
      @else
      <div class="detail-row">
        <span class="detail-label">Renter</span>
        <span class="detail-value">{{ $booking->renter->name }}</span>
      </div>
      @endif
    </div>

    @if($recipientRole === 'renter' && $qrImageUrl)
    <!-- QR Code -->
    <div class="qr-section">
      <div class="qr-title">📱 Your Pickup QR Code</div>
      <div class="qr-subtitle">Show this to the lister when you arrive.<br>They'll scan it to verify your booking.</div>
      <div class="qr-image-wrap">
        <img src="{{ $qrImageUrl }}" alt="Booking QR Code" class="qr-image" width="180" height="180" />
      </div>
      <br>
      <div class="qr-ref">{{ $booking->reference }}</div>
      <div class="qr-note">QR code valid for 48 hours · Cannot be forged or reused</div>
    </div>

    <div class="info-box">
      💡 <strong>At pickup:</strong> Open this email on your phone and show the QR code to the lister. They'll scan it with their camera to confirm your identity and start the rental.
    </div>
    @endif

    @if($recipientRole === 'lister')
    <div class="info-box">
      📷 <strong>At pickup:</strong> The renter will show you a QR code from their confirmation email or booking page. Scan it with your phone camera to verify their identity and booking details before handing over the item.
    </div>
    @endif

    <!-- CTA -->
    <div class="btn-wrap">
      @if($recipientRole === 'renter')
        <a href="{{ $scanUrl }}" class="btn">View Booking Details</a>
      @else
        <a href="{{ config('app.url') }}/lister/bookings" class="btn">View in Dashboard</a>
      @endif
    </div>

  </div>

  <!-- Footer -->
  <div class="footer">
    <p>
      <strong>RentCeylon</strong> · Colombo, Sri Lanka<br>
      <a href="{{ config('app.url') }}">rentceylon.com</a> · <a href="mailto:support@rentceylon.com">support@rentceylon.com</a><br><br>
      This email was sent because a booking was confirmed on your account.
    </p>
  </div>

</div>
</body>
</html>

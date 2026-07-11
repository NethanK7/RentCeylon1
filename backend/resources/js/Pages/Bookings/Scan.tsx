import { Head } from '@inertiajs/react';
import { CheckCircle2, Calendar, MapPin, User, Phone, CreditCard, Clock } from 'lucide-react';

interface ScanBooking {
    id: number; reference: string; status: string; status_label: string;
    start_date: string; end_date: string; days: number;
    total: number; deposit_amount: number; currency: string;
    listing: { title: string; city: string; daily_rate: number };
    renter: { name: string; email: string; phone: string | null };
    deposit_status: string | null;
}

const statusColor: Record<string, string> = {
    confirmed: 'bg-green-100 text-green-700 border-green-200',
    active: 'bg-blue-100 text-blue-700 border-blue-200',
    awaiting_return: 'bg-purple-100 text-purple-700 border-purple-200',
    cancelled: 'bg-red-100 text-red-700 border-red-200',
};

export default function Scan({ booking }: { booking: ScanBooking }) {
    const color = statusColor[booking.status] ?? 'bg-gray-100 text-gray-700 border-gray-200';

    return (
        <div className="min-h-screen bg-gray-50 flex items-start justify-center py-8 px-4">
            <Head title={`Booking ${booking.reference}`} />

            <div className="w-full max-w-sm space-y-4">
                {/* Header */}
                <div className="text-center">
                    <div className="inline-flex items-center gap-2 text-2xl font-extrabold">
                        <span className="text-rose-500">Rent</span><span className="text-gray-900">Ceylon</span>
                    </div>
                    <p className="text-sm text-gray-500 mt-1">Booking verification</p>
                </div>

                {/* Status banner */}
                <div className={`flex items-center justify-center gap-2 rounded-2xl border px-4 py-3 font-semibold text-sm ${color}`}>
                    <CheckCircle2 className="h-5 w-5" />
                    {booking.status_label}
                </div>

                {/* Renter card */}
                <div className="rounded-2xl border border-gray-200 bg-white p-5">
                    <p className="text-xs font-semibold uppercase text-gray-400 mb-3">Renter</p>
                    <div className="flex items-center gap-3">
                        <div className="flex h-11 w-11 items-center justify-center rounded-full bg-gray-900 text-white font-bold text-lg flex-shrink-0">
                            {booking.renter.name.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <p className="font-bold text-gray-900 text-lg">{booking.renter.name}</p>
                            <p className="text-sm text-gray-500">{booking.renter.email}</p>
                        </div>
                    </div>
                    {booking.renter.phone && (
                        <a href={`tel:${booking.renter.phone}`} className="mt-3 flex items-center gap-2 rounded-xl bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100">
                            <Phone className="h-4 w-4 text-gray-400" />
                            {booking.renter.phone}
                        </a>
                    )}
                </div>

                {/* Booking details */}
                <div className="rounded-2xl border border-gray-200 bg-white p-5 space-y-3">
                    <p className="text-xs font-semibold uppercase text-gray-400">Booking details</p>

                    <div className="flex items-start gap-3">
                        <div className="rounded-lg bg-gray-100 p-2"><User className="h-4 w-4 text-gray-500" /></div>
                        <div>
                            <p className="text-xs text-gray-400">Item</p>
                            <p className="font-semibold text-gray-900">{booking.listing.title}</p>
                            <p className="text-sm text-gray-500 flex items-center gap-1"><MapPin className="h-3 w-3" />{booking.listing.city}</p>
                        </div>
                    </div>

                    <div className="flex items-start gap-3">
                        <div className="rounded-lg bg-gray-100 p-2"><Calendar className="h-4 w-4 text-gray-500" /></div>
                        <div>
                            <p className="text-xs text-gray-400">Rental period</p>
                            <p className="font-semibold text-gray-900">{booking.start_date} → {booking.end_date}</p>
                            <p className="text-sm text-gray-500">{booking.days} day{booking.days > 1 ? 's' : ''} · {booking.currency} {booking.listing.daily_rate.toLocaleString()}/day</p>
                        </div>
                    </div>

                    <div className="flex items-start gap-3">
                        <div className="rounded-lg bg-gray-100 p-2"><CreditCard className="h-4 w-4 text-gray-500" /></div>
                        <div>
                            <p className="text-xs text-gray-400">Payment</p>
                            <p className="font-semibold text-gray-900">{booking.currency} {booking.total.toLocaleString()} paid</p>
                            <p className="text-sm text-gray-500">Deposit: {booking.currency} {booking.deposit_amount.toLocaleString()} held in escrow</p>
                        </div>
                    </div>
                </div>

                {/* Reference */}
                <div className="rounded-2xl border border-dashed border-gray-300 bg-white px-4 py-3 text-center">
                    <p className="text-xs text-gray-400 mb-1">Reference</p>
                    <p className="font-mono text-xl font-bold tracking-widest text-gray-900">{booking.reference}</p>
                </div>

                <p className="text-center text-xs text-gray-400">This QR code expires after 24 hours. Verified by RentCeylon.</p>
            </div>
        </div>
    );
}

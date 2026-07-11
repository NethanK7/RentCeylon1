<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Listing;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewApiController extends Controller
{
    /** Reviews for a listing — public. */
    public function listing(Listing $listing)
    {
        $reviews = Review::visible()
            ->whereHas('booking', fn ($q) => $q->where('listing_id', $listing->id))
            ->where('direction', 'renter_to_lister')
            ->with('author:id,name')
            ->latest('submitted_at')
            ->paginate(15);

        return $reviews->through(fn (Review $r) => [
            'id'           => $r->id,
            'rating'       => $r->rating,
            'body'         => $r->body,
            'submitted_at' => $r->submitted_at?->toDateString(),
            'author'       => $r->author->name,
        ]);
    }

    /** Submit a review for a completed booking. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'rating'     => 'required|integer|min:1|max:5',
            'body'       => 'nullable|string|max:1000',
            'direction'  => 'required|in:renter_to_lister,lister_to_renter',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        $user = $request->user();

        // Only renter or lister of this booking can review.
        abort_unless(
            ($validated['direction'] === 'renter_to_lister' && $booking->renter_id === $user->id) ||
            ($validated['direction'] === 'lister_to_renter' && $booking->lister_id === $user->id),
            403
        );

        // Booking must be completed or closed.
        abort_unless(in_array($booking->status->value, ['completed', 'closed']), 422);

        // One review per direction per booking.
        $exists = Review::where('booking_id', $booking->id)
            ->where('author_id', $user->id)
            ->where('direction', $validated['direction'])
            ->exists();

        abort_if($exists, 422, 'You have already reviewed this booking.');

        $subjectId = $validated['direction'] === 'renter_to_lister'
            ? $booking->lister_id
            : $booking->renter_id;

        $review = Review::create([
            'booking_id'   => $booking->id,
            'author_id'    => $user->id,
            'subject_id'   => $subjectId,
            'direction'    => $validated['direction'],
            'rating'       => $validated['rating'],
            'body'         => $validated['body'] ?? null,
            'is_visible'   => true,
            'submitted_at' => now(),
        ]);

        // Recalculate rating average on subject user.
        $this->recalcRating($subjectId);

        return response()->json(['message' => 'Review submitted.', 'id' => $review->id], 201);
    }

    private function recalcRating(int $userId): void
    {
        $avg = Review::visible()->where('subject_id', $userId)->avg('rating');
        $count = Review::visible()->where('subject_id', $userId)->count();
        \App\Models\User::where('id', $userId)->update([
            'rating_avg'   => round((float) $avg, 2),
            'rating_count' => $count,
        ]);
    }
}

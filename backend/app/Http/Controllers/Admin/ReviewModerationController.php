<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewModerationController extends Controller
{
    public function index()
    {
        $flagged = Review::where('is_flagged', true)
            ->with(['author:id,name', 'subject:id,name', 'booking:id,reference', 'flags'])
            ->latest()
            ->get()
            ->map(fn (Review $r) => [
                'id'         => $r->id,
                'rating'     => $r->rating,
                'body'       => $r->body,
                'direction'  => $r->direction,
                'is_visible' => $r->is_visible,
                'author'     => $r->author->name,
                'subject'    => $r->subject->name,
                'reference'  => $r->booking->reference,
                'flag_count' => $r->flags->count(),
                'submitted'  => $r->submitted_at?->format('d M Y'),
            ]);

        return Inertia::render('Admin/Reviews/Index', ['flagged' => $flagged]);
    }

    public function hide(Review $review)
    {
        $review->update(['is_visible' => false, 'is_flagged' => false]);
        return back()->with('success', 'Review hidden from public.');
    }

    public function keep(Review $review)
    {
        $review->update(['is_flagged' => false]);
        return back()->with('success', 'Review cleared — stays visible.');
    }
}

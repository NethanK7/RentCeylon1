<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Message;
use App\Models\MessageThread;
use Illuminate\Http\Request;

class MessageApiController extends Controller
{
    /** List all threads for the authenticated user. */
    public function threads(Request $request)
    {
        $user = $request->user();

        return MessageThread::where('renter_id', $user->id)
            ->orWhere('lister_id', $user->id)
            ->with([
                'listing:id,title,slug',
                'renter:id,name',
                'lister:id,name',
                'messages' => fn ($q) => $q->latest()->limit(1),
            ])
            ->orderByDesc('last_message_at')
            ->paginate(20)
            ->through(fn (MessageThread $t) => $this->threadSummary($t, $user->id));
    }

    /** Start a new thread (renter enquiring about a listing). */
    public function startThread(Request $request)
    {
        $validated = $request->validate([
            'listing_slug' => 'required|string|exists:listings,slug',
            'message'      => 'required|string|max:2000',
        ]);

        $user = $request->user();
        $listing = Listing::where('slug', $validated['listing_slug'])->firstOrFail();

        abort_if($listing->user_id === $user->id, 422, 'You cannot message yourself.');

        // Reuse existing thread if one already exists.
        $thread = MessageThread::firstOrCreate(
            ['listing_id' => $listing->id, 'renter_id' => $user->id, 'lister_id' => $listing->user_id],
            ['request_status' => 'open', 'initiated_by' => $user->id, 'last_message_at' => now()],
        );

        $message = Message::create([
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'body'      => $validated['message'],
        ]);

        $thread->update(['last_message_at' => now()]);

        return response()->json([
            'thread_id'  => $thread->id,
            'message_id' => $message->id,
        ], 201);
    }

    /** Messages in a thread — paginated newest first. */
    public function messages(Request $request, MessageThread $thread)
    {
        $user = $request->user();
        abort_unless($thread->renter_id === $user->id || $thread->lister_id === $user->id, 403);

        // Mark all unread messages (sent by the other party) as read.
        Message::where('thread_id', $thread->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Message::where('thread_id', $thread->id)
            ->with('sender:id,name')
            ->latest()
            ->paginate(30)
            ->through(fn (Message $m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'sender'     => ['id' => $m->sender->id, 'name' => $m->sender->name],
                'is_mine'    => $m->sender_id === $user->id,
                'read_at'    => $m->read_at?->toISOString(),
                'created_at' => $m->created_at->toISOString(),
            ]);
    }

    /** Send a message in an existing thread. */
    public function send(Request $request, MessageThread $thread)
    {
        $user = $request->user();
        abort_unless($thread->renter_id === $user->id || $thread->lister_id === $user->id, 403);

        $validated = $request->validate(['body' => 'required|string|max:2000']);

        $message = Message::create([
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'body'      => $validated['body'],
        ]);

        $thread->update(['last_message_at' => now()]);

        return response()->json([
            'id'         => $message->id,
            'body'       => $message->body,
            'is_mine'    => true,
            'created_at' => $message->created_at->toISOString(),
        ], 201);
    }

    private function threadSummary(MessageThread $t, int $userId): array
    {
        $other = $t->renter_id === $userId ? $t->lister : $t->renter;
        $last = $t->messages->first();
        return [
            'id'              => $t->id,
            'listing'         => ['title' => $t->listing->title, 'slug' => $t->listing->slug],
            'other_party'     => ['id' => $other->id, 'name' => $other->name],
            'last_message'    => $last ? ['body' => $last->body, 'sent_at' => $last->created_at->toISOString()] : null,
            'last_message_at' => $t->last_message_at?->toISOString(),
        ];
    }
}

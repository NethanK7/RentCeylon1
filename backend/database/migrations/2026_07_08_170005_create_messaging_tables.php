<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Messaging (Page 22 + Constraints 04, 05):
 *  - Threads between renter ↔ lister, optionally tied to a listing/booking
 *  - Message requests accepted before full conversation
 *  - Permanent retention, NO delete (immutable dispute evidence)
 *  - Auto-flag phone numbers / payment references → admin queue
 *  - Contact details gated behind payment
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('renter_id')->constrained('users');
            $table->foreignId('lister_id')->constrained('users');
            // request state: pending | accepted (Page 22)
            $table->string('request_status')->default('pending');
            $table->foreignId('initiated_by')->constrained('users');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['renter_id', 'lister_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('message_threads')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users');
            $table->text('body');
            // Auto-flagging (Constraint 05): phone / payment reference detection.
            $table->boolean('is_flagged')->default(false);
            $table->string('flag_reason')->nullable();    // phone_number | payment_reference
            $table->boolean('held_for_review')->default(false);
            $table->timestamp('read_at')->nullable();
            // NOTE: intentionally NO soft delete — messages are permanent (Constraint 05).
            $table->timestamps();

            $table->index(['thread_id', 'created_at']);
        });

        // Flagged-message admin queue entries.
        Schema::create('message_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->string('reason');
            // status: pending | reviewed | actioned
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // Off-platform dealing reports queue (Admin Panel).
        Schema::create('offplatform_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('message_id')->nullable()->constrained()->nullOnDelete();
            $table->text('detail')->nullable();
            $table->string('status')->default('pending'); // pending | dismissed | actioned
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offplatform_reports');
        Schema::dropIfExists('message_flags');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('message_threads');
    }
};

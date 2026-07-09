<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trust & resolution layer:
 *  - ID verification with 24hr SLA (Page 08, Constraint 11/12)
 *  - Disputes with 72hr SLA + appeal (Pages 18–19)
 *  - Reviews: bilateral blind, 30-char min, no edit/delete (Page 27)
 *  - Review flags → admin moderation queue
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── ID Verification ──
        Schema::create('id_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // doc_type: nic | passport
            $table->string('doc_type');
            // Encrypted-at-rest paths, signed URLs only (Constraint 09).
            $table->string('nic_front_path')->nullable();
            $table->string('nic_back_path')->nullable();
            $table->string('passport_path')->nullable();
            $table->string('selfie_path')->nullable();
            // status: pending | approved | rejected
            $table->string('status')->default('pending');
            $table->text('reject_reason')->nullable();    // specific reason (Page 08)
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            // 24hr SLA, alert at 20hr (Constraint 12).
            $table->timestamp('sla_deadline')->nullable();
            $table->timestamp('sla_alerted_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // ── Disputes ──
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raised_by')->constrained('users');
            // type: damage | not_as_described | late_return | other
            $table->string('type');
            $table->text('description');
            // status: raised | under_review | resolved | appealed
            $table->string('status')->default('raised');
            // resolution: full_return | partial | forfeit  (deposit outcome)
            $table->string('resolution')->nullable();
            $table->decimal('resolution_to_renter', 12, 2)->nullable();
            $table->decimal('resolution_to_lister', 12, 2)->nullable();
            $table->text('resolution_note')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            // 72hr SLA; breach → escalate, deposit defaults to renter (Constraint 12).
            $table->timestamp('sla_deadline')->nullable();
            $table->timestamp('sla_alerted_at')->nullable();
            $table->boolean('sla_breached')->default(false);
            $table->unsignedTinyInteger('appeal_count')->default(0); // 1 per dispute
            $table->timestamps();

            $table->index('status');
        });

        // ── Dispute evidence ──
        Schema::create('dispute_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            // kind: photo | video | condition_photo_ref | message_thread_ref
            $table->string('kind');
            $table->string('path')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // ── Reviews (blind, bilateral) ──
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users');
            $table->foreignId('subject_id')->constrained('users');
            // direction: renter_to_lister | lister_to_renter
            $table->string('direction');
            $table->unsignedTinyInteger('rating');        // 1..5, empty blocked (Page 27)
            $table->text('body');                          // 30-char min enforced in app
            // Blind: hidden until both submit OR 7-day window expires.
            $table->boolean('is_visible')->default(false);
            $table->timestamp('submitted_at')->nullable();
            // Moderation
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();

            // one review per author per booking
            $table->unique(['booking_id', 'author_id']);
            $table->index(['subject_id', 'is_visible']);
        });

        // ── Review flags → admin moderation queue (not auto-removed) ──
        Schema::create('review_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flagged_by')->constrained('users');
            // reason: fake | abusive | irrelevant
            $table->string('reason');
            $table->text('detail')->nullable();
            // status: pending | dismissed | upheld
            $table->string('status')->default('pending');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_flags');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('dispute_evidence');
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('id_verifications');
    }
};

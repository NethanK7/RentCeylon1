<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The transaction engine. Encodes:
 *  - Booking state machine (Pages 09–11, 16)
 *  - Tiered platform fee snapshot (Amendment 08 / Reference table)
 *  - Idempotent payments (Constraint 06 — double-fire = one charge)
 *  - Deposit escrow ledger, manual release only (Constraint 03)
 *  - Condition photos as a hard gate at pickup AND return (Constraint 02)
 *  - Cancellation tiers + lister compensation (Page 23)
 *  - Server-generated rental agreement per transaction
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Bookings ──
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();        // human ref e.g. RC-8FJ3K2
            $table->foreignId('listing_id')->constrained();
            $table->foreignId('renter_id')->constrained('users');
            $table->foreignId('lister_id')->constrained('users');

            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('days');

            // Money snapshot taken at checkout (rates can change later).
            $table->decimal('daily_rate', 12, 2);
            $table->decimal('subtotal', 12, 2);           // daily_rate * days
            $table->decimal('fee_rate', 5, 4);            // 0.10 / 0.07 / 0.05
            $table->decimal('platform_fee', 12, 2);
            $table->decimal('deposit_amount', 12, 2);
            $table->decimal('total', 12, 2);              // subtotal + fee + deposit
            $table->string('currency', 3)->default('LKR');

            /**
             * State machine:
             * pending_confirmation → confirmed → active → awaiting_return
             *   → returned → completed → closed
             * branches: cancelled | no_show | disputed
             */
            $table->string('status')->default('pending_confirmation');

            // Contact gating — Constraint 04: phone revealed only after payment.
            $table->boolean('phone_revealed')->default(false);

            // Acceptances captured pre-payment (Page 09).
            $table->timestamp('cancellation_policy_accepted_at')->nullable();
            $table->timestamp('rental_agreement_accepted_at')->nullable();

            // Lifecycle timestamps.
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            // Lister confirm-return SLA (Page 16: 24hr inaction → dispute window).
            $table->timestamp('return_confirm_deadline')->nullable();

            $table->timestamps();

            $table->index(['renter_id', 'status']);
            $table->index(['lister_id', 'status']);
            $table->index(['listing_id', 'start_date', 'end_date']);
        });

        // now that bookings exists, wire the availability FK
        Schema::table('listing_unavailabilities', function (Blueprint $table) {
            $table->foreign('booking_id')->references('id')->on('bookings')->nullOnDelete();
        });

        // ── Payments (idempotent) ──
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            // gateway: payhere | ipay | stripe
            $table->string('gateway');
            // type: rental | deposit | refund | lister_payout | lister_compensation
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('LKR');
            // status: pending | succeeded | failed | refunded
            $table->string('status')->default('pending');
            $table->string('gateway_reference')->nullable();
            $table->string('payment_token')->nullable();  // tokenised — no raw card data (Constraint 09)
            // Idempotency — Constraint 06: unique key blocks double charges.
            $table->string('idempotency_key')->unique();
            $table->json('gateway_payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'type']);
        });

        // ── Deposit escrow ledger (manual release only — Constraint 03) ──
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('LKR');
            /**
             * status: held | pending_release | returned_to_renter
             *       | released_to_lister | partially_released | forfeited
             */
            $table->string('status')->default('held');
            $table->decimal('amount_to_renter', 12, 2)->nullable();
            $table->decimal('amount_to_lister', 12, 2)->nullable();

            // Admin SLA on unactioned holds — 48hr, alert 36hr (Constraint 12).
            $table->timestamp('sla_deadline')->nullable();
            $table->timestamp('sla_alerted_at')->nullable();

            // Who released and how: lister_confirm | admin_resolution | sla_default
            $table->string('release_channel')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });

        // ── Condition photos — hard gate (Constraint 02) ──
        Schema::create('condition_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users');
            // phase: pickup | return
            $table->string('phase');
            $table->string('path');
            $table->timestamp('taken_at');                // timestamped (Constraint 02)
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            // upload_status supports offline auto-retry (Constraint 09)
            $table->string('upload_status')->default('uploaded'); // queued | uploading | uploaded | failed
            $table->timestamps();

            $table->index(['booking_id', 'phase']);
        });

        // ── Cancellations (tiered — Page 23) ──
        Schema::create('cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cancelled_by')->constrained('users');
            // tier: t_7plus | t_3_6 | t_under_3 | no_show
            $table->string('tier');
            $table->decimal('rental_refund', 12, 2)->default(0);
            $table->decimal('deposit_refund', 12, 2)->default(0);
            $table->decimal('lister_compensation', 12, 2)->default(0); // 25% late-cancel
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        // ── Rental agreements (server-side PDF per transaction) ──
        Schema::create('rental_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('pdf_path')->nullable();
            $table->json('snapshot')->nullable();         // terms + parties at time of booking
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('listing_unavailabilities', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
        });
        Schema::dropIfExists('rental_agreements');
        Schema::dropIfExists('cancellations');
        Schema::dropIfExists('condition_photos');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
    }
};

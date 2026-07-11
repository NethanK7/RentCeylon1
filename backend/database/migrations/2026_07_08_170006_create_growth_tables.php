<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subscriptions, badges, referrals & share links.
 *
 * Badge separation (Global Constraint 01) is modelled explicitly:
 * every badge row carries a `class` of EARNED or PAID so UI can render
 * them in spatially/visually distinct zones on every page.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Subscriptions (lister plans — Page 17) ──
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // tier: basic | standard | premium | property (property = separate model)
            $table->string('tier')->default('basic');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 3)->default('LKR');
            // status: active | paused | cancelled | grace
            $table->string('status')->default('active');
            $table->unsignedInteger('listing_limit')->default(3);
            $table->unsignedInteger('photo_slots')->default(5);
            $table->boolean('badge_eligible')->default(false);
            $table->timestamp('current_period_end')->nullable();
            // Cancel flow (Amendment 06): 3-day grace, auto-reactivate.
            $table->timestamp('grace_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('LKR');
            $table->string('status')->default('paid');    // paid | pending | failed
            $table->string('gateway_reference')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });

        // ── Badge definitions ──
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();              // top_rated | verified_item | fast_responder | sponsored | featured
            $table->string('name');
            // class: earned | paid  (Constraint 01 — never mixed)
            $table->string('class');
            $table->string('icon');                        // lucide icon
            $table->string('color');                       // colour family token
            $table->string('label');                       // "Earned" | "Verified" | "Sponsored" | "Featured"
            $table->text('criteria')->nullable();
            $table->timestamps();
        });

        // Badges attached to a listing (Verified Item, Sponsored, Featured, Top Rated).
        Schema::create('listing_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained();
            $table->string('class');                       // denormalized earned|paid
            $table->timestamp('expires_at')->nullable();  // paid badges expire
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['listing_id', 'badge_id']);
        });

        // Badges attached to a user profile (Top Rated, Fast Responder).
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained();
            $table->string('class');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'badge_id']);
        });

        // ── Referrals (Page 28) ──
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->string('invited_email')->nullable();
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete();
            // status: invited | signed_up | converted | rewarded
            $table->string('status')->default('invited');
            // reward_type: free_month
            $table->string('reward_type')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('rewarded_at')->nullable();
            $table->timestamps();
        });

        // ── Trackable share/short links (listing shares + referrals) ──
        Schema::create('share_links', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();             // short code
            // target: listing | referral
            $table->string('target_type');
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users');
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_links');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('listing_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('subscriptions');
    }
};

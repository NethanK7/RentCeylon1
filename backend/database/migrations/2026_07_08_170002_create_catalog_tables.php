<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog: categories (with day-one feature flags — Global Constraint 7),
 * a flexible typed-attribute system that powers rich filters
 * (e.g. Vehicles → vehicle_type / transmission / fuel / seats / brand),
 * listings, listing photos, and per-listing attribute values used for search.
 *
 * "Rent anything and everything": categories form a tree, and each category
 * declares its own filterable attributes — no schema change to add a category.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Categories (tree + feature flag) ──
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();          // lucide icon name
            $table->text('description')->nullable();
            // kind drives UI/filters: standard | vehicle | property | space | service
            $table->string('kind')->default('standard');
            // Feature flag — Constraint 7: all categories exist in schema, most toggled off.
            $table->boolean('is_enabled')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_enabled', 'parent_id']);
        });

        // ── Category attribute definitions (the "typed filters") ──
        Schema::create('category_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('key');                        // machine key e.g. "vehicle_type"
            $table->string('label');                      // "Vehicle type"
            // type: select | multiselect | number | boolean | text
            $table->string('type')->default('select');
            $table->json('options')->nullable();          // for select/multiselect
            $table->string('unit')->nullable();           // e.g. "km", "seats", "cc"
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['category_id', 'key']);
        });

        // ── Listings ──
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // lister
            $table->foreignId('category_id')->constrained();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            // condition: new | like_new | good | fair
            $table->string('condition')->default('good');

            // Pricing (lister-set). Deposit shown explicitly (Constraint / Page 03).
            $table->decimal('daily_rate', 12, 2);
            $table->decimal('security_deposit', 12, 2)->default(0);
            $table->string('currency', 3)->default('LKR');

            // Location — city/district only, NOT full address (Page 14 constraint).
            $table->string('city');
            $table->string('district');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            // status: draft | pending_verification | active | paused | removed
            $table->string('status')->default('draft');

            // Denormalized helpers for browse/search performance.
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('bookings_count')->default(0);

            $table->json('specs')->nullable();            // freeform spec bullets
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'category_id']);
            $table->index(['city', 'district']);
        });

        // ── Listing photos (min 3 enforced at app level — Constraint) ──
        Schema::create('listing_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Per-listing attribute values (drives typed filtering) ──
        Schema::create('listing_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value')->nullable();          // stored representation
            $table->decimal('value_number', 14, 2)->nullable(); // for numeric range filters
            $table->timestamps();

            $table->unique(['listing_id', 'category_attribute_id'], 'lav_unique');
            $table->index(['category_attribute_id', 'value']);
            $table->index(['category_attribute_id', 'value_number']);
        });

        // ── Availability blocks (dates a listing is unavailable) ──
        Schema::create('listing_unavailabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason')->nullable();         // booked | blocked
            $table->foreignId('booking_id')->nullable();  // set FK later to avoid cycle
            $table->timestamps();

            $table->index(['listing_id', 'start_date', 'end_date']);
        });

        // ── Listing drafts (Constraint 09: draft auto-save, offline-safe) ──
        Schema::create('listing_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('payload');                      // whole form state
            $table->timestamps();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_drafts');
        Schema::dropIfExists('listing_unavailabilities');
        Schema::dropIfExists('listing_attribute_values');
        Schema::dropIfExists('listing_photos');
        Schema::dropIfExists('listings');
        Schema::dropIfExists('category_attributes');
        Schema::dropIfExists('categories');
    }
};

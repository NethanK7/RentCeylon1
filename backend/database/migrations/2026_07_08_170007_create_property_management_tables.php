<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Property Management Service (Pages 24–26) — for Sri Lankans abroad.
 * Owner (abroad) ↔ RentLoop-assigned local Manager. Separate 8–12% monthly model.
 * Managers cannot modify financial records (record-only). Inspection photos
 * are mandatory before a report submits, timestamped + geo-tagged.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Waitlist / enquiry (landing CTA).
        Schema::create('pm_enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('country')->nullable();         // where owner lives now
            $table->string('property_city')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new');       // new | contacted | onboarded
            $table->timestamps();
        });

        Schema::create('managed_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('address');                      // full address — private to owner/manager
            $table->string('city');
            $table->string('district');
            $table->decimal('monthly_rent', 12, 2);
            $table->decimal('management_fee_rate', 5, 4)->default(0.10); // 8–12%
            $table->string('currency', 3)->default('LKR');
            $table->string('owner_timezone')->default('Asia/Colombo'); // show in owner's tz
            // tenant
            $table->string('tenant_name')->nullable();
            $table->string('tenant_phone')->nullable();
            $table->date('lease_start')->nullable();
            $table->date('lease_end')->nullable();
            $table->string('status')->default('active');    // active | vacant | ended
            $table->timestamps();
        });

        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('managed_properties')->cascadeOnDelete();
            $table->foreignId('manager_id')->constrained('users');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('scheduled'); // scheduled | completed
            $table->timestamps();
        });

        Schema::create('inspection_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->timestamp('taken_at');                  // timestamped
            $table->decimal('lat', 10, 7)->nullable();      // geo-tagged
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('managed_properties')->cascadeOnDelete();
            $table->foreignId('raised_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('status')->default('raised');    // raised | in_progress | resolved
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('rent_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('managed_properties')->cascadeOnDelete();
            $table->string('period');                       // YYYY-MM
            $table->decimal('amount', 12, 2);
            $table->decimal('management_fee', 12, 2)->default(0);
            $table->string('status')->default('outstanding'); // paid | outstanding | late
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['property_id', 'period']);
        });

        Schema::create('owner_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('managed_properties')->cascadeOnDelete();
            $table->string('period');                       // YYYY-MM
            $table->string('pdf_path')->nullable();
            $table->json('summary')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_statements');
        Schema::dropIfExists('rent_collections');
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('inspection_photos');
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('managed_properties');
        Schema::dropIfExists('pm_enquiries');
    }
};

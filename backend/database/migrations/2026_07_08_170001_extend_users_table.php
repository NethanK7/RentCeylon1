<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends the default users table with everything RentCeylon needs:
 * role-based access (renter/lister/admin/manager), profile, trust signals,
 * ToS acceptance (Global Constraint 10), ID verification gate (Constraint 11),
 * response metrics (feeds Fast Responder badge), and referral tracking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role-based access control. Spec: renter / lister / admin / manager.
            $table->string('role')->default('renter')->after('email');

            // Profile
            $table->string('phone')->nullable()->after('role');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->string('city')->nullable()->after('avatar_path');
            $table->string('district')->nullable()->after('city');
            $table->text('bio')->nullable()->after('district');

            // ToS — Global Constraint 10: non-negotiable, explicitly ticked, timestamped.
            $table->timestamp('tos_accepted_at')->nullable()->after('bio');

            // ID verification gate — Global Constraint 11.
            // pending | approved | rejected | unsubmitted
            $table->string('id_verification_status')->default('unsubmitted')->after('tos_accepted_at');

            // Fast Responder badge metrics: <1hr response on 80% of messages.
            $table->unsignedInteger('messages_received')->default(0)->after('id_verification_status');
            $table->unsignedInteger('messages_responded_fast')->default(0)->after('messages_received');
            $table->unsignedInteger('avg_response_minutes')->nullable()->after('messages_responded_fast');

            // Aggregate rating cache (source of truth is reviews table).
            $table->decimal('rating_avg', 3, 2)->default(0)->after('avg_response_minutes');
            $table->unsignedInteger('rating_count')->default(0)->after('rating_avg');

            // Referral / growth (Page 28).
            $table->string('referral_code')->nullable()->unique()->after('rating_count');
            $table->foreignId('referred_by')->nullable()->after('referral_code')
                ->constrained('users')->nullOnDelete();

            // Account moderation (admin: suspend / ban).
            $table->timestamp('suspended_at')->nullable()->after('referred_by');
            $table->string('suspended_reason')->nullable()->after('suspended_at');

            $table->index('role');
            $table->index('id_verification_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by');
            $table->dropColumn([
                'role', 'phone', 'avatar_path', 'city', 'district', 'bio',
                'tos_accepted_at', 'id_verification_status',
                'messages_received', 'messages_responded_fast', 'avg_response_minutes',
                'rating_avg', 'rating_count', 'referral_code',
                'suspended_at', 'suspended_reason',
            ]);
        });
    }
};

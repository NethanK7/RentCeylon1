<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform ops:
 *  - audit_logs: immutable trail, timestamp + user id on every admin action
 *    (Admin Panel, Constraint — immutable audit trail).
 *  - sla_events: single source of truth for every SLA clock so breaches are
 *    "never dropped silently" (ID 24hr, disputes 72hr, deposits 48hr — Constraint 12).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');                       // e.g. dispute.resolved
            $table->string('subject_type')->nullable();     // model class
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();  // immutable, no updated_at

            $table->index(['subject_type', 'subject_id']);
            $table->index('action');
        });

        Schema::create('sla_events', function (Blueprint $table) {
            $table->id();
            // kind: id_verification | dispute | deposit_hold | return_confirm
            $table->string('kind');
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->timestamp('due_at');                     // hard deadline
            $table->timestamp('alert_at')->nullable();       // early-warning threshold
            $table->boolean('alerted')->default(false);
            $table->boolean('breached')->default(false);
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['kind', 'resolved']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_events');
        Schema::dropIfExists('audit_logs');
    }
};

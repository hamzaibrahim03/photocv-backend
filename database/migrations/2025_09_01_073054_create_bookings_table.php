<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->unsignedSmallInteger('booking_type_id');
            $table->unsignedSmallInteger('lead_source_id')->nullable();
            $table->string('booking_status')->default('draft');

            // Client Info
            $table->string('client_name');
            $table->string('email')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('secondary_contact_number')->nullable();
            $table->text('address')->nullable();

            // Schedule
            $table->date('event_date')->nullable();
            $table->date('pre_event_meeting_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('set_reminder')->default(false);

            // Services
            $table->json('service_ids')->nullable();
            $table->string('location')->nullable();
            $table->string('deliverables')->nullable();
            $table->text('special_requirements')->nullable();

            // Notes
            $table->text('internal_notes')->nullable();
            $table->text('client_notes')->nullable();
            $table->string('contract_status')->default('pending');
            $table->text('requirements')->nullable();

            // Payments
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('deposit_received', 10, 2)->default(0);
            $table->decimal('remaining_balance', 10, 2)->default(0);
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->date('payment_due_date')->nullable();
            $table->string('invoice_number')->nullable();

            $table->boolean('backup_gear_needed')->default(false);

            $table->timestamps();
        });

        // Pivot for gear usage
        Schema::create('booking_gear', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('gear_id');
            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('gear_id')->references('id')->on('gears')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_gear');
        Schema::dropIfExists('bookings');
    }
};

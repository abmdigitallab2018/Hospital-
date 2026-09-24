<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Subscription Plans
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->integer('max_doctors')->default(1);
            $table->integer('max_patients_monthly')->default(100);
            $table->integer('max_staff')->default(3);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        // 2. Clinics (Tenants)
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('India');
            $table->string('registration_number')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('currency')->default('INR');
            $table->string('currency_symbol')->default('₹');
            $table->string('time_zone')->default('Asia/Kolkata');
            $table->string('logo_path')->nullable();
            $table->string('invoice_prefix')->default('INV');
            $table->text('prescription_disclaimer')->nullable();
            $table->decimal('consultation_fee', 10, 2)->default(500.00);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Clinic Subscriptions
        Schema::create('clinic_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->restrictOnDelete();
            $table->enum('status', ['active', 'trialing', 'past_due', 'canceled', 'expired'])->default('active');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
        });

        // 4. Update Users table for roles, clinics, and phone
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained('clinics')->nullOnDelete();
            $table->enum('role', ['super_admin', 'clinic_admin', 'doctor', 'receptionist', 'accountant', 'patient'])->default('clinic_admin')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->string('avatar')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('avatar');
        });

        // 5. Clinic User Pivot
        Schema::create('clinic_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['clinic_admin', 'doctor', 'receptionist', 'accountant', 'patient']);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['clinic_id', 'user_id']);
        });

        // 6. Doctors profile
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('specialization');
            $table->string('qualification');
            $table->string('license_number');
            $table->integer('experience_years')->default(0);
            $table->decimal('consultation_fee', 10, 2)->default(500.00);
            $table->string('room_number')->nullable();
            $table->text('bio')->nullable();
            $table->string('signature_path')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['clinic_id', 'is_available']);
        });

        // 7. Doctor Schedules
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->tinyInteger('day_of_week'); // 0=Sun, 1=Mon, ..., 6=Sat
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('slot_duration_minutes')->default(15);
            $table->integer('max_patients')->default(25);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['clinic_id', 'doctor_id', 'day_of_week']);
        });

        // 8. Patients
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('patient_uid'); // e.g. P-0001
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('blood_group', 10)->default('Unknown');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('allergies')->nullable();
            $table->text('existing_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['clinic_id', 'patient_uid']);
            $table->index(['clinic_id', 'phone']);
            $table->index(['clinic_id', 'status']);
        });

        // 9. Services Catalog
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('category')->default('General');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['clinic_id', 'is_active']);
        });

        // 10. Appointments
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->string('appointment_number');
            $table->integer('token_number');
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('type', ['in_person', 'follow_up', 'emergency'])->default('in_person');
            $table->enum('status', ['scheduled', 'checked_in', 'in_consultation', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'doctor_id', 'appointment_date']);
            $table->index(['clinic_id', 'status']);
            $table->index(['clinic_id', 'patient_id']);
        });

        // 11. Visits (Consultation Records)
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->date('visit_date');
            $table->text('chief_complaints');
            $table->text('symptoms')->nullable();
            $table->text('examination_notes')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('clinical_advice')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_instructions')->nullable();
            $table->enum('status', ['in_progress', 'completed'])->default('completed');
            $table->timestamps();

            $table->index(['clinic_id', 'patient_id']);
            $table->index(['clinic_id', 'doctor_id']);
            $table->index(['clinic_id', 'visit_date']);
        });

        // 12. Vitals
        Schema::create('vitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->decimal('temperature', 4, 1)->nullable(); // e.g. 98.6 F
            $table->integer('blood_pressure_systolic')->nullable();
            $table->integer('blood_pressure_diastolic')->nullable();
            $table->integer('pulse_rate')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->integer('oxygen_saturation')->nullable(); // SpO2 %
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('bmi', 4, 1)->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index(['clinic_id', 'patient_id']);
        });

        // 13. Prescriptions
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->string('prescription_number');
            $table->date('date');
            $table->text('notes')->nullable();
            $table->text('disclaimer')->nullable();
            $table->boolean('is_signed')->default(true);
            $table->timestamps();

            $table->index(['clinic_id', 'patient_id']);
            $table->index(['clinic_id', 'doctor_id']);
        });

        // 14. Prescription Items
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->string('medicine_name');
            $table->string('dosage'); // e.g. '1 Tablet'
            $table->string('frequency'); // e.g. '1-0-1'
            $table->string('duration'); // e.g. '5 Days'
            $table->string('instructions')->nullable(); // e.g. 'After food'
            $table->timestamps();

            $table->index(['prescription_id']);
        });

        // 15. Prescription Templates
        Schema::create('prescription_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('items');
            $table->timestamps();
        });

        // 16. Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('balance_amount', 10, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'refunded'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'patient_id']);
            $table->index(['clinic_id', 'invoice_date']);
            $table->index(['clinic_id', 'payment_status']);
        });

        // 17. Invoice Items
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('description');
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->integer('quantity')->default(1);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 18. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('payment_number');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'upi', 'card', 'bank_transfer', 'cheque'])->default('cash');
            $table->string('transaction_reference')->nullable();
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['completed', 'refunded'])->default('completed');
            $table->timestamps();

            $table->index(['clinic_id', 'invoice_id']);
            $table->index(['clinic_id', 'payment_date']);
        });

        // 19. Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->string('title');
            $table->string('category')->default('General');
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->enum('payment_method', ['cash', 'upi', 'card', 'bank_transfer'])->default('cash');
            $table->string('vendor')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['clinic_id', 'expense_date']);
        });

        // 20. Follow Ups
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->date('follow_up_date');
            $table->enum('status', ['pending', 'completed', 'missed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('reminded_at')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'follow_up_date']);
            $table->index(['clinic_id', 'status']);
        });

        // 21. Notifications Log
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->enum('channel', ['email', 'sms', 'whatsapp', 'in_app'])->default('in_app');
            $table->string('recipient');
            $table->string('title');
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('sent');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'status']);
        });

        // 22. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // created, updated, deleted, status_changed
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'action']);
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('prescription_templates');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('vitals');
        Schema::dropIfExists('visits');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('services');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('doctor_schedules');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('clinic_user');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropColumn(['clinic_id', 'role', 'phone', 'avatar', 'is_active']);
        });

        Schema::dropIfExists('clinic_subscriptions');
        Schema::dropIfExists('clinics');
        Schema::dropIfExists('subscription_plans');
    }
};

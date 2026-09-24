<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClinicSettingController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientPortalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Authenticated Core Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Main Clinic Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Super Admin Switch Clinic Context
    Route::post('/clinic/switch', [SuperAdminController::class, 'switchClinic'])->name('clinic.switch');

    // Patients Management
    Route::get('/patients/export/csv', [PatientController::class, 'export'])->name('patients.export');
    Route::resource('patients', PatientController::class);

    // Appointments & Booking
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::post('/appointments/{appointment}/check-in', [AppointmentController::class, 'checkIn'])->name('appointments.checkIn');
    Route::put('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
    Route::put('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::get('/api/available-slots', [AppointmentController::class, 'getAvailableSlots'])->name('appointments.availableSlots');

    // Live Reception Waiting Room Queue
    Route::get('/queue', [QueueController::class, 'index'])->name('queue.index');

    // Consultations & Clinical Records
    Route::get('/consultations', [ConsultationController::class, 'index'])->name('consultations.index');
    Route::get('/consultations/create', [ConsultationController::class, 'create'])->name('consultations.create');
    Route::post('/consultations', [ConsultationController::class, 'store'])->name('consultations.store');
    Route::get('/consultations/{consultation}', [ConsultationController::class, 'show'])->name('consultations.show');

    // Prescriptions
    Route::get('/prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::get('/prescriptions/{prescription}', [PrescriptionController::class, 'show'])->name('prescriptions.show');
    Route::get('/prescriptions/{prescription}/print', [PrescriptionController::class, 'print'])->name('prescriptions.print');
    Route::post('/prescription-templates', [PrescriptionController::class, 'storeTemplate'])->name('prescription-templates.store');

    // Invoices & Billing
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');

    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');

    // Follow-ups & Reminders
    Route::get('/follow-ups', [FollowUpController::class, 'index'])->name('follow-ups.index');
    Route::put('/follow-ups/{followUp}/status', [FollowUpController::class, 'updateStatus'])->name('follow-ups.updateStatus');
    Route::post('/follow-ups/{followUp}/remind', [FollowUpController::class, 'sendReminder'])->name('follow-ups.remind');

    // Doctors & Schedules
    Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
    Route::post('/doctors', [DoctorController::class, 'store'])->name('doctors.store');
    Route::put('/doctors/{doctor}', [DoctorController::class, 'update'])->name('doctors.update');
    Route::get('/doctors/{doctor}/schedule', [DoctorController::class, 'schedule'])->name('doctors.schedule');
    Route::put('/doctors/{doctor}/schedule', [DoctorController::class, 'updateSchedule'])->name('doctors.schedule.update');

    // Staff Management (Clinic Admin only)
    Route::middleware(['role:clinic_admin'])->group(function () {
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');

        // Clinic Settings
        Route::get('/settings/clinic', [ClinicSettingController::class, 'index'])->name('settings.clinic');
        Route::put('/settings/clinic', [ClinicSettingController::class, 'update'])->name('settings.clinic.update');
    });

    // Services Catalog
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    // Expenses (Clinic Admin + Accountant)
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Reports & Analytics (Clinic Admin & Accountant)
    Route::middleware(['role:clinic_admin,accountant'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/csv', [ReportController::class, 'export'])->name('reports.export');
    });

    // Super Admin Routes (Role protected)
    Route::middleware(['role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/clinics', [SuperAdminController::class, 'clinics'])->name('clinics');
        Route::post('/clinics', [SuperAdminController::class, 'storeClinic'])->name('clinics.store');
        Route::post('/clinics/{clinic}/toggle', [SuperAdminController::class, 'toggleClinicStatus'])->name('clinics.toggle');
        Route::get('/plans', [SuperAdminController::class, 'plans'])->name('plans');
    });

    // Patient Portal Routes
    Route::prefix('portal')->name('patient.')->group(function () {
        Route::get('/dashboard', [PatientPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/appointments', [PatientPortalController::class, 'appointments'])->name('appointments');
        Route::post('/appointments', [PatientPortalController::class, 'requestAppointment'])->name('appointments.request');
        Route::get('/prescriptions', [PatientPortalController::class, 'prescriptions'])->name('prescriptions');
        Route::get('/invoices', [PatientPortalController::class, 'invoices'])->name('invoices');
        Route::post('/invoices/{invoice}/pay', [PatientPortalController::class, 'payInvoice'])->name('invoices.pay');
    });

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

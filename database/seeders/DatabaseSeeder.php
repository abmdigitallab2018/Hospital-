<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicSubscription;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Expense;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Service;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Visit;
use App\Models\Vital;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // ─── Subscription Plans ───
        $starterPlan = SubscriptionPlan::create([
            'name' => 'Starter Practice',
            'slug' => 'starter-practice',
            'description' => 'Ideal for solo practitioners and small consulting rooms.',
            'price_monthly' => 1999.00,
            'price_yearly' => 19990.00,
            'max_doctors' => 1,
            'max_patients_monthly' => 300,
            'max_staff' => 2,
            'features' => [
                'Patient Records & History',
                'Appointment Scheduling & Queue',
                'Digital Prescriptions',
                'Standard Invoicing',
                'Email Notifications',
            ],
            'is_active' => true,
            'is_featured' => false,
        ]);

        $proPlan = SubscriptionPlan::create([
            'name' => 'Professional Clinic',
            'slug' => 'professional-clinic',
            'description' => 'Best for multi-doctor polyclinics and diagnostic centers.',
            'price_monthly' => 4999.00,
            'price_yearly' => 49990.00,
            'max_doctors' => 5,
            'max_patients_monthly' => 1500,
            'max_staff' => 10,
            'features' => [
                'All Starter Features',
                'Multi-Doctor Roster & Schedules',
                'Reception Queue & Token Display',
                'Advanced Billing, UPI & Partial Payments',
                'WhatsApp & SMS Reminders',
                'Prescription Templates & Print Letterhead',
                'Financial & Doctor Performance Reports',
            ],
            'is_active' => true,
            'is_featured' => true,
        ]);

        SubscriptionPlan::create([
            'name' => 'Enterprise Medical Network',
            'slug' => 'enterprise-network',
            'description' => 'Comprehensive solution for hospital chains and large clinics.',
            'price_monthly' => 12999.00,
            'price_yearly' => 129990.00,
            'max_doctors' => 25,
            'max_patients_monthly' => 10000,
            'max_staff' => 50,
            'features' => [
                'All Professional Features',
                'Unlimited Patient Capacity',
                'Custom Domain & Clinic Branding',
                'Patient Portal Access',
                'Automated Follow-up Campaigns',
                'Audit Logs & Compliance Export',
                'Dedicated Account Manager & Priority SLA',
            ],
            'is_active' => true,
            'is_featured' => false,
        ]);

        // ─── Super Admin ───
        User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@carepulse.app',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'phone' => '9999999999',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // ─── Demo Clinic ───
        $clinic = Clinic::create([
            'name' => 'Apollo Care Polyclinic',
            'slug' => 'apollo-care-polyclinic',
            'phone' => '+91 22 2845 9000',
            'emergency_phone' => '+91 98200 11223',
            'email' => 'contact@apollocare.com',
            'address' => 'Plot 42, Linking Road, Bandra West',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400050',
            'country' => 'India',
            'registration_number' => 'MH-BOM-MED-2024-849',
            'tax_number' => '27AAACA1234F1Z8',
            'currency' => 'INR',
            'currency_symbol' => '₹',
            'consultation_fee' => 600.00,
            'invoice_prefix' => 'APL',
            'prescription_disclaimer' => 'Valid for 15 days from date of issue. Please consult in case of any adverse drug reactions.',
            'status' => 'active',
        ]);

        ClinicSubscription::create([
            'clinic_id' => $clinic->id,
            'subscription_plan_id' => $proPlan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->addMonths(10),
            'auto_renew' => true,
        ]);

        // Second clinic (uses starter plan)
        $sunriseClinic = Clinic::create([
            'name' => 'Sunrise Family Health Clinic',
            'slug' => 'sunrise-family-health',
            'email' => 'hello@sunrisehealth.org',
            'phone' => '+91 20 2567 4321',
            'address' => 'Shop 10-12, Green Avenue, FC Road',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'postal_code' => '411004',
            'country' => 'India',
            'registration_number' => 'MH-PUN-MED-2023-112',
            'currency' => 'INR',
            'currency_symbol' => '₹',
            'consultation_fee' => 450.00,
            'invoice_prefix' => 'SFH',
            'status' => 'active',
        ]);

        ClinicSubscription::create([
            'clinic_id' => $sunriseClinic->id,
            'subscription_plan_id' => $starterPlan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonths(11),
            'auto_renew' => true,
        ]);

        // ─── Clinic Admin ───
        $clinicAdmin = User::create([
            'name' => 'Dr. Rakesh Singhania',
            'email' => 'admin@apollocare.com',
            'password' => Hash::make('password'),
            'role' => 'clinic_admin',
            'phone' => '+91 98201 23456',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $clinic->users()->attach($clinicAdmin->id, ['role' => 'clinic_admin', 'is_primary' => true]);

        // ─── Doctors ───
        $doctorUser1 = User::create([
            'name' => 'Dr. Rajesh Sharma',
            'email' => 'doctor@apollocare.com',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'phone' => '+91 98202 34567',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $doctor1 = Doctor::create([
            'clinic_id' => $clinic->id,
            'user_id' => $doctorUser1->id,
            'specialization' => 'Cardiologist',
            'qualification' => 'MBBS, MD (Medicine), DM (Cardiology)',
            'license_number' => 'MCI-CARD-2009-482',
            'experience_years' => 16,
            'consultation_fee' => 800.00,
            'room_number' => 'OPD-101',
            'bio' => 'Senior consultant interventional cardiologist specializing in preventive cardiology and hypertension.',
            'is_available' => true,
        ]);
        $clinic->users()->attach($doctorUser1->id, ['role' => 'doctor', 'is_primary' => false]);

        $doctorUser2 = User::create([
            'name' => 'Dr. Ananya Patel',
            'email' => 'doctor2@apollocare.com',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'phone' => '+91 98203 45678',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $doctor2 = Doctor::create([
            'clinic_id' => $clinic->id,
            'user_id' => $doctorUser2->id,
            'specialization' => 'Pediatrician',
            'qualification' => 'MBBS, DCH (Pediatrics), DNB',
            'license_number' => 'MMC-PED-2015-771',
            'experience_years' => 11,
            'consultation_fee' => 600.00,
            'room_number' => 'OPD-104',
            'bio' => 'Caring child specialist experienced in newborn care, pediatric nutrition, and immunization schedules.',
            'is_available' => true,
        ]);
        $clinic->users()->attach($doctorUser2->id, ['role' => 'doctor', 'is_primary' => false]);

        // Doctor Schedules: Mon=1, Tue=2, Wed=3, Thu=4, Fri=5, Sat=6 (0=Sun in migration)
        $days = [1, 2, 3, 4, 5, 6]; // Monday through Saturday
        foreach ([$doctor1, $doctor2] as $doc) {
            foreach ($days as $day) {
                DoctorSchedule::create([
                    'clinic_id' => $clinic->id,
                    'doctor_id' => $doc->id,
                    'day_of_week' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '18:00:00',
                    'slot_duration_minutes' => 20,
                    'max_patients' => 25,
                    'is_active' => true,
                ]);
            }
        }

        // ─── Receptionist ───
        $receptionistUser = User::create([
            'name' => 'Sunita Rao',
            'email' => 'receptionist@apollocare.com',
            'password' => Hash::make('password'),
            'role' => 'receptionist',
            'phone' => '+91 98205 67890',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $clinic->users()->attach($receptionistUser->id, ['role' => 'receptionist', 'is_primary' => false]);

        // ─── Accountant ───
        $accountantUser = User::create([
            'name' => 'Kunal Verma',
            'email' => 'accountant@apollocare.com',
            'password' => Hash::make('password'),
            'role' => 'accountant',
            'phone' => '+91 98206 78901',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $clinic->users()->attach($accountantUser->id, ['role' => 'accountant', 'is_primary' => false]);

        // ─── Services ───
        $services = [
            ['name' => 'General Doctor Consultation', 'code' => 'CONS-GEN', 'category' => 'Consultation', 'price' => 500.00],
            ['name' => 'Cardiology Specialist Consultation', 'code' => 'CONS-CARD', 'category' => 'Consultation', 'price' => 800.00],
            ['name' => 'Pediatric Consultation', 'code' => 'CONS-PED', 'category' => 'Consultation', 'price' => 600.00],
            ['name' => 'Blood Test - CBC', 'code' => 'LAB-CBC', 'category' => 'Lab Test', 'price' => 380.00],
            ['name' => 'Blood Glucose Rapid Test (GRBS)', 'code' => 'LAB-GRBS', 'category' => 'Lab Test', 'price' => 120.00],
            ['name' => '12-Lead ECG', 'code' => 'DIAG-ECG', 'category' => 'Diagnostics', 'price' => 450.00],
            ['name' => 'X-Ray Chest', 'code' => 'DIAG-XRC', 'category' => 'Diagnostics', 'price' => 500.00],
            ['name' => 'Nebulization Therapy', 'code' => 'PROC-NEB', 'category' => 'Procedure', 'price' => 250.00],
            ['name' => 'Aseptic Wound Dressing', 'code' => 'PROC-DRS', 'category' => 'Nursing', 'price' => 300.00],
            ['name' => 'Follow-Up Consultation', 'code' => 'FU-CONS', 'category' => 'Consultation', 'price' => 400.00],
        ];

        foreach ($services as $svc) {
            Service::create(array_merge($svc, ['clinic_id' => $clinic->id, 'is_active' => true]));
        }

        // ─── Patients ───
        $patientUser = User::create([
            'name' => 'Rahul Deshmukh',
            'email' => 'patient@demo.clinic',
            'password' => Hash::make('password'),
            'role' => 'patient',
            'phone' => '+91 98190 12345',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $clinic->users()->attach($patientUser->id, ['role' => 'patient', 'is_primary' => false]);

        $patientData = [
            ['uid' => 'APL-P-001', 'user_id' => $patientUser->id, 'first_name' => 'Rahul', 'last_name' => 'Deshmukh', 'dob' => '1990-05-14', 'gender' => 'male', 'blood_group' => 'B+', 'phone' => '+91 98190 12345', 'email' => 'patient@demo.clinic'],
            ['uid' => 'APL-P-002', 'user_id' => null, 'first_name' => 'Meera', 'last_name' => 'Kulkarni', 'dob' => '1984-11-22', 'gender' => 'female', 'blood_group' => 'O+', 'phone' => '+91 98200 45612', 'email' => 'meera.kulkarni@gmail.com'],
            ['uid' => 'APL-P-003', 'user_id' => null, 'first_name' => 'Aarav', 'last_name' => 'Kapoor', 'dob' => '2019-08-10', 'gender' => 'male', 'blood_group' => 'A+', 'phone' => '+91 98331 22334', 'email' => 'rohit.kapoor@outlook.com'],
            ['uid' => 'APL-P-004', 'user_id' => null, 'first_name' => 'Sunil', 'last_name' => 'Joshi', 'dob' => '1968-03-30', 'gender' => 'male', 'blood_group' => 'AB+', 'phone' => '+91 98210 77665', 'email' => 'sunil.joshi@rediffmail.com'],
            ['uid' => 'APL-P-005', 'user_id' => null, 'first_name' => 'Priya', 'last_name' => 'Nair', 'dob' => '1995-12-05', 'gender' => 'female', 'blood_group' => 'O-', 'phone' => '+91 98920 33445', 'email' => 'priya.nair95@gmail.com'],
            ['uid' => 'APL-P-006', 'user_id' => null, 'first_name' => 'Vikram', 'last_name' => 'Chawla', 'dob' => '1975-07-19', 'gender' => 'male', 'blood_group' => 'B-', 'phone' => '+91 98110 55443', 'email' => 'vchawla@yahoo.com'],
            ['uid' => 'APL-P-007', 'user_id' => null, 'first_name' => 'Ananya', 'last_name' => 'Sen', 'dob' => '2001-09-15', 'gender' => 'female', 'blood_group' => 'A-', 'phone' => '+91 98701 44332', 'email' => 'ananya.sen@college.edu'],
            ['uid' => 'APL-P-008', 'user_id' => null, 'first_name' => 'Ramesh', 'last_name' => 'Bhatia', 'dob' => '1959-02-14', 'gender' => 'male', 'blood_group' => 'O+', 'phone' => '+91 98205 99887', 'email' => 'ramesh.bhatia@corp.in'],
        ];

        $createdPatients = [];
        foreach ($patientData as $p) {
            $createdPatients[] = Patient::create([
                'clinic_id' => $clinic->id,
                'user_id' => $p['user_id'],
                'patient_uid' => $p['uid'],
                'first_name' => $p['first_name'],
                'last_name' => $p['last_name'],
                'date_of_birth' => $p['dob'],
                'gender' => $p['gender'],
                'blood_group' => $p['blood_group'],
                'phone' => $p['phone'],
                'email' => $p['email'],
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'status' => 'active',
            ]);
        }

        // ─── Appointments ───
        $today = Carbon::today();
        $tokenCounter = 1;
        $appointments = [];

        // Today's appointments
        $todayApptData = [
            ['patient' => $createdPatients[1], 'doctor' => $doctor1, 'hour' => 9, 'type' => 'follow_up', 'status' => 'in_consultation', 'reason' => 'Routine 3-month blood pressure review.'],
            ['patient' => $createdPatients[0], 'doctor' => $doctor1, 'hour' => 10, 'type' => 'in_person', 'status' => 'checked_in', 'reason' => 'High fever for 2 days, body ache, dry cough.'],
            ['patient' => $createdPatients[2], 'doctor' => $doctor2, 'hour' => 10, 'type' => 'in_person', 'status' => 'checked_in', 'reason' => 'Child has runny nose, mild wheeze.'],
            ['patient' => $createdPatients[3], 'doctor' => $doctor1, 'hour' => 11, 'type' => 'in_person', 'status' => 'scheduled', 'reason' => 'Fasting blood sugar follow-up and ECG checkup.'],
            ['patient' => $createdPatients[4], 'doctor' => $doctor1, 'hour' => 17, 'type' => 'in_person', 'status' => 'scheduled', 'reason' => 'Severe throbbing left-sided headache with nausea.'],
        ];

        foreach ($todayApptData as $i => $data) {
            $apt = Appointment::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $data['patient']->id,
                'doctor_id' => $data['doctor']->id,
                'appointment_date' => $today->format('Y-m-d'),
                'start_time' => sprintf('%02d:00:00', $data['hour']),
                'end_time' => sprintf('%02d:20:00', $data['hour']),
                'token_number' => $tokenCounter++,
                'type' => $data['type'],
                'reason' => $data['reason'],
                'status' => $data['status'],
                'checked_in_at' => in_array($data['status'], ['checked_in', 'in_consultation']) ? now()->subMinutes(10 + $i * 5) : null,
            ]);
            $appointments[] = $apt;
        }

        // ─── Past Appointments with Visits, Vitals, Prescriptions, Invoices, Payments ───
        foreach ($createdPatients as $i => $patient) {
            $doctor = ($i % 2 === 0) ? $doctor1 : $doctor2;

            for ($j = 1; $j <= 3; $j++) {
                $date = $today->copy()->subDays($j * 7 + $i);

                $apt = Appointment::create([
                    'clinic_id' => $clinic->id,
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'appointment_date' => $date->format('Y-m-d'),
                    'start_time' => '10:00:00',
                    'end_time' => '10:20:00',
                    'token_number' => $tokenCounter++,
                    'type' => 'in_person',
                    'reason' => ['Fever and body pain', 'Routine checkup', 'Follow-up review', 'Blood pressure check'][$j % 4],
                    'status' => 'completed',
                    'checked_in_at' => $date->copy()->setTime(9, 50),
                    'completed_at' => $date->copy()->setTime(10, 25),
                ]);

                $visit = Visit::create([
                    'clinic_id' => $clinic->id,
                    'appointment_id' => $apt->id,
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'visit_date' => $date->format('Y-m-d'),
                    'chief_complaints' => ['Cough and cold', 'Fever', 'Back pain', 'Headache', 'Stomach ache'][$j % 5],
                    'symptoms' => 'Mild symptoms, manageable.',
                    'examination_notes' => 'Chest clear, no wheezing. BP within normal limits.',
                    'diagnosis' => ['Viral URI', 'URTI', 'Lumbar strain', 'Tension headache', 'Gastritis'][$j % 5],
                    'treatment_plan' => 'Rest, hydration, prescribed medications.',
                    'clinical_advice' => 'Avoid cold beverages. Follow-up if no improvement in 5 days.',
                    'status' => 'completed',
                ]);

                Vital::create([
                    'clinic_id' => $clinic->id,
                    'visit_id' => $visit->id,
                    'patient_id' => $patient->id,
                    'temperature' => round(36.5 + (rand(0, 20) / 10), 1),
                    'blood_pressure_systolic' => rand(110, 140),
                    'blood_pressure_diastolic' => rand(70, 90),
                    'pulse_rate' => rand(68, 98),
                    'respiratory_rate' => rand(14, 20),
                    'oxygen_saturation' => rand(96, 99),
                    'weight_kg' => round(55 + rand(0, 40) + (rand(0, 9) / 10), 1),
                    'height_cm' => rand(155, 185),
                    'recorded_at' => $date->copy()->setTime(10, 0),
                ]);

                $rx = Prescription::create([
                    'clinic_id' => $clinic->id,
                    'visit_id' => $visit->id,
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'prescription_number' => 'RX-' . $date->format('Ymd') . '-' . sprintf('%04d', $visit->id),
                    'date' => $date->format('Y-m-d'),
                    'notes' => 'Take medications as prescribed. Rest adequately.',
                    'disclaimer' => $clinic->prescription_disclaimer,
                    'is_signed' => true,
                ]);

                PrescriptionItem::create([
                    'prescription_id' => $rx->id,
                    'medicine_name' => ['Paracetamol 500mg', 'Amoxicillin 500mg', 'Ibuprofen 400mg', 'Cetirizine 10mg', 'Pantoprazole 40mg'][rand(0, 4)],
                    'dosage' => ['1 tablet', '1 capsule', '1 tablet'][rand(0, 2)],
                    'frequency' => ['Once daily', 'Twice daily', 'Thrice daily', 'BD', 'TDS'][rand(0, 4)],
                    'duration' => ['3 days', '5 days', '7 days'][rand(0, 2)],
                    'instructions' => 'After food',
                ]);

                $fee = (float)$doctor->consultation_fee;
                $invNum = sprintf('APL-%d-%04d', date('Y'), $visit->id);
                $inv = Invoice::create([
                    'clinic_id' => $clinic->id,
                    'patient_id' => $patient->id,
                    'appointment_id' => $apt->id,
                    'visit_id' => $visit->id,
                    'invoice_number' => $invNum,
                    'invoice_date' => $date->format('Y-m-d'),
                    'due_date' => $date->format('Y-m-d'),
                    'subtotal' => $fee,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'total_amount' => $fee,
                    'paid_amount' => $fee,
                    'balance_amount' => 0,
                    'payment_status' => 'paid',
                ]);

                InvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'description' => 'Doctor Consultation - ' . ($doctor->user?->name ?? 'Doctor'),
                    'unit_price' => $fee,
                    'quantity' => 1,
                    'total' => $fee,
                ]);

                $payNum = sprintf('PAY-%d-%04d', date('Y'), $inv->id);
                Payment::create([
                    'clinic_id' => $clinic->id,
                    'invoice_id' => $inv->id,
                    'payment_number' => $payNum,
                    'amount' => $fee,
                    'payment_method' => ['cash', 'upi', 'card'][rand(0, 2)],
                    'payment_date' => $date->format('Y-m-d'),
                    'status' => 'completed',
                    'received_by_user_id' => $receptionistUser->id,
                ]);
            }
        }

        // ─── Unpaid Invoice (Patient Portal demo) ───
        $unpaidInv = Invoice::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $createdPatients[0]->id,
            'invoice_number' => 'APL-' . date('Y') . '-0999',
            'invoice_date' => $today->format('Y-m-d'),
            'due_date' => $today->copy()->addDays(7)->format('Y-m-d'),
            'subtotal' => 1200.00,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 1200.00,
            'paid_amount' => 0,
            'balance_amount' => 1200.00,
            'payment_status' => 'unpaid',
            'notes' => 'Pending payment for consultation + lab tests.',
        ]);
        foreach ([['General Consultation', 700], ['Blood Test - CBC', 350], ['ECG', 150]] as [$desc, $price]) {
            InvoiceItem::create([
                'invoice_id' => $unpaidInv->id,
                'description' => $desc,
                'unit_price' => $price,
                'quantity' => 1,
                'total' => $price,
            ]);
        }

        // ─── Follow-ups ───
        FollowUp::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $createdPatients[0]->id,
            'doctor_id' => $doctor1->id,
            'follow_up_date' => $today->copy()->addDays(3)->format('Y-m-d'),
            'status' => 'pending',
            'notes' => 'Blood sugar recheck after 3 days of medication.',
        ]);
        FollowUp::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $createdPatients[2]->id,
            'doctor_id' => $doctor1->id,
            'follow_up_date' => $today->copy()->addDays(7)->format('Y-m-d'),
            'status' => 'pending',
            'notes' => 'Review hypertension medication efficacy.',
        ]);
        FollowUp::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $createdPatients[1]->id,
            'doctor_id' => $doctor2->id,
            'follow_up_date' => $today->copy()->subDays(2)->format('Y-m-d'),
            'status' => 'missed',
            'notes' => 'Child vaccination follow-up.',
        ]);

        // ─── Expenses ───
        $expenseData = [
            ['Medicines & Supplies', 3500, 'bank_transfer', 'MedVendors Ltd', 'Monthly medical supply order'],
            ['Equipment Maintenance', 1800, 'bank_transfer', 'TechCare Services', 'ECG machine annual service'],
            ['Utilities', 5200, 'bank_transfer', 'MSEDCL', 'Electricity bill'],
            ['Staff Salaries', 45000, 'bank_transfer', null, 'Monthly payroll'],
            ['Rent', 25000, 'bank_transfer', 'Property Owner', 'Clinic premises rent'],
        ];

        foreach ($expenseData as [$category, $amount, $method, $vendor, $title]) {
            Expense::create([
                'clinic_id' => $clinic->id,
                'title' => $title,
                'category' => $category,
                'amount' => $amount,
                'expense_date' => $today->copy()->startOfMonth()->format('Y-m-d'),
                'payment_method' => $method,
                'vendor' => $vendor,
                'created_by_user_id' => $accountantUser->id,
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info("\n✅ CarePulse CMS demo data seeded successfully!\n");
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin', 'superadmin@carepulse.app', 'password'],
                ['Clinic Admin', 'admin@apollocare.com', 'password'],
                ['Doctor 1', 'doctor@apollocare.com', 'password'],
                ['Doctor 2', 'doctor2@apollocare.com', 'password'],
                ['Receptionist', 'receptionist@apollocare.com', 'password'],
                ['Accountant', 'accountant@apollocare.com', 'password'],
                ['Patient', 'patient@demo.clinic', 'password'],
            ]
        );
    }
}

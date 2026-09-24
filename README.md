# 🏥 CarePulse CMS — Clinic Management System

A production-ready, multi-tenant SaaS Clinic Management System built with **Laravel 12**, **Vue 3**, **Inertia.js v2**, **Tailwind CSS**, and **MySQL**.

---

## ✨ Features

### Multi-Role Platform
| Role | Capabilities |
|------|--------------|
| **Super Admin** | Manage all clinics, subscriptions, plans, platform settings |
| **Clinic Admin** | Manage clinic profile, doctors, staff, services, reports |
| **Doctor** | View appointments, record consultations, prescriptions, follow-ups |
| **Receptionist** | Register patients, book appointments, manage queue, generate invoices |
| **Accountant** | Payments, expenses, financial reports, outstanding balances |
| **Patient** | Portal to view appointments, prescriptions, invoices |

### Modules
- 📊 **Dashboard** — Real-time stats: today's appointments, revenue, pending payments, follow-ups
- 👥 **Patient Management** — Unique patient IDs, demographics, medical history, visit history, search & export
- 📅 **Appointment Management** — Doctor-wise calendar, slot booking, check-in, queue management, token numbers, double-booking prevention
- 😺 **Consultation Management** — Chief complaints, vitals, examination notes, diagnosis, treatment plan
- 📝 **Prescription Management** — Medicine details, dosage, frequency, printable PDF, doctor templates
- 💰 **Billing & Payments** — Invoices, partial payments, cash/UPI/card, outstanding balances, receipts
- 🔔 **Follow-ups & Reminders** — Follow-up scheduling, automated missed tracking, notification logging
- 💸 **Expenses** — Track operational costs by category with payment method tracking
- 📈 **Reports** — Revenue, collections, doctor-wise stats, payment methods, CSV export
- ⚙️ **Clinic Settings** — Profile, logo, consultation fees, invoice format, prescription disclaimer
- 🌐 **Patient Portal** — Appointment requests, prescription history, invoice payments
- 🔒 **Super Admin** — Clinic onboarding, subscription plans, clinic suspension/activation

---

## 🚀 Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Vue 3 Composition API, Inertia.js v2 |
| Styling | Tailwind CSS v3, Lucide Icons |
| Database | MySQL 8.0+ |
| Build Tool | Vite 7 |
| Auth | Laravel Breeze (Session-based) |
| Storage | Spatie Media Library |
| Queue | Database Queue |

---

## 💻 Requirements

- PHP 8.2+
- Composer
- Node.js 18+ (LTS) / npm
- MySQL 8.0+
- Git

---

## 🔧 Installation

### 1. Clone the Repository
```bash
git clone <repository-url> carepulse-cms
cd carepulse-cms
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node Dependencies
```bash
npm install
```

### 4. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure:
```
DB_DATABASE=clinic_cms
DB_USERNAME=your_mysql_user
DB_PASSWORD=your_mysql_password

MAIL_MAILER=smtp  # or 'log' for development
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=no-reply@yourclinic.com

QUEUE_CONNECTION=database  # or 'redis' for production
```

### 5. Create Database
```sql
CREATE DATABASE clinic_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Run Migrations
```bash
php artisan migrate
```

### 7. Seed Demo Data
```bash
php artisan db:seed
```

### 8. Build Frontend Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### 9. Start the Application
```bash
php artisan serve
```

Visit: `http://localhost:8000`

---

## 👤 Demo Accounts

After running `php artisan db:seed`:

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@carepulse.app | password |
| Clinic Admin | admin@demo.clinic | password |
| Doctor | doctor@demo.clinic | password |
| Receptionist | receptionist@demo.clinic | password |
| Accountant | accountant@demo.clinic | password |
| Patient Portal | patient@demo.clinic | password |

---

## ⚙️ Background Services

### Queue Worker (for notifications)
```bash
php artisan queue:work --tries=3
```

### Scheduled Tasks (daily follow-up tracking)
Add to crontab:
```cron
* * * * * cd /path/to/carepulse && php artisan schedule:run >> /dev/null 2>&1
```

Or run manually:
```bash
php artisan app:mark-missed-followups
```

---

## 🏦 Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# With coverage
php artisan test --coverage
```

### Test Database Setup
Create a separate test database and configure in `phpunit.xml`:
```xml
<env name="DB_DATABASE" value="clinic_cms_testing"/>
```

---

## 🚀 Production Deployment

### Environment Configuration
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourclinic.com

SESSION_ENCRYPT=true
BCRYPT_ROUNDS=12
```

### Optimization Commands
```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan storage:link
```

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourclinic.com;
    root /var/www/carepulse/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Supervisor for Queue Workers
```ini
[program:carepulse-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/carepulse/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stoponerror=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/carepulse-worker.log
```

---

## 📦 Database Schema

Key tables:
- `clinics` — Clinic profiles (multi-tenant root)
- `users` — All users with role and clinic_id
- `doctors` — Doctor profiles linked to users
- `patients` — Patient records scoped to clinic
- `appointments` — Scheduled appointments with token numbers
- `visits` — Consultation records (linked to appointment)
- `vitals` — Patient vitals per visit
- `prescriptions` + `prescription_items` — Medication records
- `invoices` + `invoice_items` — Billing records
- `payments` — Payment transactions
- `expenses` — Clinic operational expenses
- `follow_ups` — Scheduled follow-up reminders
- `subscription_plans` + `clinic_subscriptions` — SaaS subscription management
- `audit_logs` — Security audit trail
- `notifications` — Notification delivery log

---

## 🔒 Security

- Clinic-level data isolation via global `ClinicScope` model scope
- Role-based access control via `EnsureUserHasRole` middleware
- Clinic context validation via `SetClinicContext` middleware
- Audit logging for all critical actions
- CSRF protection on all forms
- Encrypted session storage
- Soft deletes for data integrity
- Medical data excluded from application logs

---

## 📞 Support & Contact

For issues, feature requests, or commercial deployments, contact: **support@carepulse.app**

---

**CarePulse CMS** — Built for India's healthcare professionals.

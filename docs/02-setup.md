# Setup & Installation

## Requirements

- PHP 8.3+
- Composer
- Node.js 18+ and npm
- PostgreSQL
- A PayMongo account (test keys are free)

---

## Step 1 — Clone and Install Dependencies

```bash
git clone https://github.com/Harleythetech/e-canteen-project
cd e-canteen-project

composer install
npm install
```

---

## Step 2 — Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and configure:

```dotenv
APP_NAME="PLSP E-Canteen"
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Manila

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ecanteen
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

PAYMONGO_SECRET_KEY=sk_test_xxxxxxxxxxxx
PAYMONGO_PUBLIC_KEY=pk_test_xxxxxxxxxxxx
PAYMONGO_WEBHOOK_SECRET=whsk_xxxxxxxxxxxx
```

> **PayMongo keys** — Get them from [dashboard.paymongo.com](https://dashboard.paymongo.com) under Developers → API Keys. Use test keys during development.

---

## Step 3 — Database Setup

```bash
php artisan migrate
php artisan db:seed
```

This creates the tables and seeds test data:

| Role | Email | Password |
|---|---|---|
| Student | student@example.com | password |
| Staff | staff@example.com | password |
| Admin | admin@example.com | password |

---

## Step 4 — Storage Link

Product images are stored in `storage/app/public`. Create the symlink:

```bash
php artisan storage:link
```

---

## Step 5 — Build Frontend Assets

```bash
npm run build
```

For development with hot reload:

```bash
npm run dev
```

---

## Step 6 — Start the Application

```bash
# All-in-one (server + queue + vite)
composer run dev

# Or individually:
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

The app will be available at `http://localhost:8000`.

---

## Step 7 — Scheduler (Production)

The scheduler auto-cancels unpaid orders older than 30 minutes. Add this single cron entry to your server:

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

For local testing, run manually:

```bash
php artisan orders:cancel-stale
```

---

## PayMongo Webhook Setup (Optional for Local Dev)

Webhooks are not required for local development — the app polls PayMongo directly when the student lands on the confirmation page. For production:

1. Go to PayMongo Dashboard → Developers → Webhooks
2. Add endpoint: `https://yourdomain.com/webhooks/paymongo`
3. Select event: `checkout_session.payment.paid`
4. Copy the webhook secret into `PAYMONGO_WEBHOOK_SECRET`

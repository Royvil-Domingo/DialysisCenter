# Dialysis Center Management System

A Laravel web app for a dialysis center — patient records, appointment scheduling, treatment session logging, charge tracking, and an AI-assisted FAQ chatbot ("Diabot") for patients and visitors.

## Roles

- **Admin** — everything staff can do, plus managing staff/admin accounts, viewing activity logs, and editing clinic settings (name, address, contact info, operating hours).
- **Staff** — manages patients, appointments, treatment records, and charges; views reports and notifications.
- **Patient** — not a login account. Patients have no password-based access; they look up their own appointment on the public homepage using a patient reference code and birthdate.

There is no public self-registration — staff and admin accounts are created by an admin via User Management (step 6 below), never self-signup.

## Setup

1. **Clone the repo**
   ```bash
   https://github.com/Royvil-Domingo/DialysisCenter.git
   cd <repo-name>
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JS dependencies and build frontend assets**
   ```bash
   npm install
   npm run build       # production build
   npm run dev          # or: run this instead, in a separate terminal, while developing
   ```

4. **Set up environment variables**
   ```bash
   copy .env.example .env      # Windows
   cp .env.example .env        # macOS/Linux
   php artisan key:generate
   ```
   Open `.env` and fill in:
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — your local MySQL credentials.
   - `ADMIN_EMAIL` and `ADMIN_PASSWORD` — the first admin account's login. **Set these before step 6** — the seeder that creates the admin account reads them once, at seed time, and silently does nothing if either is blank.
   - `CHATBOT_PROVIDER`, `GEMINI_API_KEY` / `OPENAI_API_KEY` — optional. Leave `CHATBOT_PROVIDER` unset (or `none`) to run without AI; Diabot still answers its ~50 seeded FAQs either way and only calls out to Gemini/OpenAI for questions outside that list.

5. **Create the database**
   Create an empty MySQL database matching `DB_DATABASE` from your `.env` (e.g. `dialysis_center`) in phpMyAdmin or the `mysql` CLI.

6. **Apply the schema and seed starting data**
   ```bash
   php artisan migrate --seed
   ```
   This creates every table from the migration history, then seeds the first admin account (from `ADMIN_EMAIL`/`ADMIN_PASSWORD`) and Diabot's preset FAQ library — don't rely on the app creating tables or the admin account on its own.

7. **Run the app**
   ```bash
   php artisan serve
   ```
   Visit `http://127.0.0.1:8000`. Log in with the admin account from step 6 to reach the dashboard, or visit the homepage and use **Check My Appointment** to try the public patient lookup — no account needed.

## Pages

| Page | Who | Route |
|---|---|---|
| Homepage | Everyone | `/` |
| Check My Appointment (patient lookup) | Public, no login | `/lookup` |
| Login | Everyone | `/login` |
| Dashboard | Staff, Admin | `/dashboard` |
| Patients | Staff, Admin | `/patients` |
| Appointments | Staff, Admin | `/appointments` |
| Treatment Records | Staff, Admin | `/treatment-records` |
| Charges | Staff, Admin | `/charges` |
| Reports | Staff, Admin | `/reports` |
| Notifications (view / post) | Staff, Admin / Admin only | `/notifications`, `/admin/notifications/create` |
| User Management | Admin | `/admin/users` |
| Activity Logs | Admin | `/admin/activity-logs` |
| Clinic Settings | Admin | `/admin/settings` |
| Demo preview (read-only, no login) | Public | `/demo/*` |

Diabot, the FAQ/AI chatbot, isn't a page of its own — it's a widget available on every page, backed by `POST /chatbot/message`.

## Appointment, treatment, and charge workflow

An appointment starts as `scheduled`. Staff mark it `completed` after the visit, or `cancelled` / `no_show` if it didn't happen. A completed appointment is typically paired with a treatment record (session details — pre/post weight, blood pressure, duration, complications) and, where applicable, a charge. A charge starts `unpaid`; staff mark it `paid` or `waived` once settled. The system tracks what a patient owes — it does not process any actual payment, by design.

## Diabot (the chatbot)

Diabot checks its seeded FAQ library first and answers instantly, with no external API call, whenever it finds a confident match. If a question isn't covered, and a provider is configured (`CHATBOT_PROVIDER=gemini` or `openai` in `.env`, with the matching API key), it falls back to that provider for a general, non-diagnostic answer. With no provider configured, Diabot still answers every FAQ in its library — it just tells patients to contact staff for anything outside that list instead of calling an AI.

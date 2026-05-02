# PDC Concert Ticketing - Run System Guide

This is the updated step-by-step guide to run the system properly on Windows using XAMPP.

## 1) Requirements

Make sure these are installed:

- XAMPP (Apache + MySQL)
- PHP (compatible with this project's Laravel version)
- Composer
- Node.js + npm

## 2) Project Path

The project should be located at:

`C:\xampp\htdocs\PDC-3-FINALPROJ-VER3`

## 3) First-Time Setup (One-Time Only)

Open a terminal in the project folder, then run:

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

## 4) Configure Database

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Create a database in phpMyAdmin (example: `concert_ticket_reservation_system`).
3. Update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=concert_ticket_reservation_system
DB_USERNAME=root
DB_PASSWORD=
```

## 5) Import Data / Run Migrations

Choose one:

- **Option A (recommended for fresh setup):**

```bash
php artisan migrate --seed
```

- **Option B (if you already have an SQL dump):**
  - Import the `.sql` file in phpMyAdmin
  - Do not run `migrate --seed` if the imported database is already complete

## 6) Storage Link (for images)

Run this once so uploaded images work properly:

```bash
php artisan storage:link
```

## 7) Run the System

Use **1 terminal** in the project folder:

```bash
php artisan serve
```

Open in your browser:

- `http://127.0.0.1:8000`

**Note:** Tailwind CSS is compiled at build time, no need for a development watcher.

## 8) Daily Startup (Every Work Session)

1. Start **Apache** + **MySQL** in XAMPP
2. In the project folder, run:
   - `php artisan serve`
3. Open `http://127.0.0.1:8000`

## 9) Quick Troubleshooting

- **CSS not loading**
  - Make sure Tailwind CSS is compiled in `resources/css/`
  - Check browser cache (Ctrl+Shift+Delete)
  - Verify `public/build/` exists with compiled assets
- **Blank page**
  - Check Laravel logs: `storage/logs/laravel.log`
  - Ensure `.env` database credentials are correct
  - Run `php artisan migrate` if needed

- **Database connection error**
  - Check the DB values in `.env`
  - Confirm MySQL is running in XAMPP
  - Run `php artisan config:clear`

- **Application key missing**
  - Run `php artisan key:generate`

- **Images not showing**
  - Run `php artisan storage:link`

- **Class/config not updating**
  - Run:

```bash
php artisan optimize:clear
```


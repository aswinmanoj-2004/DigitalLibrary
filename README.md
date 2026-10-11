# Digital Library — *Ready 2 Read*

A web-based digital library platform built with **PHP** and **MySQL**, where authors
publish and sell e-books, readers discover/buy/download and review them, and an
admin manages the whole catalog and community.

The public landing page (`index.php`) shows live library analytics — books by
category, trending titles, new arrivals, free-spotlight, and reader reviews — with a
modern glassmorphism UI and light/dark theming.

---

## ✨ Features

- **Public storefront** with animated intro loader, category charts, trending & new-arrival sections, and free-book spotlight
- **Author publishing** — upload manuscript PDFs + cover images, set free/paid pricing, manage own books
- **Reader experience** — browse/search, purchase, download, wishlist, rate & review, complaints, and an AI reading assistant
- **Admin dashboard** — approve/reject sellers & books, manage categories, moderate reviews, handle complaints & notifications, view stats
- **Light / dark mode** with persistent theme preference

---

## 👥 Roles

The app is split into three role-scoped areas, each with its own login/session flow.

| Role | Folder | Who | Can do |
|------|--------|-----|--------|
| **Admin** | [`admin/`](admin) | Platform operator | Manage users, sellers, books, categories, reviews, complaints, notifications, and reports; approve/reject/disable content |
| **Author / Seller** | [`author/`](author) | Book publishers | Register, upload & edit books (PDF + cover), set pricing, view sales/reports, respond to feedback and complaints |
| **Reader** | [`reader/`](reader) | End users | Register/login, browse & search the catalog, purchase/download books, maintain a wishlist, write reviews, file complaints, use the AI assistant |

The public home page is [`index.php`](index.php).

---

## 🧱 Tech Stack

- **PHP** (procedural + lightweight class layer, no framework)
- **MySQL / MariaDB**
- **WAMP** (Windows, Apache, MySQL, PHP) as the local server
- **Bootstrap**, **Tailwind CSS (CDN)**, **jQuery**, **Font Awesome / Material Symbols**
- **Chart/Morris** for dashboard analytics

### Project layout

```
DigitalLibrary/
├── index.php            # Public landing page
├── admin/               # Admin panel (auth, books, categories, users, complaints...)
├── author/              # Author / seller dashboard
├── reader/              # Reader storefront, purchase, wishlist, reviews
├── classes/             # Core PHP classes (see below)
│   ├── DataAccess.class.php   # DB query helper
│   ├── database.php           # PDO/DB bootstrap
│   ├── FileUpload.class.php   # Book PDF / cover-image upload handling
│   ├── FormAssist.class.php   # Form helpers
│   └── FormValidator.class.php# Input validation
├── config/
│   ├── autoload.php     # Constants (BASE_URL / BASE_PATH), session, requires
│   └── database.php     # DB credentials ($DB_HOST, $DB_NAME, $DB_USER, $DB_PASSWORD)
├── uploads/             # Uploaded book cover images (served by the app)
├── uploadpdf/           # Uploaded book PDF files
└── digitallibrary .sql  # Full database dump (schema + seed data)
```

---

## 🚀 Getting Started (Local Setup with WAMP)

### 1. Prerequisites
- **WAMP Server** installed (PHP 7.4+ and MySQL/MariaDB recommended)

### 2. Place the project
Copy this repository into the WAMP web root so the path becomes:

```
C:\wamp64\www\DigitalLibrary
```

### 3. Start the services
Launch the WAMP tray icon and make sure **Apache** and **MySQL** are both online (green).

### 4. Import the database
The schema and demo data live in **`digitallibrary .sql`** (note the space before the extension).

**Option A — phpMyAdmin**
1. Open http://localhost/phpmyadmin
2. **New** database named `digitallibrary` (collation `utf8_general_ci`)
3. Select it → **Import** tab → choose the `digitallibrary .sql` file → **Go**

**Option B — Command line**
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS digitallibrary;"
mysql -u root -p digitallibrary < "digitallibrary .sql"
```

The database contains 13 tables: `admin`, `authorregistration`, `books`, `category`,
`complaints`, `downloads`, `feedback`, `notifications`, `payments`, `purchases`,
`readerregistration`, `reviews`, `wishlist`.

### 5. Check the configuration

**Database credentials** — [`config/database.php`](config/database.php) (default WAMP values, no root password):
```php
$DB_HOST = "localhost";
$DB_NAME = "digitallibrary";
$DB_USER = "root";
$DB_PASSWORD = "";
```

**Paths** — [`config/autoload.php`](config/autoload.php) defines the project URLs/paths.
Adjust them if your install path or port differs:
```php
define("BASE_URL",  "http://localhost/DigitalLibrary/");
define("BASE_PATH", "C:/wamp64/www/DigitalLibrary/");
```

> ⚠️ `BASE_PATH` must point to the **actual folder** where the project is installed,
> otherwise the `require()` calls in `autoload.php` will fail.

### 6. Upload folders
The app writes files into two folders — make sure they exist and are writable by Apache:

- **`uploads/`** — book **cover images** referenced via the `CoverImage` field on `books`
- **`uploadpdf/`** — uploaded book **PDF** files

The dump ships with sample files in both folders so seed data renders correctly. Files
are stored with hashed/unique names (`<hash>_<random>.<ext>`).

### 7. Run it
Open the home page in your browser:

```
http://localhost/DigitalLibrary/
```

### Default login

| Role | URL | Credentials |
|------|-----|-------------|
| Admin | http://localhost/DigitalLibrary/admin/login.php | username `admin` · password `admin123` |
| Author | http://localhost/DigitalLibrary/author/login.php | register a new author account |
| Reader | http://localhost/DigitalLibrary/reader/readerLogin.php | register a new reader account |

> These are **development-only** credentials (MySQL `root` with no password). Change
> them before any real deployment.

---

## 📖 Typical Workflow

1. **Reader** registers and browses the catalog on the home page.
2. **Author** registers, uploads a book (PDF to `uploadpdf/`, cover to `uploads/`), and sets free/paid pricing.
3. **Admin** approves the new book/seller from the dashboard.
4. **Reader** purchases/downloads the book, adds it to the wishlist, and leaves a review.
5. **Admin / Author** handle reviews, complaints, and feedback.

---

## 🛠️ Troubleshooting

- **Blank page / `require(): Failed opening ... classes/...`** → `BASE_PATH` in `config/autoload.php` doesn't match your install folder.
- **`Connection failed` / `Access denied for user 'root'`** → verify MySQL is running and credentials in `config/database.php`.
- **Covers or PDFs not loading** → confirm the file exists in `uploads/` / `uploadpdf/` and the folder is writable.
- **Home page shows "No books available yet"** → the `digitallibrary .sql` import didn't run or the DB name doesn't match `digitallibrary`.

---

## 📄 Notes

This project is intended to run on a local WAMP environment as-is; the bundled database
dump contains sample/demo data. It is not configured for secure production use
(plaintext credentials, no HTTPS, no DB password).

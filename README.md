# Stayora - Modern Luxury Farmhouses & Villa Stays

Stayora is a PHP MVC web application for booking and listing luxury farmhouses, private villas, and vacation retreats.

---

## 🚀 Quick Start (Local Setup)

### 1. Prerequisites
- **XAMPP** (or Apache + MySQL + PHP 8.2+)
- **Composer** (optional, recommended)
- Required PHP Extensions: `mysqli`, `pdo_mysql`, `curl`, `json`, `mbstring`, `fileinfo`, `openssl`, `gd`

---

### 2. Fast Setup (1 Command)

Open your terminal in the project directory (`C:\xampp\htdocs\Stayora`) and run:

```bash
# Option A: With Composer
composer setup

# Option B: Directly with PHP
php setup.php
```

The setup script automatically:
1. Validates your PHP version and extensions.
2. Creates `.env` from `.env.example` if not present.
3. Ensures all required `assets/images/uploads/` folders exist.
4. Connects to MySQL, creates the `Stayora` database, and imports tables if missing.

---

### 3. Starting the Local Development Server

Choose either method below:

#### Method A: Built-in PHP Server (Recommended / Fast)
```bash
composer dev
# Or
php -S localhost:8000 router.php
```
👉 Open **[http://localhost:8000](http://localhost:8000)** in your browser.

#### Method B: XAMPP Apache
1. Open **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Project is located in `C:\xampp\htdocs\Stayora`.
3. Open **[http://localhost/Stayora/](http://localhost/Stayora/)** in your browser.

---

## ⚙️ Environment Configuration (`.env`)

Configuration settings are stored in `.env`:

```env
# Database Settings
DB_HOST=localhost
DB_PORT=3306
DB_NAME=Stayora
DB_USER=root
DB_PASS=

# Application Settings
APP_NAME="Stayora"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

---

## 🔑 Guest Portal Access & Login Credentials

### 👤 Customer / Guest Portal
* **Access URL:** `http://localhost/Stayora/login` (or `http://localhost:8000/login`)
* **Email:** `user@stayora.com`
* **Password:** `Admin@123`
* **Privileges:** Search verified farmhouses, booking requests, wishlist, real-time profile management, and account settings.

> **Note:** The Admin Panel and Owner Panel have been decommissioned for a clean ground-up redesign (front-end and back-end).

---

## 📁 Project Structure

```
Stayora/
├── app/
│   ├── Config/          # Database connection, mail, sms configs
│   ├── Controllers/     # MVC Controllers (Admin, Owner, User, Home, Farmhouses)
│   ├── Helpers/         # Helper functions (base_url, asset, url, redirect, seo)
│   ├── Middleware/      # Auth and role access control middleware
│   ├── Models/          # Database models & business queries
│   ├── Services/        # SMS, OTP, Email notifications
│   ├── Views/           # View templates and UI layouts
│   └── routes.php       # Application router
├── assets/
│   ├── css/             # Stylesheets (style.css - White & Blue Theme)
│   ├── images/          # Images, logos (Stayora logo), uploads
│   └── js/              # Client-side JavaScript
├── database/            # Database schema and migration scripts
├── .env.example         # Environment template
├── composer.json        # Dependencies & PSR-4 autoloading
├── index.php            # Main web entry point
├── router.php           # Local dev server router
└── setup.php            # Automated setup script
```
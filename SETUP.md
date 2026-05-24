# TuniCuisine — Setup & run guide

This document explains what you need installed and how to start the project on **Windows**.

---

## Requirements

| Requirement | Version / notes |
|-------------|-----------------|
| **PHP** | **8.4 or newer** (Symfony 8). PHP 8.2 from XAMPP is **not** supported. |
| **Composer** | Installed globally, **or** use the project’s `composer.phar` (downloaded by `setup.bat`). |
| **PHP extensions** | `ctype`, `iconv`, `intl`, `pdo_sqlite`, `openssl`, `curl`, `zip`, **`fileinfo`**, **`mbstring`** |
| **Database** | SQLite (file `var/data.db` — created automatically). No MySQL/PostgreSQL required for local dev. |
| **Browser** | Any modern browser (Chrome, Edge, Firefox). |

### Install PHP 8.4 (one time)

```powershell
winget install PHP.PHP.8.4
```

After install, enable extensions (one time):

```powershell
cd path\to\TuniCuisine
.\php.bat bin/enable-php-extensions.php
```

> **Important:** On this project, always use the helper scripts (`php.bat`, `console.bat`, `composer.bat`, `run-server.bat`). They point to PHP 8.4 from winget. Plain `php` in PowerShell may still be XAMPP 8.2.

---

## First-time setup

1. Open the project folder in File Explorer or terminal.
2. Copy environment file (if you don’t have `.env` yet):

   ```powershell
   copy .env.example .env
   ```

   On first run, Symfony may create `.env` from `.env.dev` automatically.

3. Run the automated setup (installs dependencies, runs migrations, loads demo data):

   **Double-click `setup.bat`**

   Or in PowerShell:

   ```powershell
   cd TuniCuisine
   .\setup.bat
   ```

   This will:
   - Install PHP 8.4 via winget if missing
   - Download `composer.phar` if needed
   - Run `composer install`
   - Create/update the SQLite database
   - Load demo recipes, regions, users, etc.

---

## Run the server (every day)

**Double-click `run-server.bat`**

Or:

```powershell
.\run-server.bat
```

Then open: **http://localhost:8000**

Stop the server with `Ctrl+C` in the terminal window.

---

## Helper scripts (Windows)

| Script | Purpose |
|--------|---------|
| `setup.bat` | First-time install: Composer, database, fixtures |
| `run-server.bat` | Start site at http://localhost:8000 |
| `console.bat` | Run Symfony commands, e.g. `.\console.bat cache:clear` |
| `composer.bat` | Run Composer, e.g. `.\composer.bat install` |
| `php.bat` | Run PHP 8.4 for this project |

### Useful console commands

```powershell
.\console.bat doctrine:migrations:migrate --no-interaction
.\console.bat doctrine:fixtures:load --no-interaction
.\console.bat cache:clear
```

---

## Demo accounts

After fixtures are loaded:

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@tunicuisine.local` | `admin123` |
| Member | `user@tunicuisine.local` | `user1234` |

---

## Troubleshooting

### “require PHP >= 8.4” but you have PHP 8.2

Your terminal is using XAMPP’s PHP. Use `.\run-server.bat` and `.\console.bat` instead of `php` / `symfony`.

### Invalid CSRF token on login

1. Restart `run-server.bat`.
2. Hard-refresh the login page (`Ctrl+F5`).
3. Try a private/incognito window.

The project loads CSRF protection via `assets/app.js`. Don’t disable JavaScript on the login page.

### “Unable to guess the MIME type” when uploading a recipe image

Enable PHP `fileinfo`:

```powershell
.\php.bat bin/enable-php-extensions.php
```

Restart the server and try again.

### Port 8000 already in use

Edit `run-server.bat` and change `localhost:8000` to another port (e.g. `localhost:8080`), then open that URL.

### Database / migration errors

```powershell
.\console.bat doctrine:migrations:migrate --no-interaction
```

If migrations still fail on SQLite:

```powershell
.\php.bat bin/patch-sqlite-schema.php
.\console.bat doctrine:migrations:migrate --no-interaction
```

### Recipe images

- Uploaded images are stored in `public/images/recipes/`.
- Default fixture photos: `mloukhia.jpg`, `couscous-royal.jpg`, `brik-a-l-oeuf.jpg`, `lablabi.jpg`, `makroudh.jpg`.
- If no image is found, the site uses `public/images/tunisian_cuisine_banner_1774909075250.png`.
- Optional: set `MAILER_DSN` in `.env.local` for email notifications.

---

## Project structure (essentials)

```
TuniCuisine/
  public/          Web root (index.php)
  src/             PHP code (controllers, entities, services)
  templates/       Twig HTML templates
  assets/          CSS, JavaScript (Asset Mapper)
  config/          Symfony configuration
  migrations/      Database migrations
  var/             Cache, logs, SQLite DB (generated locally)
  setup.bat        First-time setup
  run-server.bat   Start dev server
```

---

## Optional: email

Create `.env.local`:

```env
MAILER_DSN=smtp://user:pass@smtp.example.com:587
```

Without this, the app works; approve/decline emails are simply not sent.

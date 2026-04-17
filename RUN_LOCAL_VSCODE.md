# Run EditX Studio Locally in VS Code (Safe for Hosting)

This setup lets you test locally without changing live hosting behavior.

## 1) What was set up

- `login.php` now loads `config.local.php` only on `localhost` / `127.0.0.1`.
- Live hosting still uses `config.php`.
- Local database default is `editing`.

## 2) Local files used

- `config.local.php` (local-only)
- `setup_database.php` (creates local MySQL DB + admins table)

## 3) Start local server

Use any one method:

### Option A: XAMPP/WAMP (recommended)
1. Put project inside htdocs/www.
2. Start Apache + MySQL.
3. Open: `http://localhost/editing project/`

### Option B: PHP built-in server
1. Open terminal in project folder.
2. Run: `php -S localhost:8080`
3. Open: `http://localhost:8080/`

## 4) Setup local database

1. Open `http://localhost/editing project/setup_database.php`
2. It creates:
   - DB: `editing`
   - Table: `admins`
   - Default user: `admin` / `admin123`

If using built-in server path, open:
- `http://localhost:8080/setup_database.php`

## 5) Login locally

Open:
- `http://localhost/editing project/login.php`

Use:
- Username: `admin`
- Password: `admin123`

## 6) Deploy to hosting safely

Upload normal project files, but avoid uploading local-only files:
- Do NOT upload `config.local.php`
- Do NOT upload `RUN_LOCAL_VSCODE.md` (optional)

Live site will continue using `config.php` only.

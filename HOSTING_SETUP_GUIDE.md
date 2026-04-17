# EditX Studio - Complete Hosting Setup Guide
## For filemanager.ai & Similar Hosting Platforms

---

## 📋 Table of Contents
1. [Project Overview](#overview)
2. [Database Structure](#database-structure)
3. [Local Setup](#local-setup)
4. [Hosting on filemanager.ai](#hosting-setup)
5. [File Upload Guide](#file-upload)
6. [Testing & Verification](#testing)
7. [Troubleshooting](#troubleshooting)

---

## <a name="overview"></a>🎯 Project Overview

**EditX Studio** is a PHP-based video/photo editing service management platform with:
- ✅ Admin login system (MySQL)
- ✅ Booking management (JSON files)
- ✅ Contact messages (JSON files)
- ✅ Gallery & offers management (JSON files)
- ✅ Responsive design with Bootstrap 5

### Hybrid Database Architecture
- **MySQL:** Only `admins` table (for secure login)
- **JSON Files:** bookings, contacts, gallery, offers (for flexibility)

---

## <a name="database-structure"></a>💾 Database Structure

### MySQL Database: `editning`

Only ONE table in MySQL:

```sql
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Default Admin Credentials:**
- Username: `admin`
- Password: `admin123`

### JSON Files (in root directory)

| File | Purpose | Auto-created |
|------|---------|--------------|
| `bookings.json` | Store booking requests | Yes ✓ |
| `contact_messages.json` | Store contact form submissions | Yes ✓ |
| `gallery.json` | Gallery items (photos/videos) | Yes ✓ |
| `offers.json` | Service offers & pricing | Yes ✓ |

---

## <a name="local-setup"></a>🖥️ Local Setup (Development)

### Step 1: Setup Local XAMPP/WAMP

1. **Download & Install:**
   - [XAMPP](https://www.apachefriends.org/) OR
   - [WAMP](https://www.wampserver.com/)

2. **Start Services:**
   - Open XAMPP Control Panel
   - Click "Start" for Apache & MySQL
   
3. **Create Project Folder:**
   ```bash
   C:\xampp\htdocs\editing_project
   # or for WAMP
   C:\wamp\www\editing_project
   ```

4. **Copy All Project Files** into the folder

### Step 2: Setup Database Locally

**Option A: Using PHP Script (Recommended)**
1. Open browser: `http://localhost/editing_project/setup_database.php`
2. Follow the setup wizard
3. You'll see: "✅ Database setup completed successfully!"

**Option B: Using phpMyAdmin**
1. Open: `http://localhost/phpmyadmin`
2. Click SQL tab
3. Copy-paste contents of `hosting-setup.sql`
4. Click "Go"

### Step 3: Verify Setup

1. Open: `http://localhost/editing_project/login.php`
2. Enter credentials:
   - Username: `admin`
   - Password: `admin123`
3. Should see the Dashboard ✅

### Step 4: Initial JSON Files

When you first:
- Submit a booking → `bookings.json` is created
- Submit a contact form → `contact_messages.json` is created
- Upload gallery → `gallery.json` is updated
- Add offers → `offers.json` is updated

---

## <a name="hosting-setup"></a>🚀 Hosting on filemanager.ai

### Step 1: Create Account & Domain

1. Go to [filemanager.ai](https://filemanager.ai) (or similar hosting)
2. Sign up for an account
3. Create a domain (e.g., `yourname.infinityfreeapp.com`)

### Step 2: Access Control Panel

1. Login to hosting control panel
2. Locate "MySQL Database" or "Database Manager"

### Step 3: Create MySQL Database

**Path:** Control Panel → MySQL Databases → Create New Database

You'll get something like:
```
Database Name:    if0_123456_editning
DB Username:      if0_123456_admin
DB Password:      YourPassword123
DB Host:          sqlXXX.infinityfree.com
```

**⚠️ SAVE THESE 4 DETAILS - You need them for connection!**

### Step 4: Execute Database SQL

1. In Control Panel, click "phpMyAdmin"
2. Select your database from left panel
3. Click "SQL" tab
4. Paste contents from `hosting-setup.sql`
5. Click "Go/Execute"

✅ You should see: "Admins table created successfully"

### Step 5: Update PHP Connection File

The project uses db-functions.php for JSON (no DB connection needed for bookings/contacts).

**For admin login**, the system connects to MySQL in `login.php`. You might need to create a config file:

**Create file: `config.php`**
```php
<?php
// Hosting Database Configuration
define('DB_HOST', 'sqlXXX.infinityfree.com');      // Replace with your host
define('DB_USER', 'if0_123456_admin');              // Replace with your username
define('DB_PASS', 'YourPassword123');               // Replace with your password
define('DB_NAME', 'if0_123456_editning');           // Replace with your database

// For local development, use localhost
// define('DB_HOST', 'localhost');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_NAME', 'editing');
?>
```

Then update `login.php` to use this config (if it's not already using it).

### Step 6: Upload Project Files

**⚠️ Important Directory:**
- Upload all files into: `htdocs` or `public_html` folder
- NOT outside this folder

**Files to Upload:**
```
✓ All .php files
✓ All folders (style/, uploads/, images/)
✓ All .json files (initially empty or with sample data)
✓ config.php (new file with hosting credentials)
```

**Don't Upload:**
- ❌ setup_database.bat
- ❌ auto-sync.ps1
- ❌ quick-sync.bat
- ❌ start-sync.bat
- ❌ .git folder
- ❌ README.md (optional)

### Step 7: Set File Permissions

In File Manager, right-click and set permissions:
```
bookings.json       → 644 (readable & writable)
contact_messages.json → 644
gallery.json        → 644
offers.json         → 644
uploads/           → 755 (writable)
```

### Step 8: Test Website

1. Open: `https://yourname.infinityfreeapp.com/login.php`
2. Enter:
   - Username: `admin`
   - Password: `admin123`
3. Should see dashboard ✅

---

## <a name="file-upload"></a>📁 Complete File Upload Checklist

### Root Directory Files
```
✓ Home.php
✓ about.php
✓ book.php
✓ bookings_total.php
✓ contact.php
✓ ContectUs_total.php
✓ dashboard.php
✓ gallary.php
✓ gallery.json (empty array initially: [])
✓ index.php
✓ login.php
✓ logout.php
✓ manage_gallery.php
✓ manage_gallery_secure.php
✓ offer.php
✓ offers.json (empty array initially: [])
✓ pass.php
✓ test.php
✓ bookings.json (empty array initially: [])
✓ contact_messages.json (empty array initially: [])
✓ db-functions.php (NEW - for JSON handling)
✓ config.php (NEW - hosting connection details)
```

### Folders
```
✓ style/
   ├── about.css
   ├── book.css
   ├── bookings_total.css
   ├── contact.css
   ├── contactus_total.css
   ├── dashboard.css
   ├── gallary.css
   ├── Home.css
   ├── login.css
   ├── manage_gallery.css
   ├── offer.css
   ├── portal.css

✓ uploads/
   ├── photos/ (empty initially)
   ├── videos/ (empty initially)

✓ images/ (if any)
```

---

## <a name="testing"></a>✅ Testing & Verification

### Test 1: Admin Login
```
URL: https://yourname.infinityfreeapp.com/login.php
Username: admin
Password: admin123
Expected: See Dashboard with 4 stats cards
```

### Test 2: Booking Form
```
URL: https://yourname.infinityfreeapp.com/book.php
1. Fill all fields
2. Click "Submit Booking"
3. Expected: Success modal appears
4. Check: bookings.json updated (File Manager)
```

### Test 3: Contact Form
```
URL: https://yourname.infinityfreeapp.com/contact.php
1. Fill name, email, message
2. Click "Send"
3. Expected: "✅ Thank you! Your message has been sent."
4. Check: contact_messages.json updated (File Manager)
```

### Test 4: View Bookings (Admin)
```
URL: https://yourname.infinityfreeapp.com/bookings_total.php
1. Login with admin credentials first
2. Should see table with all bookings
3. Expected: Shows bookings from JSON file
```

### Test 5: View Messages (Admin)
```
URL: https://yourname.infinityfreeapp.com/ContectUs_total.php
1. Login with admin credentials first
2. Should see table with all contact messages
3. Expected: Shows messages from JSON file
```

---

## <a name="troubleshooting"></a>🔧 Troubleshooting

### Problem 1: "Connection failed: Access denied"
**Cause:** Wrong database credentials in login.php

**Solution:**
1. Double-check filemanager.ai gave you correct:
   - Database host (e.g., sqlXXX.infinityfree.com)
   - Database username
   - Database password
   - Database name
2. Update config.php with correct values
3. Make sure database name starts with prefix (e.g., `if0_123456_editning`)

### Problem 2: JSON Files Not Updating
**Cause:** File permissions issue

**Solution:**
1. Right-click on `bookings.json` in File Manager
2. Select "Permissions"
3. Set to `644` (read & write for owner)
4. Repeat for all .json files

### Problem 3: Admin Login Not Working
**Cause:** Database not set up or wrong password

**Solution:**
1. Open phpMyAdmin in hosting panel
2. Check if `admins` table exists
3. Check if admin user exists with password hash
4. If not, re-run `hosting-setup.sql`
5. Default password hash: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi` = `admin123`

### Problem 4: Gallery/Offers Not Showing
**Cause:** gallery.json or offers.json not present

**Solution:**
1. Create empty files via File Manager:
   - `gallery.json` → paste: `[]`
   - `offers.json` → paste: `[]`
2. Set permissions to 644
3. Try uploading gallery/offers again

### Problem 5: Bookings/Contacts Form Not Submitting
**Cause:** JSON files not writable

**Solution:**
1. Ensure bookings.json and contact_messages.json exist
2. Set permissions to 644
3. Check server error logs (File Manager → Logs)
4. Verify db-functions.php is in root directory

### Problem 6: White Blank Page
**Cause:** PHP error (usually database connection)

**Solution:**
1. Check File Manager → Error Logs
2. Look for PHP errors
3. Verify all required files are uploaded
4. Check database credentials in login.php/config.php

---

## 📞 Need Help?

### Common Links for filemanager.ai
- **Control Panel:** https://filemanager.ai/dashboard
- **phpMyAdmin:** Usually: https://filemanager.ai/phpmyadmin
- **File Manager:** https://filemanager.ai/file-manager

### Credentials to Keep Handy
```
Hosting Username:   [YOUR ACCOUNT EMAIL]
Hosting Password:   [YOUR ACCOUNT PASSWORD]

Database Host:      sqlXXX.infinityfree.com (from panel)
Database Name:      if0_123456_editning (from panel)
DB Username:        if0_123456_admin (from panel)
DB Password:        [Created in panel]

Admin Login:        admin / admin123
FTP/SSH:            Check your hosting provider's panel
```

---

## 🎉 Success Checklist

- ✅ MySQL database `editning` created with `admins` table
- ✅ Default admin user created (admin/admin123)
- ✅ All PHP files uploaded to htdocs/public_html
- ✅ JSON files created (bookings.json, contact_messages.json, gallery.json, offers.json)
- ✅ File permissions set to 644 for .json files
- ✅ Admin login works
- ✅ Booking form submits successfully
- ✅ Contact form submits successfully
- ✅ Admin can view bookings and messages

**🚀 Your EditX Studio is now LIVE!**

---

## 📝 Support Notes

**Remember:**
- Always use HTTPS for your hosting (filemanager.ai provides SSL)
- Keep your admin password secure
- Backup JSON files regularly
- Monitor disk space usage
- Clear old bookings/messages periodically from JSON files

**For Questions:**
- Check `hosting-setup.sql` for database structure
- Review `db-functions.php` for JSON handling code
- Check `login.php` for authentication logic
- See `config.php` for connection settings


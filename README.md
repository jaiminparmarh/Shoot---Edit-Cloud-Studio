# EditX Studio - Setup Instructions

## Database Setup

1. **Import the database schema:**
   ```sql
   mysql -u root -p < database_setup.sql
   ```

2. **Default Admin Login:**
   - Username: `admin`
   - Password: `admin123`

## Project Structure

### Fixed Issues:
✅ **Database Connection** - All files now use consistent database name `editing`
✅ **Database Schema** - Complete SQL schema created with proper tables
✅ **AOS Animations** - Added proper AOS library for smooth animations
✅ **File Paths** - Fixed escaped slashes in gallery.json
✅ **Session Security** - Added authentication checks to admin pages
✅ **Error Handling** - Improved validation and error messages

### Files Overview:
- `index.php` - Main landing page (portal)
- `Home.php` - Services page with portfolio
- `book.php` - Service booking form
- `contact.php` - Contact form
- `gallery.php` - Photo/video gallery display
- `login.php` - Admin login
- `dashboard.php` - Admin dashboard (secured)
- `manage_gallery_secure.php` - Secure gallery management
- `database_setup.sql` - Complete database schema

## Features:
- Responsive design with Bootstrap 5
- Smooth animations with AOS library
- Admin dashboard with statistics
- Gallery management system
- Booking and contact forms
- Secure session management

## Quick Start:
1. Set up local server (XAMPP/WAMP)
2. Import database schema
3. Configure database credentials if needed
4. Access: `http://localhost/editing project/`
5. Admin panel: `http://localhost/editing project/login.php`

## Security Notes:
- All admin pages require authentication
- Input validation and sanitization
- Prepared statements for database queries
- Session-based access control

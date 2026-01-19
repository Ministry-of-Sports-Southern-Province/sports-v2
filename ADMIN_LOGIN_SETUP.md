# Admin Login Setup Guide

## Database Setup

1. **Run the admin table creation script:**

   ```bash
   # Import the admin.sql file into your database
   mysql -u root -p sports_db < sql/admin.sql
   ```

   Or manually execute the SQL in phpMyAdmin/MySQL Workbench.

## Default Admin Credentials

**Username:** `admin`  
**Password:** `admin123`

> ⚠️ **Important:** Change the default password after first login!

## Features

### ✅ Session Management

- Secure PHP sessions
- Auto-logout on session expiry
- Protection against unauthorized access

### ✅ Protected Pages

All pages except `login.php` require authentication:

- Dashboard
- Register Club
- Edit Club
- Reorganizations
- Summary
- Reports

### ✅ Admin Functions

Located in `includes/auth.php`:

- `isLoggedIn()` - Check if user is authenticated
- `requireLogin()` - Redirect to login if not authenticated
- `getCurrentAdmin()` - Get current admin user info
- `setAdminSession()` - Set admin session data
- `clearAdminSession()` - Logout and clear session

## Files Created

### Database

- `sql/admin.sql` - Admin users table schema

### Authentication

- `includes/auth.php` - Session management functions
- `login.php` - Login page
- `api/login.php` - Login handler
- `api/logout.php` - Logout handler

### Modified Files

- `includes/header.php` - Added logout button and session check

## Password Management

### Creating New Admin Users

```php
<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$pdo = getDBConnection();

$username = 'newadmin';
$password = 'secure_password_here';
$full_name = 'Admin Full Name';
$email = 'admin@example.com';

$hashedPassword = hashPassword($password);

$stmt = $pdo->prepare("INSERT INTO admin_users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
$stmt->execute([$username, $hashedPassword, $full_name, $email]);

echo "Admin user created successfully!";
?>
```

### Changing Password

```php
<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$pdo = getDBConnection();

$username = 'admin';
$newPassword = 'your_new_secure_password';

$hashedPassword = hashPassword($newPassword);

$stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE username = ?");
$stmt->execute([$hashedPassword, $username]);

echo "Password updated successfully!";
?>
```

## Security Features

1. **Password Hashing:** Uses PHP's `password_hash()` with bcrypt
2. **Session Security:** PHP sessions with proper configuration
3. **SQL Injection Prevention:** PDO prepared statements
4. **XSS Protection:** `htmlspecialchars()` on user output
5. **Access Control:** All pages require authentication

## Troubleshooting

### Cannot Login

1. Check database connection in `config/database.php`
2. Verify admin_users table exists
3. Check if default admin user was created
4. Clear browser cookies and try again

### Session Issues

1. Ensure PHP session directory is writable
2. Check `session.save_path` in php.ini
3. Verify cookies are enabled in browser

### Forgot Password

Since there's no password reset feature yet, directly update the database:

```sql
-- Reset admin password to 'admin123'
UPDATE admin_users
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE username = 'admin';
```

## Next Steps (Optional Enhancements)

- [ ] Add password change page
- [ ] Add "Remember Me" functionality
- [ ] Add password reset via email
- [ ] Add user management page (add/edit/delete admins)
- [ ] Add activity logging
- [ ] Add two-factor authentication
- [ ] Add password strength requirements
- [ ] Add account lockout after failed attempts

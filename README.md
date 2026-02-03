# Sports Club Management System v2.0

A modern sports club management system for the Department of Sports Southern Province (දකුණු පළාත් ක්‍රීඩා දෙපාර්තමේන්තුව) built with clean architecture, proper database normalization, and multilingual support.

## Features

### Core Functionality

- ✅ Club registration with comprehensive information capture
- ✅ Automatic registration number generation (දපස/ක්‍රිඩා/{district}/{number})
- ✅ Real-time registration number uniqueness validation
- ✅ Dual date entry options (current date or manual past date)
- ✅ Flexible equipment tracking system
- ✅ Searchable autocomplete for locations with on-the-fly creation
- ✅ Dashboard with search and filter capabilities

### Technical Features

- ✅ **Offline Support** - No CDN dependencies, works without internet
- ✅ Multilingual support (English, Sinhala, Tamil) - Frontend only
- ✅ UTF-8 support for Sinhala and Tamil characters
- ✅ Properly normalized database (7 tables, 3NF compliant)
- ✅ Tom Select for enhanced dropdowns with search
- ✅ Tailwind CSS for clean government-style UI
- ✅ PDO with prepared statements for security
- ✅ Transaction support for data integrity
- ✅ Inline validation with translated error messages

## Technology Stack

- **Frontend**: HTML5, Tailwind CSS, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Libraries**:
  - Chart.js 4.4.1 (statistics and charts)
  - Tom Select 2.3.1 (searchable dropdowns)
  - Tailwind CSS 3.x (styling)

## Database Structure

### 7 Normalized Tables

1. **districts** - District master data (Galle, Matara, Hambantota)
2. **divisions** - Divisional Secretariat data (linked to districts)
3. **grama_niladhari_divisions** - GN Divisions (linked to divisions)
4. **clubs** - Main club information with chairman/secretary details
5. **equipment_types** - Equipment type master (standard + custom)
6. **club_equipment** - Junction table for club equipment with quantities
7. **club_reorganizations** - Track reorganization dates per club

## Installation Instructions

### Prerequisites

- WAMP/XAMPP/LAMP server
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser with JavaScript enabled

### Step 1: Setup Database

1. Open phpMyAdmin or MySQL command line
2. Create a new database:

   ```sql
   CREATE DATABASE sports_club_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Import the schema:

   ```bash
   mysql -u root -p sports_club_system < sql/schema.sql
   ```

4. Import the seed data:
   ```bash
   mysql -u root -p sports_club_system < sql/seed.sql
   ```

### Step 2: Configure Database Connection

Edit `config/database.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sports_club_system');
define('DB_USER', 'root');          // Change if needed
define('DB_PASS', '');               // Change if needed
```

### Step 3: Download Vendor Libraries (Offline Support)

The system requires local copies of third-party libraries for offline operation.

**Quick Setup:**
```bat
download-vendors.bat
```

This downloads:
- Chart.js v4.4.1 (for statistics charts)
- Tom Select v2.3.1 (for searchable dropdowns)

See `assets/VENDOR_SETUP.md` for manual download instructions.

### Step 4: Build Tailwind CSS (Standalone CLI)

Build Tailwind using the **standalone CLI** (no npm or Node.js required):

1. **Download the CLI** from [Tailwind CSS releases](https://github.com/tailwindlabs/tailwindcss/releases/latest):
   - Windows 64-bit: `tailwindcss-windows-x64.exe`
   - **Note:** This project uses `tailwind.config.js` (v3 style). If the latest release is v4, download a **v3.x** standalone build from an older release (e.g. [v3.4.16](https://github.com/tailwindlabs/tailwindcss/releases/tag/v3.4.16)).
   - Place the exe in the project `bin\` folder, optionally rename to `tailwindcss.exe`.

2. **Build CSS** (production, minified):

   ```bat
   build-css.bat
   ```

   Or run the CLI directly:

   ```bat
   bin\tailwindcss-windows-x64.exe -i assets\css\input.css -o assets\css\output.css --minify -c tailwind.config.js
   ```

3. **Watch mode** (rebuild on file changes):
   ```bat
   build-css-watch.bat
   ```

This generates `assets/css/output.css` required for the system to work.

### Step 5: Access the System

1. Place the `sports-v2` folder in your web server's document root:
   - WAMP: `c:\wamp64\www\sports-v2`
   - XAMPP: `c:\xampp\htdocs\sports-v2`

2. Access the system in your browser:
   - Dashboard: `http://localhost/sports-v2/public/dashboard.php`
   - Registration: `http://localhost/sports-v2/public/register.php`

## Usage Guide

### Registering a New Club

1. Navigate to **Register Club** page
2. **Registration Number**:
   - Select district (prefix auto-updates to දපස/ක්‍රිඩා/{ග|ම|හ}/)
   - Enter manual number (digits only)
   - System validates uniqueness in real-time
3. **Location Information**:
   - Type at least 3 characters to search districts/divisions/GN divisions
   - Can add new entries on-the-fly (saved immediately)
   - District selection filters divisions
   - Division selection filters GN divisions

4. **Chairman & Secretary Information**:
   - Enter name, address, and 10-digit phone number
   - Phone validated automatically

5. **Equipment** (Optional):
   - Check standard equipment and enter quantities (min: 1)
   - Use "Add Other Equipment" for custom items
   - Custom equipment supports autocomplete

6. **Registration Date**:
   - Choose "Use Current Date" (default) OR
   - Choose "Enter Past Date" for backdated registrations
   - Manual date cannot be in the future

7. Click **Register Club** to save

### Viewing Clubs

1. Navigate to **Dashboard**
2. Use search box to find clubs by name, reg number, or chairman
3. Filter by district
4. Click "View Details" to see full club information

### Language Switching

- Click language buttons in top right (EN / සිං / தமிழ்)
- Preference saved in browser
- Only UI text changes - data remains in original language

## Validation Rules

### Registration Number

- Format: `දපස/ක්‍රිඩා/{ග|ම|හ}/{digits only}`
- Must be unique across all clubs
- Real-time validation with visual feedback

### Phone Numbers

- Exactly 10 digits
- Numeric only
- Required for both chairman and secretary

### Dates

- Manual registration date cannot be in future
- Format: YYYY-MM-DD

### Equipment

- Quantity must be at least 1 when selected
- Optional (clubs can register with zero equipment)

## Project Structure

```
sports-v2/
├── api/                        # Backend API endpoints
│   ├── clubs.php              # Club registration
│   ├── clubs-list.php         # Club listing with filters
│   ├── locations.php          # Locations CRUD
│   ├── equipment-types.php    # Equipment types CRUD
│   └── validate-reg-number.php # Registration number validation
├── assets/
│   ├── css/                   # Custom styles (if needed)
│   ├── js/
│   │   ├── i18n.js           # Internationalization module
│   │   ├── register.js       # Registration form logic
│   │   └── dashboard.js      # Dashboard logic
│   └── lang/
│       ├── en.json           # English translations
│       ├── si.json           # Sinhala translations
│       └── ta.json           # Tamil translations
├── config/
│   └── database.php          # Database connection & utilities
├── includes/
│   ├── header.php            # Common header
│   └── footer.php            # Common footer
├── public/
│   ├── register.html         # Club registration form
│   ├── dashboard.html        # Clubs dashboard
│   └── club-details.html     # Club details view (TODO)
├── sql/
│   ├── schema.sql            # Database schema
│   └── seed.sql              # Seed data
└── README.md                 # This file
```

## Key Improvements Over Old System

### Database Design

- ❌ **Old**: 2 poorly normalized tables with redundant data
- ✅ **New**: 7 properly normalized tables (3NF compliant)
- ❌ **Old**: Equipment as 8 separate columns in main table
- ✅ **New**: Flexible equipment tracking with junction table
- ❌ **Old**: Contact info mixed in single text field
- ✅ **New**: Separate structured fields for name, address, phone
- ❌ **Old**: No foreign key constraints
- ✅ **New**: Proper foreign keys with CASCADE rules

### User Experience

- ❌ **Old**: Manual data entry with no validation
- ✅ **New**: Searchable autocomplete with 3-char minimum
- ❌ **Old**: No uniqueness checking
- ✅ **New**: Real-time validation with visual feedback
- ❌ **Old**: Sinhala-only interface
- ✅ **New**: Multilingual support (EN/SI/TA)
- ❌ **Old**: Fixed equipment list
- ✅ **New**: Add custom equipment on-the-fly

### Code Quality

- ❌ **Old**: Mixed PHP/HTML, no structure
- ✅ **New**: Separated concerns (API/Frontend/Config)
- ❌ **Old**: No input validation
- ✅ **New**: Server-side + client-side validation
- ❌ **Old**: No transaction support
- ✅ **New**: PDO transactions for data integrity
- ❌ **Old**: SQL injection risks
- ✅ **New**: Prepared statements throughout

## Future Enhancements

- [ ] Club details page with edit functionality
- [ ] Reorganization tracking interface
- [ ] Equipment inventory reports
- [ ] User authentication and authorization
- [ ] Data export (CSV/PDF)
- [ ] Advanced search with multiple filters
- [ ] Activity audit log
- [ ] Email notifications
- [ ] Mobile responsive optimizations
- [ ] Print-friendly views

## Support

For issues or questions, contact the Department of Sports Southern Province IT team.

## License

Proprietary - Department of Sports Southern Province, Sri Lanka

---

**Version**: 2.0  
**Last Updated**: January 5, 2026  
**Status**: Initial Release - Registration & Dashboard Complete

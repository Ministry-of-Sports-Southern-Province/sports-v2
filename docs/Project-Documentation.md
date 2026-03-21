<div align="center">

# Project Documentation

# Sports Club Management System (SCMS)

**Version:** 2.0  
**Date:** March 2026  
**Confidentiality:** Internal / Administrator Use Only

</div>

<br><br>

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Architecture & Technology Stack](#2-architecture--technology-stack)
3. [System Requirements](#3-system-requirements)
4. [Directory Structure](#4-directory-structure)
5. [Database Architecture](#5-database-architecture)
6. [Installation & Deployment](#6-installation--deployment)
7. [User Roles & Access Levels](#7-user-roles--access-levels)
8. [Core Modules & Workflows](#8-core-modules--workflows)
9. [Multilingual (i18n) Configuration](#9-multilingual-i18n-configuration)
10. [Offline Capabilities](#10-offline-capabilities)

---

## 1. Project Overview

The Sports Club Management System (SCMS) is a centralized web application designed to manage, track, and report on sports clubs across various regional districts and divisions. It automates the registration numbering process, tracks equipment allocations, and maintains rigorous historical records of club reorganizations and status renewals.

**Key Features:**

- Automated and localized registration number generation.
- Real-time district, division, and Grama Niladhari (GN) mapping.
- Comprehensive tracking of physical sports equipment.
- Historical logging of club reorganizations.
- Advanced administrative dashboard with Chart.js analytics.
- PDF and Excel export capabilities.
- Fully offline-capable frontend libraries.

## 2. Architecture & Technology Stack

The project follows a standard client-server architecture using a monolithic PHP approach with AJAX-driven dynamic interfaces.

- **Backend:** PHP (Native / Procedural with PDO for Database Operations)
- **Database:** MySQL / MariaDB
- **Frontend Framework:** HTML5, CSS3, Tailwind CSS (via local build)
- **JavaScript Libraries:**
  - _Tom Select_ (Advanced select dropdowns)
  - _Chart.js_ (Data visualization)
  - _SweetAlert2_ (Interactive modals and alerts)
- **Exporting Modules:** FPDF/TCPDF (for PDF exports), CSV generation headers.

## 3. System Requirements

### 3.1. Local Development

- **OS:** Windows / Linux / macOS
- **Environment:** WAMP, XAMPP, or manual Apache/Nginx stack
- **PHP:** Version 8.0 or higher (Extensions: `pdo_mysql`, `mbstring`, `json`)
- **MariaDB/MySQL:** Version 10.4+ / 8.0+

### 3.2. Production Server

- Standard Linux shared hosting or VPS (cPanel/Plesk).
- Valid SSL Certificate (HTTPS) for secure login transmissions.

## 4. Directory Structure

```text
sports-v2/
├── api/                  # Backend endpoints for AJAX requests
├── assets/               # Static assets (CSS, JS, Fonts, Images, Vendor libs)
├── config/               # System configurations (Database connections)
├── db/                   # Database schemas and seed files
├── docs/                 # Project documentation
├── includes/             # Reusable PHP components (Header, Footer, Auth)
├── lang/                 # Translation JSON files (en, si, ta)
├── public/               # Core application views (Dashboard, reports, editing)
├── sql/                  # Active database migration files
└── tests/                # Integration and unit tests
```

## 5. Database Architecture

The system relies on a highly relational schema. Key tables include:

- `users`: Stores administrator credentials securely (hashed passwords).
- `clubs`: The primary entity table storing club names, reg numbers, GN divisions, and contact info.
- `club_reorganizations`: A one-to-many historical table tracking application renewals and committee changes.
- `club_equipment`: A one-to-many mapping of equipment allocated to specific clubs.
- `equipment_types`: A standardized lookup table for available equipment.
- `locations` / `districts` / `divisions`: Hierarchical structure tables mapping the regional demographics.

_(Check `sql/schema.sql` for exact column definitions and foreign key constraints)._

## 6. Installation & Deployment

### 6.1. Local Setup (WAMP/XAMPP)

1. Clone or copy the project to your `www` or `htdocs` directory.
2. Open phpMyAdmin and create a database named `scms` (or `sports_db`).
3. Import the initial database schema from `db/scms.sql`.
4. Update `config/database.php` with your local credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'scms');
   ```
5. Navigate to `http://localhost/sports-v2/login.php` to access the system.

### 6.2. Production Deployment

Refer to `PLESK_DEPLOYMENT_GUIDE.md` for specific hosting setup. Always ensure the `config/database.php` is restricted from public access and directory indexing is disabled on the server.

## 7. User Roles & Access Levels

1. **System Administrator:**
   Fully authenticated user. Has permission to view dashboards, read/write/edit/delete any club, manage equipment, run exports, and access personal identifying information (PII) of club officials.
2. **Public User (Guest):**
   Unauthenticated user. Restricted entirely to the `public-clubs.php` view. Can only view the registry of approved clubs and basic geographic data.

## 8. Core Modules & Workflows

### 8.1. Club Registration

The registration engine (`register.php`) features an automated numbering system. Based on the selected District and Division, a real-time AJAX call checks the DB and determines the next sequence number (e.g., `DEP/SPO/COL/2026/015`).

### 8.2. Reorganizations (Renewals)

Instead of overwriting a club's active status, the system enforces a _Reorganization_ model (`reorganize-club.php`). Every time a club renews its registration, a new historical record is generated, capturing the active date ranges and preserving the structural history of the club.

### 8.3. Reporting & Exports

The `reports.php` module queries grouped aggregate data (e.g., total clubs per division, equipment counts). Results are instantly rendered via _Chart.js_.
The Export engine uses specialized headers to output `.csv` or formatted `.pdf` files directly to the user's browser without storing temporary files on the server.

## 9. Multilingual (i18n) Configuration

The UI is fully translated into three languages: English (`en.json`), Sinhala (`si.json`), and Tamil (`ta.json`).

- Translations are managed in JavaScript via `assets/js/i18n.js`.
- The current user language is persisted in `localStorage`.
- To add a new property, append the key-value pair across all three JSON files in the `lang/` directory.

## 10. Offline Capabilities

To ensure operability in areas with poor internet connectivity (or entirely internal offline intranets), **all assets are stored locally**.

- **Tailwind:** Compiled locally via `build-css.bat`.
- **Fonts & Icons:** Stored within `assets/fonts/`.
- **JS Vendors:** Essential plugins (Tom Select, Chart.js) reside in `assets/js/vendor/`.
  No external Content Delivery Networks (CDNs) are required for the system to function at 100% capacity.

# Sports Club Management System (SCMS)

## User Manual

**Version:** 2.0  
**Date:** March 2026

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Getting Started & Access](#2-getting-started--access)
3. [User Roles & Permissions](#3-user-roles--permissions)
4. [Core Workflows](#4-core-workflows)
   - [4.1. Club Registration](#41-club-registration)
   - [4.2. Reorganization & Status Renewal](#42-reorganization--status-renewal)
   - [4.3. Equipment Management](#43-equipment-management)
5. [Dashboards & Reporting](#5-dashboards--reporting)
6. [Exports and Printing](#6-exports-and-printing)
7. [System Administration](#7-system-administration)

---

## 1. Introduction

The Sports Club Management System (SCMS) is a comprehensive web-based platform designed to centralize and manage the registration, tracking, and reporting of sports clubs across different districts and divisions. It offers advanced features like offline support, multilingual interfaces, automated registration numbering, and detailed analytical reports.

## 2. Getting Started & Access

Access the application via your standard web browser. The system is fully responsive, meaning it works on desktops, tablets, and smartphones.

- **Languages:** Switch between English, Sinhala, and Tamil using the language selector in the top navigation bar.
- **Offline Mode:** Once loaded, many components of the system continue functioning offline, syncing configurations securely.

## 3. User Roles & Permissions

- **Administrator (Logged In):** Has full access to the system. Can create, edit, and archive clubs. Can allocate equipment, update reorganization histories, access personal identifying information of club officials, view overarching Dashboards, and generate detailed reports.
- **Public Viewer (Guest):** Has read-only access. Can browse the "Public Directory" of clubs, view basic profiles (location, active status, registration dates), but cannot view personal contact info or internal system statistics.

## 4. Core Workflows

### 4.1. Club Registration

1. Navigate to **Clubs -> Register New**.
2. **Registration Number Generation:** The system auto-generates a standardized registration number based on location (e.g., `දපස/ක්‍රිඩා/{district}/{year}/{number}`).
3. **Data Entry:** Provide Club Name, District, Division, and GN Division.
4. **Officials:** Input Chairman and Secretary details (Names, NICs, Phone numbers).
5. **Equipment:** Pre-allocate any government-granted sports equipment immediately during registration.
6. **Submit:** Validate and save. The system prevents duplicates based on contact details and club names in the exact region.

### 4.2. Reorganization & Status Renewal

Clubs must periodically renew their status. SCMS tracks this via the _Reorganization_ module to preserve history.

1. Navigate to **Clubs -> Reorganizations**.
2. Select a Club.
3. Add a new reorganization entry, defining the new term dates and any structural changes.
4. The system retains the chronological history of all past reorganizations.

### 4.3. Equipment Management

Keep track of physical assets allocated to each club.

1. Visit the **Equipment Allocation** section within a specific club's profile.
2. Add new items from the predefined system list or define custom items.
3. Track quantities and distribution dates.

## 5. Dashboards & Reporting

SCMS provides real-time data visualization through Interactive Charts.

- **Main Dashboard:** Shows total registered clubs, active reorganizations, and geographical spread.
- **Reporting Module:** Accessed via the sidebar, allows generating customized breakdown reports based on specific dates, districts, or status variables.

## 6. Exports and Printing

Any list view or report can be extracted from the system for physical filing.

- **PDF Export:** Generates an official, highly formatted document.
- **Excel/CSV Export:** Downloads raw data for spreadsheet processing.
- **Print View:** Optimizes the screen for physical direct printing (hides menus and footers).

## 7. System Administration

_(Admin Only)_

- Manage user accounts, reset passwords, and configure overarching district/division locational structures if governmental boundaries change.
- Configure default dropdown variables like generic equipment types and local languages.

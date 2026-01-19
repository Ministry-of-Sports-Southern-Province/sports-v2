# PHP Component Usage Guide

## Overview

This document explains how to use the reusable header and footer PHP components for the Sports Club Management System.

## Component Structure

### Header Component (`includes/header.php`)

The header component provides a consistent navigation bar, language switcher, and page structure across all pages.

### Footer Component (`includes/footer.php`)

The footer component provides consistent branding and script includes.

## How to Use

### Basic Usage

```php
<?php
// Page configuration
$pageTitle = 'page.dashboard_title';      // i18n key for page title
$pageHeading = 'page.dashboard_title';    // i18n key for page heading
$activePage = 'dashboard';                // Current page for navigation highlighting

// Optional: Custom CSS styles
$customStyles = '
    .custom-class {
        color: blue;
    }
';

// Optional: Additional CSS/JS links
$additionalLinks = [
    '<link href="path/to/custom.css" rel="stylesheet">',
    '<script src="path/to/custom.js"></script>'
];

// Include header
include '../includes/header.php';
?>

<!-- Your page content here -->
<main>
    <!-- Content -->
</main>

<?php
// Optional: Additional scripts
$scripts = [
    '../assets/js/your-script.js'
];

// Include footer
include '../includes/footer.php';
?>
```

### Configuration Options

#### Header Variables

| Variable           | Type   | Required | Description                                                                     |
| ------------------ | ------ | -------- | ------------------------------------------------------------------------------- |
| `$pageTitle`       | string | No       | i18n key for the browser title (defaults to 'page.dashboard_title')             |
| `$pageHeading`     | string | No       | i18n key for the page heading (defaults to 'page.dashboard_title')              |
| `$activePage`      | string | No       | Current page identifier for navigation highlighting (defaults to 'dashboard')   |
| `$customStyles`    | string | No       | Additional CSS styles to include in the page (defaults to empty)                |
| `$additionalLinks` | array  | No       | Array of HTML link/script tags to include in the head (defaults to empty array) |

#### Active Page Options

The `$activePage` variable can be set to:

- `'dashboard'` - Dashboard page
- `'register'` - Registration page
- `'club-details'` - Club details page

This highlights the corresponding navigation item.

#### Footer Variables

| Variable   | Type  | Required | Description                                                         |
| ---------- | ----- | -------- | ------------------------------------------------------------------- |
| `$scripts` | array | No       | Array of JavaScript file paths to include (defaults to empty array) |

### Examples

#### Simple Page (No Custom Styling)

```php
<?php
$pageTitle = 'page.my_page';
$pageHeading = 'page.my_page';
$activePage = 'dashboard';

include '../includes/header.php';
?>

<main>
    <h2>My Content</h2>
</main>

<?php
include '../includes/footer.php';
?>
```

#### Page with Custom Styles

```php
<?php
$pageTitle = 'page.club_details_title';
$pageHeading = 'page.club_details_title';
$activePage = 'club-details';

$customStyles = '
    .detail-card {
        background: white;
        border-radius: 0.5rem;
        padding: 1.5rem;
    }
';

include '../includes/header.php';
?>

<main>
    <div class="detail-card">
        <!-- Content -->
    </div>
</main>

<?php
include '../includes/footer.php';
?>
```

#### Page with Additional Libraries

```php
<?php
$pageTitle = 'page.register_title';
$pageHeading = 'page.register_title';
$activePage = 'register';

$additionalLinks = [
    '<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">'
];

include '../includes/header.php';
?>

<main>
    <!-- Form content -->
</main>

<?php
$scripts = [
    'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js',
    '../assets/js/register.js'
];

include '../includes/footer.php';
?>
```

## Features

### Automatic Features

- **Language Switcher**: Automatically included in header
- **Responsive Navigation**: Mobile-friendly navigation bar
- **i18n Support**: All text uses i18n keys for multi-language support
- **Dynamic Year**: Footer copyright year updates automatically
- **Consistent Styling**: Government website theme applied consistently

### Built-in Scripts

- `i18n.js` - Always loaded automatically by the footer
- Custom scripts can be added via the `$scripts` array

## File Conversion Examples

### Converting HTML to PHP

Original HTML structure:

```html
<!DOCTYPE html>
<html>
  <head>
    <title>My Page</title>
    <style>
      /* styles */
    </style>
  </head>
  <body>
    <header>
      <!-- header content -->
    </header>

    <main>
      <!-- page content -->
    </main>

    <footer>
      <!-- footer content -->
    </footer>

    <script src="script.js"></script>
  </body>
</html>
```

Converted PHP structure:

```php
<?php
$pageTitle = 'page.my_title';
$pageHeading = 'page.my_title';
$activePage = 'dashboard';

$customStyles = '
    /* styles from original file */
';

include '../includes/header.php';
?>

<main>
    <!-- page content from original file -->
</main>

<?php
$scripts = ['../assets/js/script.js'];
include '../includes/footer.php';
?>
```

## Benefits

1. **DRY Principle**: Don't Repeat Yourself - header and footer code is written once
2. **Easy Maintenance**: Update navigation or footer in one place, affects all pages
3. **Consistency**: Ensures all pages have the same look and feel
4. **Flexibility**: Easy to customize per-page while maintaining consistency
5. **Scalability**: Adding new pages is quick and straightforward

## Best Practices

1. Always set `$pageTitle`, `$pageHeading`, and `$activePage` before including header
2. Place custom styles in `$customStyles` rather than inline in the page
3. Use the `$scripts` array for JavaScript files rather than manual script tags
4. Keep navigation links updated in `header.php` when adding new pages
5. Use i18n keys for all user-facing text

## File Locations

- Header component: `includes/header.php`
- Footer component: `includes/footer.php`
- Example pages:
  - `public/dashboard.php`
  - `public/register.php`
  - `public/club-details.php`

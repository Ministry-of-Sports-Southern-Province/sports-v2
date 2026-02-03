# Vendor Libraries Setup (Offline Support)

This system requires local copies of third-party libraries for offline operation.

## Required Files

### 1. Chart.js (v4.4.1)
**Location:** `assets/js/vendor/chart.min.js`

**Download:**
```
https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js
```

**Steps:**
1. Download the file from the URL above
2. Save as `assets/js/vendor/chart.min.js`

### 2. Tom Select (v2.3.1)
**Location:** 
- CSS: `assets/css/vendor/tom-select.css`
- JS: `assets/js/vendor/tom-select.complete.min.js`

**Download:**
```
CSS: https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css
JS:  https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js
```

**Steps:**
1. Download both files from the URLs above
2. Save CSS as `assets/css/vendor/tom-select.css`
3. Save JS as `assets/js/vendor/tom-select.complete.min.js`

## Directory Structure

```
assets/
├── css/
│   ├── vendor/
│   │   └── tom-select.css
│   ├── input.css
│   └── output.css
└── js/
    ├── vendor/
    │   ├── chart.min.js
    │   └── tom-select.complete.min.js
    ├── dashboard.js
    ├── register.js
    └── ...
```

## Quick Setup Script (Windows)

Create `download-vendors.bat` in project root:

```bat
@echo off
echo Downloading vendor libraries...

mkdir assets\css\vendor 2>nul
mkdir assets\js\vendor 2>nul

echo Downloading Chart.js...
curl -L "https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" -o "assets\js\vendor\chart.min.js"

echo Downloading Tom Select CSS...
curl -L "https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" -o "assets\css\vendor\tom-select.css"

echo Downloading Tom Select JS...
curl -L "https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js" -o "assets\js\vendor\tom-select.complete.min.js"

echo Done! All vendor libraries downloaded.
pause
```

Run: `download-vendors.bat`

## Verification

After downloading, verify files exist:
- [ ] `assets/css/vendor/tom-select.css` (~20KB)
- [ ] `assets/js/vendor/chart.min.js` (~200KB)
- [ ] `assets/js/vendor/tom-select.complete.min.js` (~80KB)

## Notes

- **Fonts:** System fonts are used (no Google Fonts dependency)
- **Tailwind CSS:** Generated locally via `build-css.bat` to `assets/css/output.css`
- **No CDN fallback:** All resources must be local for offline operation

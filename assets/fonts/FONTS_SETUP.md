# Font Setup Guide for Offline Use

This directory contains offline font files for the Sports Club Management System. All fonts are loaded locally for offline functionality.

## Quick Overview

| Font | Purpose | Weights | Variants |
|------|---------|---------|----------|
| **Poppins** | English text, UI elements, buttons | 400, 500, 600, 700, 900 | Regular, Medium, SemiBold, Bold, Black |
| **Noto Sans Sinhala** | Sinhala text, headings | 400, 500, 600, 700 | Regular, Medium, SemiBold, Bold |
| **Iskoola Pota** | Sinhala fallback font | 400 | Regular |

---

## Detailed Font Information

### 1. Poppins Font Family
**Purpose:** Primary font for English text, UI elements, navigation, buttons  
**Download:** https://fonts.google.com/specimen/Poppins  
**License:** Open Font License (OFL)  
**Language Support:** Latin-based scripts

#### Required Files:

| Weight | Name | File (WOFF2) | File (WOFF) | CSS Weight | Use Case |
|--------|------|--------------|------------|-----------|----------|
| 400 | Regular | `Poppins-Regular.woff2` | `Poppins-Regular.woff` | `font-weight: 400` | Body text, normal content |
| 500 | Medium | `Poppins-Medium.woff2` | `Poppins-Medium.woff` | `font-weight: 500` | Secondary headings, labels |
| 600 | SemiBold | `Poppins-SemiBold.woff2` | `Poppins-SemiBold.woff` | `font-weight: 600` | Form labels, buttons |
| 700 | Bold | `Poppins-Bold.woff2` | `Poppins-Bold.woff` | `font-weight: 700` | Headings, emphasis |
| 900 | Black | `Poppins-Black.woff2` | `Poppins-Black.woff` | `font-weight: 900` | Large headings, titles |

**Total Size:** ~240 KB (WOFF2 format)

---

### 2. Noto Sans Sinhala Font Family
**Purpose:** Sinhala language display, headings, forms, emphasized content  
**Download:** https://fonts.google.com/specimen/Noto+Sans+Sinhala  
**License:** Open Font License (OFL)  
**Language Support:** Sinhala script (and Latin fallback)

#### Required Files:

| Weight | Name | File (WOFF2) | File (WOFF) | CSS Weight | Use Case |
|--------|------|--------------|------------|-----------|----------|
| 400 | Regular | `NotoSansSinhala-Regular.woff2` | `NotoSansSinhala-Regular.woff` | `font-weight: 400` | Sinhala body text, content |
| 500 | Medium | `NotoSansSinhala-Medium.woff2` | `NotoSansSinhala-Medium.woff` | `font-weight: 500` | Sinhala secondary headings |
| 600 | SemiBold | `NotoSansSinhala-SemiBold.woff2` | `NotoSansSinhala-SemiBold.woff` | `font-weight: 600` | Sinhala form labels, emphasis |
| 700 | Bold | `NotoSansSinhala-Bold.woff2` | `NotoSansSinhala-Bold.woff` | `font-weight: 700` | Sinhala headings, titles |

**Total Size:** ~180 KB (WOFF2 format)

---

### 3. Iskoola Pota Font Family
**Purpose:** Sinhala fallback font, alternative Sinhala rendering  
**Download:** https://fonts.google.com/specimen/Iskoola+Pota  
**License:** Open Font License (OFL)  
**Language Support:** Sinhala script

#### Required Files:

| Weight | Name | File (WOFF2) | File (WOFF) | CSS Weight | Use Case |
|--------|------|--------------|------------|-----------|----------|
| 400 | Regular | `IskoollaPota.woff2` | `IskoollaPota.woff` | `font-weight: 400` | Sinhala fallback/alternative rendering |

**Total Size:** ~120 KB (WOFF2 format)

---

## Installation Steps

### Step 1: Download Font Files

#### From Google Fonts (Easiest):

1. **Poppins Font:**
   - Visit: https://fonts.google.com/specimen/Poppins
   - Click red **"Download family"** button (top right)
   - Extract the ZIP file

2. **Noto Sans Sinhala Font:**
   - Visit: https://fonts.google.com/specimen/Noto+Sans+Sinhala
   - Click red **"Download family"** button (top right)
   - Extract the ZIP file

3. **Iskoola Pota Font:**
   - Visit: https://fonts.google.com/specimen/Iskoola+Pota
   - Click red **"Download family"** button (top right)
   - Extract the ZIP file

#### From GitHub (Alternative):

- Poppins: https://github.com/google/fonts/tree/main/ofl/poppins
- Noto Sans Sinhala: https://github.com/google/fonts/tree/main/ofl/notosanssinhala
- Iskoola Pota: https://github.com/google/fonts/tree/main/ofl/iskoolsinhala

---

### Step 2: Convert TTF to WOFF2 and WOFF

After downloading, you'll have `.ttf` files that need conversion.

#### Option A: Online Converter (Easiest)

1. Go to: https://convertio.co/ttf-woff2/
2. Upload each TTF file
3. Download as WOFF2
4. Repeat for WOFF format: https://convertio.co/ttf-woff/

#### Option B: Using Python (fonttools)

Windows PowerShell:
```powershell
# Install fonttools
pip install fonttools brotli

# Navigate to your downloaded fonts directory
cd C:\Users\YourUsername\Downloads\fonts

# Convert all TTF files to WOFF2
Get-ChildItem -Filter "*.ttf" | ForEach-Object {
    $output = $_.BaseName + ".woff2"
    python -c "from fontTools.ttLib import TTFont; font = TTFont('$($_.FullName)'); font.flavor = 'woff2'; font.save('$output')"
    Write-Host "Created: $output"
}

# Convert all TTF files to WOFF
Get-ChildItem -Filter "*.ttf" | ForEach-Object {
    $output = $_.BaseName + ".woff"
    python -c "from fontTools.ttLib import TTFont; font = TTFont('$($_.FullName)'); font.flavor = 'woff'; font.save('$output')"
    Write-Host "Created: $output"
}
```

#### Option C: Online Tools with Batch Processing

- **CloudConvert:** https://cloudconvert.com/ttf-to-woff2 (batch upload 20 files/month free)
- **Convertio:** https://convertio.co/ttf-woff/ (faster converter)
- **Font Converter Online:** https://woff.tools/

---

### Step 3: Rename and Organize Files

After conversion, rename your files **exactly** as shown below (case-sensitive):

#### Poppins Files (5 files total):
```
Poppins-Regular.woff2
Poppins-Regular.woff
Poppins-Medium.woff2
Poppins-Medium.woff
Poppins-SemiBold.woff2
Poppins-SemiBold.woff
Poppins-Bold.woff2
Poppins-Bold.woff
Poppins-Black.woff2
Poppins-Black.woff
```

#### Noto Sans Sinhala Files (4 files total):
```
NotoSansSinhala-Regular.woff2
NotoSansSinhala-Regular.woff
NotoSansSinhala-Medium.woff2
NotoSansSinhala-Medium.woff
NotoSansSinhala-SemiBold.woff2
NotoSansSinhala-SemiBold.woff
NotoSansSinhala-Bold.woff2
NotoSansSinhala-Bold.woff
```

#### Iskoola Pota Files (1 file total):
```
IskoollaPota.woff2
IskoollaPota.woff
```

---

### Step 4: Copy to Project Directory

Copy **all renamed files** to: `c:\wamp64\www\sports-v2\assets\fonts\`

Your final directory structure should look like:
```
c:\wamp64\www\sports-v2\assets\fonts\
├── Poppins-Regular.woff2
├── Poppins-Regular.woff
├── Poppins-Medium.woff2
├── Poppins-Medium.woff
├── Poppins-SemiBold.woff2
├── Poppins-SemiBold.woff
├── Poppins-Bold.woff2
├── Poppins-Bold.woff
├── Poppins-Black.woff2
├── Poppins-Black.woff
├── NotoSansSinhala-Regular.woff2
├── NotoSansSinhala-Regular.woff
├── NotoSansSinhala-Medium.woff2
├── NotoSansSinhala-Medium.woff
├── NotoSansSinhala-SemiBold.woff2
├── NotoSansSinhala-SemiBold.woff
├── NotoSansSinhala-Bold.woff2
├── NotoSansSinhala-Bold.woff
├── IskoollaPota.woff2
├── IskoollaPota.woff
└── FONTS_SETUP.md
```

---

### Step 5: Rebuild CSS and Test

1. **Rebuild Tailwind CSS:**
   ```powershell
   cd c:\wamp64\www\sports-v2
   .\build-css.bat
   ```

2. **Clear Browser Cache:**
   - Ctrl+Shift+Delete (or Cmd+Shift+Delete on Mac)
   - Select "All time"
   - Clear cache

3. **Reload Page:**
   - Refresh page in browser
   - Fonts should now display from local files

4. **Test Offline Mode:**
   - Disconnect from internet
   - Reload page
   - Fonts should still display correctly

---

## File Size & Browser Support

### Total File Sizes (Downloaded):
| Format | Size | Total |
|--------|------|-------|
| WOFF2 (modern browsers) | 540 KB | ~3 MB |
| WOFF (older browsers) | 650 KB | ~4 MB |
| Both formats | 1.2 MB | ~7 MB |

### Browser Support:
| Format | Supported Browsers |
|--------|-------------------|
| WOFF2 | Chrome 36+, Firefox 39+, Safari 12+, Edge 15+ |
| WOFF | IE 9+, Chrome 5+, Firefox 3.6+, Safari 5.1+, Edge 12+ |

**Note:** WOFF2 is 30-50% smaller than WOFF. Including both ensures compatibility with all modern and older browsers.

---

## Font Usage in Application

### Where Fonts Are Used:

#### English Text (Uses Poppins):
- Navigation menu
- Buttons and links
- English form fields
- Administration panels
- Dialog boxes

#### Sinhala Text (Uses Noto Sans Sinhala → Iskoola Pota):
- Page titles and headings
- Labels in Sinhala (සිංහල)
- Sinhala form content
- Table headers with Sinhala text
- Dialog text in Sinhala

### CSS Font Stacks:

**For English Content:**
```css
font-family: 'Poppins', 'Noto Sans Sinhala', 'Iskoola Pota', sans-serif;
```

**For Sinhala Content:**
```css
font-family: 'Noto Sans Sinhala', 'Iskoola Pota', 'Poppins', sans-serif;
```

### Font Weights in Use:

| Weight | Used For |
|--------|----------|
| 400 (Regular) | Body text, normal content, list items |
| 500 (Medium) | Secondary headings, labels, emphasized text |
| 600 (SemiBold) | Form labels, buttons, table headers |
| 700 (Bold) | Page titles, main headings, emphasis |
| 900 (Black) | Large titles, special emphasis (Poppins only) |

---

## Verification & Testing

### Before Testing:

1. Verify all 21 font files are in `assets/fonts/` directory
2. File names are **exactly** as specified (including capitalization)
3. Files are readable (check permissions)

### Testing Locally:

```powershell
cd c:\wamp64\www\sports-v2

# Verify font directory
Get-ChildItem -Path "assets\fonts" -Filter "*.woff*" | Measure-Object

# Should show 21 files
```

### Browser DevTools Testing:

1. Open page in browser (Chrome, Firefox, etc.)
2. Press F12 to open DevTools
3. Go to **Network** tab
4. Reload page
5. Filter for "fonts"
6. Verify `.woff2` and `.woff` files load with status 200
7. Check file sizes match expectations

### Check Loaded Fonts:

In browser console (F12 → Console tab):
```javascript
// Check what fonts are loaded
window.getComputedStyle(document.body).fontFamily
// Output: Poppins, Noto Sans Sinhala, Iskoola Pota, sans-serif

// Check specific element
window.getComputedStyle(document.querySelector('h1')).fontFamily
```

---

## Troubleshooting

### Issue: Fonts Not Displaying

**Symptoms:** System fonts appear instead of downloaded fonts

**Solutions:**
1. Clear browser cache completely (Ctrl+Shift+Delete)
2. Verify all 21 files exist in `assets/fonts/` with correct names
3. Check browser console (F12) for CSS errors
4. Verify file paths in `assets/css/output.css`
5. Check Apache/server has read permissions on font files

**Windows Permission Fix:**
```powershell
# Grant read permissions to everyone on fonts folder
icacls "c:\wamp64\www\sports-v2\assets\fonts" /grant Everyone:F /t
```

---

### Issue: Fonts Load Slow

**Symptoms:** Text appears to flash or delays while fonts load

**Solutions:**
1. Ensure only WOFF2 files are used in modern browsers
2. Check that CSS `font-display: swap` is set (allows text display while fonts load)
3. Verify Apache gzip compression is enabled
4. Use browser DevTools Network tab to check file sizes load correctly

**Check compression in Apache:**
```apache
# In .htaccess or httpd.conf
AddEncoding gzip .woff2
AddEncoding gzip .woff
```

---

### Issue: Sinhala Text Still Not Displaying Correctly

**Symptoms:** Sinhala characters appear broken or mixed up

**Solutions:**
1. Verify page charset is UTF-8:
   ```html
   <meta charset="UTF-8">
   ```
2. Verify Noto Sans Sinhala weight 400 and 700 are present
3. Test in Firefox (better Sinhala support) vs Chrome
4. Clear all browser cache including cookies
5. Check that `lang="si"` attribute is set on HTML element

---

### Issue: Fonts Work Online But Not Offline

**Symptoms:** Fonts display when connected to internet but fail when offline

**Causes:**
1. CSS still contains `@import` from Google Fonts URLs
2. Font files not fully downloaded to browser cache
3. Incorrect file paths in CSS

**Fix:**
1. Verify `assets/css/input.css` has **NO** `@import url('https://fonts.googleapis.com')`
2. Verify all `@font-face` rules point to local `/assets/fonts/` paths
3. Open page offline and check Network tab shows files loading

---

### Issue: Only WOFF Files Work, Not WOFF2

**Symptom:** WOFF2 files don't load

**Solution:**
1. Verify WOFF2 files were converted correctly
2. Check MIME type is set in Apache:
   ```apache
   AddType font/woff2 .woff2
   AddType font/woff .woff
   ```

---

## File Naming Reference

Use this table to verify all files are present and correctly named:

### Poppins Fonts

| Variant | WOFF2 Filename | WOFF Filename | CSS Weight |
|---------|----------------|---------------|-----------|
| Regular | `Poppins-Regular.woff2` | `Poppins-Regular.woff` | 400 |
| Medium | `Poppins-Medium.woff2` | `Poppins-Medium.woff` | 500 |
| SemiBold | `Poppins-SemiBold.woff2` | `Poppins-SemiBold.woff` | 600 |
| Bold | `Poppins-Bold.woff2` | `Poppins-Bold.woff` | 700 |
| Black | `Poppins-Black.woff2` | `Poppins-Black.woff` | 900 |

### Noto Sans Sinhala Fonts

| Variant | WOFF2 Filename | WOFF Filename | CSS Weight |
|---------|----------------|---------------|-----------|
| Regular | `NotoSansSinhala-Regular.woff2` | `NotoSansSinhala-Regular.woff` | 400 |
| Medium | `NotoSansSinhala-Medium.woff2` | `NotoSansSinhala-Medium.woff` | 500 |
| SemiBold | `NotoSansSinhala-SemiBold.woff2` | `NotoSansSinhala-SemiBold.woff` | 600 |
| Bold | `NotoSansSinhala-Bold.woff2` | `NotoSansSinhala-Bold.woff` | 700 |

### Iskoola Pota Fonts

| Variant | WOFF2 Filename | WOFF Filename | CSS Weight |
|---------|----------------|---------------|-----------|
| Regular | `IskoollaPota.woff2` | `IskoollaPota.woff` | 400 |

---

## Related Files

- **CSS Definition:** `/assets/css/input.css` (contains all @font-face rules)
- **Compiled CSS:** `/assets/css/output.css` (generated from input.css)
- **Header Script:** `/includes/header.php` (no longer includes Google CDN)
- **Config:** `/tailwind.config.js` (defines font stacks)

---

## Support & Resources

- **Google Fonts Issues:** https://github.com/google/fonts/issues
- **Font Format Support:** https://caniuse.com/woff2
- **Font Conversion Tool:** https://convertio.co/ttf-woff2/
- **Font Tools Documentation:** https://fonttools.readthedocs.io/

---

**Last Updated:** February 3, 2026  
**Total Font Files Needed:** 21 (10 Poppins + 8 Noto Sans Sinhala + 3 Iskoola Pota)  
**Total Size (WOFF2):** ~3 MB  
**Status:** ✅ Ready for offline use

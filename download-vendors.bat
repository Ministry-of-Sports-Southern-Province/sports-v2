@echo off
echo ========================================
echo  Downloading Vendor Libraries
echo  For Offline Support
echo ========================================
echo.

REM Create vendor directories
if not exist "assets\css\vendor" mkdir "assets\css\vendor"
if not exist "assets\js\vendor" mkdir "assets\js\vendor"

echo [1/3] Downloading Chart.js v4.4.1...
curl -L "https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" -o "assets\js\vendor\chart.min.js"
if %errorlevel% neq 0 (
    echo ERROR: Failed to download Chart.js
) else (
    echo SUCCESS: Chart.js downloaded
)
echo.

echo [2/3] Downloading Tom Select CSS v2.3.1...
curl -L "https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" -o "assets\css\vendor\tom-select.css"
if %errorlevel% neq 0 (
    echo ERROR: Failed to download Tom Select CSS
) else (
    echo SUCCESS: Tom Select CSS downloaded
)
echo.

echo [3/3] Downloading Tom Select JS v2.3.1...
curl -L "https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js" -o "assets\js\vendor\tom-select.complete.min.js"
if %errorlevel% neq 0 (
    echo ERROR: Failed to download Tom Select JS
) else (
    echo SUCCESS: Tom Select JS downloaded
)
echo.

echo ========================================
echo  Download Complete!
echo ========================================
echo.
echo Verifying files...
if exist "assets\js\vendor\chart.min.js" (
    echo [OK] Chart.js
) else (
    echo [MISSING] Chart.js
)

if exist "assets\css\vendor\tom-select.css" (
    echo [OK] Tom Select CSS
) else (
    echo [MISSING] Tom Select CSS
)

if exist "assets\js\vendor\tom-select.complete.min.js" (
    echo [OK] Tom Select JS
) else (
    echo [MISSING] Tom Select JS
)

echo.
echo All vendor libraries are ready for offline use.
echo.
pause

@echo off
setlocal
cd /d "%~dp0"

set TAILWIND=
if exist "bin\tailwindcss.exe" set TAILWIND=bin\tailwindcss.exe
if exist "bin\tailwindcss-windows-x64.exe" set TAILWIND=bin\tailwindcss-windows-x64.exe
if "%TAILWIND%"=="" set TAILWIND=tailwindcss

"%TAILWIND%" -i assets\css\input.css -o assets\css\tailwind.css --minify -c tailwind.config.js
if errorlevel 1 (
  echo.
  echo Tailwind CLI not found. Download the standalone executable:
  echo   https://github.com/tailwindlabs/tailwindcss/releases/latest
  echo   Get tailwindcss-windows-x64.exe and place it in the bin\ folder.
  echo   Or add tailwindcss to your PATH.
  exit /b 1
)
echo Built assets\css\tailwind.css
exit /b 0

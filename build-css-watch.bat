@echo off
setlocal
cd /d "%~dp0"

set TAILWIND=
if exist "bin\tailwindcss.exe" set TAILWIND=bin\tailwindcss.exe
if exist "bin\tailwindcss-windows-x64.exe" set TAILWIND=bin\tailwindcss-windows-x64.exe
if "%TAILWIND%"=="" set TAILWIND=tailwindcss

"%TAILWIND%" -i assets\css\input.css -o assets\css\output.css --watch -c tailwind.config.js
exit /b 0

@echo off
echo Building Tailwind CSS...
tailwindcss -i assets\css\input.css -o assets\css\output.css --minify
if %errorlevel% neq 0 (
    echo ERROR: Build failed. Ensure Tailwind CLI is installed: npm install -g @tailwindcss/cli
    echo Also run: npm install tailwindcss
) else (
    echo SUCCESS: assets\css\output.css rebuilt.
)
pause

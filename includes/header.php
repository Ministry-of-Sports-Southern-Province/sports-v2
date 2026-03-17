<?php
// Include authentication functions
require_once __DIR__ . '/auth.php';

// Require login for all pages (except login page itself and public pages)
$currentScript = basename($_SERVER['SCRIPT_FILENAME']);
if ($currentScript !== 'login.php' && !($isPublicPage ?? false)) {
    requireLogin();
}

/**
 * Reusable Header Component
 * 
 * @param string $pageTitle - The title of the page for i18n attribute
 * @param string $pageHeading - The heading text for i18n attribute
 * @param string $activePage - Current active page for navigation highlighting
 * @param string $customStyles - Additional CSS styles (optional)
 * @param array $additionalLinks - Additional CSS/JS links to include in head (optional)
 * @param string $basePath - Base path for assets (defaults to '../' for public folder pages)
 */

// Default values
$pageTitle = $pageTitle ?? 'page.dashboard_title';
$pageHeading = $pageHeading ?? 'page.dashboard_title';
$activePage = $activePage ?? 'dashboard';
$customStyles = $customStyles ?? '';
$additionalLinks = $additionalLinks ?? [];
$basePath = $basePath ?? '../';
$isPublicPage = $isPublicPage ?? false;
$currentAdmin = $isPublicPage ? null : getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="si">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="<?php echo htmlspecialchars($pageTitle); ?>">ක්‍රීඩා සමාජ කළමනාකරණ පද්ධතිය</title>

    <!-- Tailwind CSS -->
    <link href="<?php echo htmlspecialchars($basePath); ?>assets/css/output.css" rel="stylesheet">

    <?php foreach ($additionalLinks as $link): ?>
        <?php echo $link . "\n    "; ?>
    <?php endforeach; ?>

    <style>
        /* Base styles for government website */
        body {
            font-family: 'Poppins', 'Noto Sans Sinhala', 'Iskoola Pota', sans-serif;
            transition: font-size 0.3s ease;
        }

        /* Sinhala-specific typography */
        [lang="si"],
        [data-i18n],
        .sinhala-text {
            font-family: 'Noto Sans Sinhala', 'Iskoola Pota', 'Poppins', sans-serif;
        }

        /* Headings with Sinhala support */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .page-title,
        .section-heading {
            font-family: 'Noto Sans Sinhala', 'Iskoola Pota', 'Poppins', sans-serif;
            font-weight: 700;
        }

        /* Form labels and inputs with Sinhala support */
        .form-label,
        .form-select,
        .form-input,
        .form-textarea {
            font-family: 'Noto Sans Sinhala', 'Iskoola Pota', 'Poppins', sans-serif;
        }

        /* Table text with Sinhala support */
        table {
            font-family: 'Noto Sans Sinhala', 'Iskoola Pota', 'Poppins', sans-serif;
        }

        .gov-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }

        /* Global Accessibility Widget */
        .accessibility-fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
            z-index: 1000;
            transition: all 0.3s ease;
            border: 3px solid white;
        }

        .accessibility-fab:hover {
            transform: scale(1.1) rotate(10deg);
            box-shadow: 0 12px 28px rgba(59, 130, 246, 0.5);
        }

        .accessibility-fab.active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }

        .accessibility-fab svg {
            width: 32px;
            height: 32px;
            color: white;
        }

        .accessibility-panel {
            position: fixed;
            bottom: 100px;
            right: 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 20px;
            min-width: 280px;
            z-index: 999;
            opacity: 0;
            transform: translateY(20px) scale(0.9);
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border: 2px solid #e5e7eb;
        }

        .accessibility-panel.active {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: all;
        }

        .accessibility-panel-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 2px solid #e5e7eb;
        }

        .accessibility-panel-icon {
            width: 28px;
            height: 28px;
            color: #3b82f6;
        }

        .accessibility-panel-title {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }

        .font-control-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .font-control-label {
            font-size: 13px;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .font-control-buttons {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .font-control-btn {
            flex: 1;
            height: 44px;
            border: 2px solid #e5e7eb;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: #4b5563;
            font-weight: 600;
        }

        .font-control-btn:hover:not(:disabled) {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
            transform: translateY(-2px);
        }

        .font-control-btn:active:not(:disabled) {
            transform: translateY(0);
        }

        .font-control-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .font-control-btn svg {
            width: 20px;
            height: 20px;
        }

        .font-size-display {
            font-weight: 700;
            color: #1f2937;
            font-size: 18px;
            min-width: 60px;
            text-align: center;
            background: #f3f4f6;
            border-radius: 10px;
            padding: 10px;
        }

        .reset-btn {
            width: 100%;
            margin-top: 12px;
            padding: 10px;
            background: #f3f4f6;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            color: #6b7280;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 13px;
        }

        .reset-btn:hover {
            background: #e5e7eb;
            color: #374151;
        }

        /* ===== Global UI: Sections, Forms, Buttons ===== */
        main.container {
            max-width: 1280px;
        }

        /* allow main to grow so footer can stick to bottom */
        main {
            flex: 1 1 auto;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
        }

        .page-title.center {
            text-align: center;
            margin-bottom: 2rem;
        }

        /* Section cards */
        .section-card {
            background: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.5rem;
        }

        .section-card:last-child {
            margin-bottom: 0;
        }

        .section-heading {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #3b82f6;
        }

        .section-heading+p {
            margin-top: 0;
        }

        /* Form controls */
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.625rem 0.875rem;
            font-size: 0.9375rem;
            color: #1e293b;
            background: #fff;
            border: 1.5px solid #cbd5e1;
            border-radius: 0.5rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .form-input::placeholder,
        .form-textarea::placeholder {
            color: #94a3b8;
        }

        .form-input:disabled,
        .form-select:disabled,
        .form-textarea:disabled {
            background: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
        }

        .form-textarea {
            min-height: 80px;
            resize: vertical;
        }

        .form-hint {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .form-error {
            font-size: 0.8125rem;
            color: #dc2626;
            margin-top: 0.25rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.9375rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #fff;
            box-shadow: 0 1px 3px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.25);
        }

        .btn-secondary {
            background: #64748b;
            color: #fff;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .btn-success {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #fff;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%);
        }

        .btn-outline {
            background: #fff;
            color: #475569;
            border: 1.5px solid #cbd5e1;
        }

        .btn-outline:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #334155;
        }

        /* Stat cards (dashboard / summary) */
        .stat-card {
            background: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            border-left: 4px solid #3b82f6;
            padding: 1.25rem 1.5rem;
            transition: box-shadow 0.2s;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-card .stat-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .stat-card .stat-value {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1e293b;
        }

        /* Content / report output card */
        .content-card {
            background: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
        }

        /* Link cards (e.g. reports hub) */
        .link-card {
            display: block;
            background: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
        }

        .link-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }

        .link-card .link-card-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .link-card .link-card-desc {
            font-size: 0.875rem;
            color: #64748b;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-overlay.hidden {
            display: none;
        }

        .modal-panel {
            background: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid #e2e8f0;
            max-width: 28rem;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        @media (max-width: 640px) {
            .modal-panel {
                max-width: 100%;
                max-height: 95vh;
                margin: 0.5rem;
            }
        }

        .modal-panel .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-panel .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .modal-panel .modal-body {
            padding: 1.5rem;
        }

        /* Data tables: better visibility, borders, spacing */
        .data-table {
            min-width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .data-table thead {
            background: linear-gradient(to right, #334155, #1e293b);
            color: white;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .data-table thead th {
            padding: 0.875rem 1.25rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: white;
            white-space: nowrap;
            border-bottom: 2px solid #475569;
        }

        .data-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: background-color 0.15s;
        }

        .data-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: rgba(248, 250, 252, 0.5);
        }

        .data-table tbody tr:nth-child(even):hover {
            background-color: #f1f5f9;
        }

        .data-table tbody td {
            padding: 0.875rem 1.25rem;
            font-size: 0.875rem;
            color: #1e293b;
        }

        .data-table-wrapper {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        /* Report-style tables (bordered cells) */
        .report-table {
            min-width: 100%;
            border-collapse: collapse;
        }

        .report-table thead {
            background: #334155;
            color: white;
        }

        .report-table thead th {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            border: 1px solid #475569;
        }

        .report-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .report-table tbody td {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: #1e293b;
            border: 1px solid #cbd5e1;
        }

        <?php echo $customStyles; ?>
    </style>
</head>

<body class="bg-gray-50 flex flex-col min-h-screen">
    <!-- Header -->
    <header class="gov-header text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <!-- Top row: Logo, Title and User Info -->
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-4">
                    <!-- Site Logo -->
                    <img src="<?php echo htmlspecialchars($basePath); ?>assets/img/logo.svg" alt="Southern Province Sports" class="w-12 h-12 object-contain" />
                    <div>
                        <h1 class="text-2xl font-bold" data-i18n="<?php echo htmlspecialchars($pageHeading); ?>">ක්‍රීඩා සමාජ කළමනාකරණ පද්ධතිය</h1>
                        <p class="text-sm text-blue-100" data-i18n="page.welcome_subtitle">දකුණු පළාත් ක්‍රීඩා දෙපාර්තමේන්තුව</p>
                    </div>
                </div>

                <!-- Right side: Language Switcher above User Info & Logout -->
                <div class="flex flex-col items-end space-y-3">
                    <!-- Language Switcher -->
                    <div class="flex space-x-2">
                        <button data-language="en" class="px-3 py-1 rounded text-sm font-medium transition bg-white/10 text-white hover:bg-white/20">
                            EN
                        </button>
                        <button data-language="si" class="px-3 py-1 rounded text-sm font-medium transition bg-white/10 text-white hover:bg-white/20">
                            සිං
                        </button>
                        <button data-language="ta" class="px-3 py-1 rounded text-sm font-medium transition bg-white/10 text-white hover:bg-white/20">
                            தமிழ்
                        </button>
                    </div>

                    <!-- User Info & Logout (hidden on public pages) -->
                    <?php if ($isPublicPage): ?>
                        <div class="flex items-center space-x-2 bg-white/10 rounded-lg px-4 py-2">
                            <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" />
                            </svg>
                            <span class="text-sm font-semibold text-white" data-i18n="public.public_view_badge">Public View</span>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center space-x-3 bg-white/10 rounded-lg px-4 py-2">
                            <!-- User Avatar Icon -->
                            <div class="bg-white/20 rounded-full p-2">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>

                            <!-- User Info -->
                            <div class="text-left border-r border-white/20 pr-4">
                                <div class="text-sm font-semibold text-white leading-tight"><?php echo htmlspecialchars($currentAdmin['full_name']); ?></div>
                                <div class="text-xs text-blue-100 mt-0.5"><?php echo htmlspecialchars($currentAdmin['username']); ?></div>
                            </div>

                            <!-- Logout Button -->
                            <a href="<?php echo $basePath; ?>api/logout.php"
                                class="flex items-center space-x-2 px-3 py-1.5 bg-white/10 hover:bg-red-500 rounded-md transition-all duration-200 group"
                                title="Logout">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span class="text-sm font-medium text-white">Logout</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Navigation -->
            <nav>
                <?php if ($isPublicPage): ?>
                    <!-- Public page: only show current page indicator, no other links -->
                    <ul class="flex space-x-6">
                        <li><span class="font-semibold border-b-2 border-white pb-1" data-i18n="nav.public_directory">Public Directory</span></li>
                    </ul>
                <?php else: ?>
                    <ul class="flex space-x-6">
                        <li>
                            <a href="<?php echo $basePath; ?>public/dashboard.php"
                                class="<?php echo $activePage === 'dashboard' ? 'font-semibold border-b-2 border-white pb-1' : 'hover:text-blue-200 transition'; ?>"
                                data-i18n="nav.dashboard">උපකරණ පුවරුව</a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li>
                                <a href="<?php echo $basePath; ?>public/register.php"
                                    class="<?php echo $activePage === 'register' ? 'font-semibold border-b-2 border-white pb-1' : 'hover:text-blue-200 transition'; ?>"
                                    data-i18n="nav.register">සමාජය ලියාපදිංචි කරන්න</a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a href="<?php echo $basePath; ?>public/reorganizations.php"
                                class="<?php echo $activePage === 'reorganizations' ? 'font-semibold border-b-2 border-white pb-1' : 'hover:text-blue-200 transition'; ?>"
                                data-i18n="nav.reorganizations">ප්‍රතිසංවිධාන</a>
                        </li>
                        <li>
                            <a href="<?php echo $basePath; ?>public/summary.php"
                                class="<?php echo $activePage === 'summary' ? 'font-semibold border-b-2 border-white pb-1' : 'hover:text-blue-200 transition'; ?>"
                                data-i18n="nav.summary">සාරාංශය</a>
                        </li>
                        <li>
                            <a href="<?php echo $basePath; ?>public/reports.php"
                                class="<?php echo $activePage === 'reports' ? 'font-semibold border-b-2 border-white pb-1' : 'hover:text-blue-200 transition'; ?>"
                                data-i18n="nav.reports">වාර්තා</a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li>
                                <a href="<?php echo $basePath; ?>public/admin-settings.php"
                                    class="<?php echo $activePage === 'admin-settings' ? 'font-semibold border-b-2 border-white pb-1' : 'hover:text-blue-200 transition'; ?>"
                                    data-i18n="nav.admin_settings">පරිපාලන සැකසීම්</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Global Accessibility Widget -->
    <div class="accessibility-fab" id="accessibilityFab" onclick="toggleAccessibilityPanel()" title="Accessibility Options" aria-label="Open accessibility options">
        <svg fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C10.9 2 10 2.9 10 4s.9 2 2 2 2-.9 2-2-.9-2-2-2zm9 7h-6v13h-2v-6h-2v6H9V9H3V7h18v2z" />
        </svg>
    </div>

    <div class="accessibility-panel" id="accessibilityPanel">
        <div class="accessibility-panel-header">
            <svg class="accessibility-panel-icon" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C10.9 2 10 2.9 10 4s.9 2 2 2 2-.9 2-2-.9-2-2-2zm9 7h-6v13h-2v-6h-2v6H9V9H3V7h18v2z" />
            </svg>
            <span class="accessibility-panel-title">Accessibility</span>
        </div>
        <div class="font-control-group">
            <span class="font-control-label">Text Size</span>
            <div class="font-control-buttons">
                <button onclick="decreaseGlobalFontSize()" class="font-control-btn" title="Decrease text size" aria-label="Decrease text size" id="globalFontDecreaseBtn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4" />
                    </svg>
                </button>
                <span class="font-size-display" id="globalFontSizeLabel">100%</span>
                <button onclick="increaseGlobalFontSize()" class="font-control-btn" title="Increase text size" aria-label="Increase text size" id="globalFontIncreaseBtn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            </div>
            <button onclick="resetGlobalFontSize()" class="reset-btn" title="Reset to default" aria-label="Reset font size to default">
                Reset to Default
            </button>
        </div>
    </div>

    <script>
        // Global Accessibility Functions
        let globalFontSize = 100;
        const MIN_FONT_SIZE = 80;
        const MAX_FONT_SIZE = 140;
        const FONT_SIZE_STEP = 10;

        function initializeGlobalAccessibility() {
            const savedFontSize = localStorage.getItem('globalFontSize');
            if (savedFontSize) {
                globalFontSize = parseInt(savedFontSize);
            }
            applyGlobalFontSize();
        }

        function applyGlobalFontSize() {
            document.documentElement.style.fontSize = globalFontSize + '%';
            updateGlobalFontSizeLabel();
            localStorage.setItem('globalFontSize', globalFontSize);
        }

        function updateGlobalFontSizeLabel() {
            const label = document.getElementById('globalFontSizeLabel');
            const decreaseBtn = document.getElementById('globalFontDecreaseBtn');
            const increaseBtn = document.getElementById('globalFontIncreaseBtn');

            if (label) {
                label.textContent = globalFontSize + '%';
            }

            if (decreaseBtn) {
                decreaseBtn.disabled = globalFontSize <= MIN_FONT_SIZE;
            }
            if (increaseBtn) {
                increaseBtn.disabled = globalFontSize >= MAX_FONT_SIZE;
            }
        }

        function increaseGlobalFontSize() {
            if (globalFontSize < MAX_FONT_SIZE) {
                globalFontSize += FONT_SIZE_STEP;
                applyGlobalFontSize();
            }
        }

        function decreaseGlobalFontSize() {
            if (globalFontSize > MIN_FONT_SIZE) {
                globalFontSize -= FONT_SIZE_STEP;
                applyGlobalFontSize();
            }
        }

        function resetGlobalFontSize() {
            globalFontSize = 100;
            applyGlobalFontSize();
        }

        function toggleAccessibilityPanel() {
            const panel = document.getElementById('accessibilityPanel');
            const fab = document.getElementById('accessibilityFab');
            panel.classList.toggle('active');
            fab.classList.toggle('active');
        }

        document.addEventListener('click', function(event) {
            const panel = document.getElementById('accessibilityPanel');
            const fab = document.getElementById('accessibilityFab');
            if (panel && fab && !panel.contains(event.target) && !fab.contains(event.target)) {
                panel.classList.remove('active');
                fab.classList.remove('active');
            }
        });

        document.addEventListener('DOMContentLoaded', initializeGlobalAccessibility);
    </script>
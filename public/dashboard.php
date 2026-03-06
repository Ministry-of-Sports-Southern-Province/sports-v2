<?php

/**
 * Dashboard Page
 * Main dashboard for viewing and managing sports clubs
 */

// Page configuration
$pageTitle = 'page.dashboard_title';
$pageHeading = 'page.dashboard_title';
$activePage = 'dashboard';

// Custom styles for this page
$customStyles = '
        body {
            background: linear-gradient(to bottom, #f8fafc 0%, #e2e8f0 100%);
        }
        .stat-card {
            background: white;
            border-left: 4px solid;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .search-card {
            background: white;
            border-top: 3px solid #3b82f6;
        }
        table tbody tr:hover {
            background-color: #f1f5f9;
        }
        .action-btn {
            transition: all 0.2s;
        }
        .action-btn:hover {
            transform: scale(1.05);
        }
        /* Consistent input styling */
        #searchInput,
        #filterDistrict,
        #filterDivision,
        #filterGnDivision,
        #filterStatus {
            font-family: "Noto Sans Sinhala", "Noto Sans Tamil", "Roboto", sans-serif;
            font-size: 14px;
            color: #374151;
            height: 42px;
        }
        
        /* Placeholder styling for inputs */
        #searchInput::placeholder {
            color: #9ca3af;
            font-size: 14px;
        }
        
        /* Select dropdown styling to match input */
        #filterDistrict option,
        #filterDivision option,
        #filterGnDivision option,
        #filterStatus option {
            font-size: 14px;
            color: #374151;
            padding: 8px;
        }
        
        /* Placeholder option styling (first option) */
        #filterDistrict option[value=""],
        #filterDivision option[value=""],
        #filterGnDivision option[value=""],
        #filterStatus option[value=""] {
            color: #9ca3af;
            font-size: 14px;
        }

        /* ----- Print Container and Styles (Club Detail Model) ----- */
        #printContainer {
            display: none;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm 10mm 15mm 10mm;
                @bottom-center {
                    content: "Page " counter(page) " of " counter(pages);
                    font-size: 7pt;
                    color: #374151;
                }
            }

            body {
                margin: 0;
                padding: 0;
                background: white;
            }

            /* Hide all main dashboard elements */
            body > *:not(#printContainer) {
                display: none !important;
            }

            .accessibility-fab, .accessibility-panel, header, footer {
                display: none !important;
            }

            /* Show Print Container as normal flow */
            #printContainer {
                display: block !important;
                width: 100%;
                position: static;
            }

            /* Report Header — appears once at top of document */
            .print-header {
                text-align: center;
                margin-bottom: 6px;
                border-bottom: 2px solid #1e3a8a;
                padding-bottom: 3px;
                padding-top: 2px;
                background: #f0f4f8;
                border-radius: 2px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .print-header .dept-name {
                font-size: 7.5pt;
                font-weight: bold;
                color: #1e3a8a;
                text-transform: uppercase;
                margin-bottom: 2px;
                line-height: 1;
                letter-spacing: 0.5px;
            }
            .print-header h1 {
                font-size: 14pt;
                font-weight: 900;
                color: #000;
                text-transform: uppercase;
                margin: 2px 0 3px 0;
                letter-spacing: 0.8px;
                line-height: 1.1;
            }
            .print-header .report-subtitle {
                font-size: 8.5pt;
                font-weight: bold;
                color: #1e3a8a;
                margin-top: 2px;
                background: #f3f4f6;
                padding: 1px 8px;
                border-radius: 10px;
                display: inline-block;
                border: 1px solid #d1d5db;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Table — single continuous table, browser handles page breaks */
            #printContainer table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10pt;
                line-height: 1.2;
                table-layout: fixed;
                margin-top: 2px;
            }

            /* thead repeats automatically on every printed page */
            #printContainer table thead {
                display: table-header-group;
            }
            #printContainer table tbody {
                display: table-row-group;
            }

            /* Never split a row across two pages */
            #printContainer table tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            #printContainer table th {
                background-color: #1e3a8a !important;
                color: white !important;
                font-weight: bold;
                font-size: 10pt;
                padding: 2px 3px;
                border: 0.5px solid #0f3a6d;
                text-align: left;
                line-height: 1.2;
                white-space: normal;
                word-break: break-word;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            #printContainer table td {
                padding: 2px 3px;
                border: 0.5px solid #999;
                font-size: 10pt;
                color: #000;
                line-height: 1.3;
                vertical-align: top;
                word-wrap: break-word;
                overflow-wrap: break-word;
                white-space: normal;
            }
            #printContainer table tr:nth-child(even) {
                background-color: #f5f5f5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Footer - keep together, do not split from last row */
            .print-footer {
                margin-top: 4mm;
                border-top: 1px solid #ccc;
                padding-top: 2mm;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .signatures {
                display: flex;
                justify-content: space-between;
                margin-top: 2mm;
                gap: 10px;
            }
            .sig-block {
                flex: 1;
                text-align: center;
            }
            .sig-line {
                border-bottom: 0.5px solid #000;
                margin-bottom: 2px;
                height: 12px;
            }
            .sig-label {
                font-size: 6pt;
                font-weight: bold;
                text-transform: uppercase;
                color: #000;
                line-height: 1;
            }
        }
        
        
';

// Additional links for Chart.js
$additionalLinks = [
    '<script src="' . htmlspecialchars($basePath ?? '../', ENT_QUOTES, 'UTF-8') . 'assets/js/vendor/chart.min.js"></script>'
];

// Include header
include '../includes/header.php';
?>

<!-- Main Content -->
<main class="container mx-auto px-4 py-8">

    <!-- Statistics Cards -->
    <div id="statisticsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Clubs -->
        <div class="stat-card" style="border-left-color: #3b82f6;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label" data-i18n="stats.total_clubs">Total Clubs</p>
                    <p class="stat-value mt-1" id="statTotalClubs">0</p>
                </div>
                <div class="text-blue-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>
        <!-- District cards will be dynamically inserted here -->
    </div>

    <!-- Action Bar -->
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <h2 class="page-title m-0" data-i18n="nav.dashboard"></h2>
        <div class="flex items-center gap-3 flex-wrap">
            <button onclick="exportToExcel()" class="btn btn-success" title="Export to Excel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span data-i18n="button.export_excel">Excel</span>
            </button>
            <button onclick="printWithDate()" class="btn btn-primary" title="Print Preview">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span data-i18n="button.print">Print</span>
            </button>
            <?php if (isAdmin()): ?>
                <a href="register.php" class="btn btn-primary" data-i18n="nav.register"></a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="section-card mb-6">
        <h2 class="section-heading" data-i18n="search.title">Search &amp; Filter</h2>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-2">
                <label class="form-label" data-i18n="placeholder.search"></label>
                <input type="text" id="searchInput" class="form-input" data-i18n-placeholder="placeholder.search">
            </div>
            <div class="md:col-span-2">
                <label class="form-label" data-i18n="form.district"></label>
                <select id="filterDistrict" class="form-select">
                    <option value="" data-i18n="placeholder.select"></option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="form-label" data-i18n="form.division"></label>
                <select id="filterDivision" class="form-select">
                    <option value="" data-i18n="placeholder.select_district_first"></option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="form-label" data-i18n="form.gn_division"></label>
                <select id="filterGnDivision" class="form-select">
                    <option value="" data-i18n="placeholder.select_division_first"></option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="form-label" data-i18n="form.reorg_status"></label>
                <select id="filterStatus" class="form-select">
                    <option value="" data-i18n="filter.all_statuses"></option>
                    <option value="active" data-i18n="status.active"></option>
                    <option value="expired" data-i18n="status.expired"></option>
                </select>
            </div>
            <div class="md:col-span-2 flex flex-col justify-end gap-2">
                <button onclick="loadClubs()" class="btn btn-primary w-full" data-i18n="button.search"></button>
                <button onclick="resetFilters()" class="btn btn-outline w-full" data-i18n="button.reset"></button>
            </div>
        </div>
    </div>

    <!-- Pagination -->

    <!-- Clubs Table -->
    <div class="data-table-wrapper">
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th data-i18n="table.reg_number"></th>
                        <th data-i18n="table.registration_date"></th>
                        <th data-i18n="table.club_name"></th>
                        <th data-i18n="table.division"></th>
                        <th data-i18n="table.gn_division"></th>
                        <th data-i18n="table.chairman_name"></th>
                        <th data-i18n="table.chairman_address"></th>
                        <th data-i18n="table.secretary_name"></th>
                        <th data-i18n="table.secretary_address"></th>
                        <th data-i18n="table.last_reorg_date"></th>
                        <th data-i18n="table.next_reorg_due_date"></th>
                        <th data-i18n="table.actions"></th>
                    </tr>
                </thead>
                <tbody id="clubsTableBody">
                    <tr>
                        <td colspan="12" class="py-8 text-center text-slate-500">
                            <span data-i18n="message.loading"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Pagination -->
    <div class="flex justify-between items-center mt-4 px-4">
        <!-- Left info (optional later) -->
        <div id="paginationInfo" class="text-sm text-gray-600"></div>

        <!-- Buttons -->
        <div id="pagination" class="flex gap-2"></div>
    </div>
</main>

<!-- Hidden Print Container -->
<div id="printContainer">
    <!-- Pages will be generated dynamically by JavaScript -->
</div>


<?php
// Scripts to include
$scripts = ['../assets/js/dashboard.js'];

// Include footer
include '../includes/footer.php';
?>

<script>
    // API base (relative to public/) so filters and search work
    window.API_BASE = '<?php echo isset($basePath) ? rtrim($basePath, "/") . "api" : "../api"; ?>';
    window.currentUserRole = '<?php echo htmlspecialchars(getCurrentRole() ?? 'admin', ENT_QUOTES, 'UTF-8'); ?>';
</script>
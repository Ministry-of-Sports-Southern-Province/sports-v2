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
        #filterGnDivision {
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
        #filterGnDivision option {
            font-size: 14px;
            color: #374151;
            padding: 8px;
        }
        
        /* Placeholder option styling (first option) */
        #filterDistrict option[value=""],
        #filterDivision option[value=""],
        #filterGnDivision option[value=""] {
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
                margin: 5mm; /* Minimal browser margins for maximum space */
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
            
            /* Hide floating widgets/headers/footers */
            .accessibility-fab, .accessibility-panel, header, footer {
                display: none !important;
            }

            /* Show Print Container */
            #printContainer {
                display: block !important;
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
                z-index: 9999;
            }

            /* Report Page Layout (Classic Certificate Style) */
            .print-page-wrapper {
                width: 100%;
                min-height: 195mm; 
                border: 3px double #1e3a8a;
                padding: 8mm;
                box-sizing: border-box;
                background: white;
                
                /* Layout: Header top, Content middle, Footer bottom */
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .report-content {
                flex-grow: 1;
                width: 100%;
            }

            /* Header */
            .print-header { 
                text-align: center; 
                margin-bottom: 12px; 
                border-bottom: 2px solid #1e3a8a; 
                padding-bottom: 6px; 
            }
            .print-header .dept-name { 
                font-size: 9pt;
                font-weight: bold; 
                color: #4b5563; 
                text-transform: uppercase; 
                margin-bottom: 3px; 
            }
            .print-header h1 { 
                font-size: 16pt;
                font-weight: 900; 
                color: #1e3a8a; 
                text-transform: uppercase; 
                margin: 3px 0; 
                letter-spacing: 0.5px; 
                line-height: 1;
            }
            .print-header .report-subtitle {
                font-size: 10pt;
                font-weight: bold;
                color: #000;
                margin-top: 6px;
                background: #f3f4f6;
                padding: 3px 12px;
                border-radius: 15px;
                display: inline-block;
                border: 1px solid #d1d5db;
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
            
            /* Table Styling - Compact for Maximum Rows; text wraps to avoid column overflow */
            #printContainer table { width: 100%; border-collapse: collapse; font-size: 7pt; margin-top: 8px; table-layout: fixed; line-height: 1.2; }
            #printContainer table th { 
                background-color: #1e3a8a !important; 
                color: white !important; 
                font-weight: bold; 
                font-size: 7pt;
                padding: 3px 4px; 
                border: 1px solid #ccc; 
                text-align: left; 
                line-height: 1.1;
                word-wrap: break-word;
                overflow-wrap: break-word;
                word-break: break-word;
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
            #printContainer table td { 
                padding: 3px 4px; 
                border: 1px solid #ccc; 
                font-size: 7pt; 
                color: #333; 
                line-height: 1.2; 
                vertical-align: top;
                word-wrap: break-word;
                overflow-wrap: break-word;
                word-break: break-word;
                min-width: 0;
            }
            #printContainer table tr:nth-child(even) { background-color: #f9fafb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;}
            
            /* Footer */
            .print-footer { margin-top: 12px; width: 100%; }
            .signatures { display: flex; justify-content: space-between; margin-bottom: 20px; margin-top: 25px; }
            .sig-block { width: 180px; text-align: center; }
            .sig-line { border-bottom: 1px dotted #000; margin-bottom: 4px; height: 15px; }
            .sig-label { font-size: 8pt; font-weight: bold; text-transform: uppercase; color: black; }
            .footer-bottom { border-top: 2px solid #1e3a8a; padding-top: 4px; text-align: center; font-size: 7pt; color: #555; }
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
            <div class="md:col-span-3">
                <label class="form-label" data-i18n="form.division"></label>
                <select id="filterDivision" class="form-select">
                    <option value="" data-i18n="placeholder.select_district_first"></option>
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="form-label" data-i18n="form.gn_division"></label>
                <select id="filterGnDivision" class="form-select">
                    <option value="" data-i18n="placeholder.select_division_first"></option>
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
    <div class="print-page-wrapper">
        <div class="print-header">
            <div class="dept-name" data-i18n="header.department_name">Department of Sports Southern Province</div>
            <h1 data-i18n="page.dashboard_title">Sports Clubs Dashboard</h1>
            <div class="report-subtitle" id="printFilterInfo"></div>
        </div>

        <div class="report-content">
            <table id="printTable">
                <thead>
                    <tr>
                        <th data-i18n="table.no"></th>
                        <th data-i18n="table.reg_number">Reg No.</th>
                        <th data-i18n="table.registration_date"></th>
                        <th data-i18n="table.club_name">Club Name</th>
                        <th data-i18n="table.district">District</th>
                        <th data-i18n="table.division">Division</th>
                        <th data-i18n="table.gn_division">GN Division</th>
                        <th data-i18n="table.chairman_name">Chairman's name</th>
                        <th data-i18n="table.chairman_address">Address</th>
                        <th data-i18n="table.secretary_name">Secretary's name</th>
                        <th data-i18n="table.secretary_address">Address</th>
                        <th data-i18n="table.last_reorg_date">Last Reorg Date</th>
                        <th data-i18n="table.next_reorg_due_date">Next Reorg Due Date</th>
                    </tr>
                </thead>
                <tbody id="printTableBody">
                </tbody>
            </table>
        </div>

        <div class="print-footer">
            <div class="signatures">
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-label" data-i18n="footer.prepared_by">Prepared By</div>
                </div>
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-label" data-i18n="footer.checked_by">Checked By</div>
                </div>
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-label" data-i18n="footer.approved_by">Approved By</div>
                </div>
            </div>
            <div class="footer-bottom">
                <p data-i18n="footer.report_note">This report was generated by the Department of Sports Southern Province</p>
            </div>
        </div>
    </div>
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
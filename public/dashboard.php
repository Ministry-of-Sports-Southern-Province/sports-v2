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
            
            /* Table Styling - Compact for Maximum Rows */
            #printContainer table { width: 100%; border-collapse: collapse; font-size: 7pt; margin-top: 8px; table-layout: fixed; line-height: 1.2; }
            #printContainer table th { 
                background-color: #1e3a8a !important; 
                color: white !important; 
                font-weight: bold; 
                font-size: 7pt;
                padding: 3px; 
                border: 1px solid #ccc; 
                text-align: left; 
                line-height: 1.1;
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
            #printContainer table td { padding: 2px 3px; border: 1px solid #ccc; font-size: 7pt; color: #333; line-height: 1.2; vertical-align: top; }
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
    '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>'
];

// Include header
include '../includes/header.php';
?>

<!-- Main Content -->
<main class="container mx-auto px-4 py-8">

    <!-- Statistics Cards -->
    <div id="statisticsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Clubs -->
        <div class="stat-card rounded-lg shadow-md p-6" style="border-left-color: #3b82f6;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-600" data-i18n="stats.total_clubs">Total Clubs</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2" id="statTotalClubs">0</p>
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

    <!-- Charts Section -->


    <!-- Action Bar -->
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800" data-i18n="nav.dashboard"></h2>
        <div class="flex items-center gap-4">
            <!-- Export Buttons -->
            <button onclick="exportToExcel()" class="action-btn px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow-md font-medium flex items-center gap-2" title="Export to Excel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span data-i18n="button.export_excel">Excel</span>
            </button>
            <button onclick="printWithDate()" class="action-btn px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 shadow-md font-medium flex items-center gap-2" title="Print Preview">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span data-i18n="button.print">Print</span>
            </button>
            <!-- Register Button -->
            <a href="register.php" class="action-btn px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md font-medium" data-i18n="nav.register"></a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="search-card rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2" data-i18n="placeholder.search"></label>
                <input type="text" id="searchInput"
                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    data-i18n-placeholder="placeholder.search">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2" data-i18n="form.district"></label>
                <select id="filterDistrict" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="" data-i18n="placeholder.select"></option>
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2" data-i18n="form.division"></label>
                <select id="filterDivision" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="" data-i18n="placeholder.select_district_first"></option>
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2" data-i18n="form.gn_division"></label>
                <select id="filterGnDivision" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="" data-i18n="placeholder.select_division_first"></option>
                </select>
            </div>
            <div class="md:col-span-2 flex flex-col justify-end gap-2">
                <button onclick="loadClubs()" class="action-btn w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md font-medium" data-i18n="button.search"></button>
                <button onclick="resetFilters()" class="action-btn w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 shadow-md font-medium" data-i18n="button.reset"></button>
            </div>
        </div>
    </div>

    <!-- Clubs Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.reg_number"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.registration_date"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.club_name"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.division"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.gn_division"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.chairman_name"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.chairman_address"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.secretary_name"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.secretary_address"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.last_reorg_date"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.next_reorg_due_date"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.actions"></th>
                    </tr>
                </thead>
                <tbody id="clubsTableBody" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="12" class="px-6 py-8 text-center text-gray-500">
                            <span data-i18n="message.loading"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Hidden Print Container -->
<div id="printContainer">
    <div class="print-page-wrapper">
        <div class="print-header">
            <div class="dept-name" data-i18n="header.department_name">Southern Province Sports Department</div>
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
                <p data-i18n="footer.report_note">This report was generated by the Southern Province Sports Department</p>
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
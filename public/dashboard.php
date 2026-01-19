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
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.club_name"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.district"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.chairman"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.registration_date"></th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.actions"></th>
                    </tr>
                </thead>
                <tbody id="clubsTableBody" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <span data-i18n="message.loading"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php
// Scripts to include
$scripts = ['../assets/js/dashboard.js'];

// Include footer
include '../includes/footer.php';
?>
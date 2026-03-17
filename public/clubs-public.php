<?php

/**
 * Public Club Directory
 * No login required. Shows club name, registration number, district,
 * division and reorganization status. Personal information is never shown.
 */

$isPublicPage = true;
$pageTitle    = 'page.public_clubs_title';
$pageHeading  = 'page.public_clubs_title';
$activePage   = 'public-clubs';

include '../includes/header.php';
?>

<main class="container mx-auto px-4 py-8">

    <!-- Stats bar -->
    <div class="mb-6">
        <div class="mb-5">
            <h2 class="page-title mb-1" data-i18n="page.public_clubs_title">Public Club Directory</h2>
            <p class="text-gray-500 text-sm" data-i18n="public.directory_subtitle">Browse registered sports clubs — no login required.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-blue-100 shadow-sm px-5 py-4 flex items-center gap-4">
                <div class="bg-blue-100 text-blue-600 rounded-xl p-3 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide" data-i18n="stats.total_clubs">Total Clubs</p>
                    <p class="text-2xl font-bold text-gray-800 tabular-nums" id="stat-total">-</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-green-100 shadow-sm px-5 py-4 flex items-center gap-4">
                <div class="bg-green-100 text-green-600 rounded-xl p-3 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide" data-i18n="stats.active_clubs">Active Clubs</p>
                    <p class="text-2xl font-bold text-green-600 tabular-nums" id="stat-active">-</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-red-100 shadow-sm px-5 py-4 flex items-center gap-4">
                <div class="bg-red-100 text-red-500 rounded-xl p-3 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide" data-i18n="stats.expired_clubs">Expired Clubs</p>
                    <p class="text-2xl font-bold text-red-500 tabular-nums" id="stat-expired">-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="section-card mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div class="lg:col-span-2">
                <label class="form-label" data-i18n="button.search">Search</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="pub-search" class="form-input pl-9" data-i18n-placeholder="public.search_placeholder" placeholder="Search by name or reg. number...">
                </div>
            </div>
            <div>
                <label class="form-label" data-i18n="form.district">District</label>
                <select id="pub-district" class="form-select">
                    <option value="" data-i18n="public.all_districts">All Districts</option>
                </select>
            </div>
            <div>
                <label class="form-label" data-i18n="form.division">Division</label>
                <select id="pub-division" class="form-select" disabled>
                    <option value="" data-i18n="public.all_divisions">All Divisions</option>
                </select>
            </div>
            <div class="flex gap-2 items-end">
                <div class="flex-1">
                    <label class="form-label" data-i18n="table.reorg_status">Status</label>
                    <select id="pub-status" class="form-select">
                        <option value="" data-i18n="public.all_statuses">All Statuses</option>
                        <option value="active" data-i18n="status.active">Active</option>
                        <option value="expired" data-i18n="status.expired">Expired</option>
                    </select>
                </div>
                <button id="pub-reset" class="btn btn-secondary flex-shrink-0 px-3 py-2.5" title="Reset filters" data-i18n-title="button.reset">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Results info -->
    <div class="mb-3">
        <p class="text-sm text-gray-500" id="pub-result-info">&nbsp;</p>
    </div>

    <!-- Table (desktop) -->
    <div class="section-card p-0 overflow-x-auto hidden sm:block">
        <table class="w-full text-sm">
            <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b-2 border-blue-100 sticky top-0 z-10">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 w-10" data-i18n="table.no">No</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600" data-i18n="table.club_name">Club Name</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600" data-i18n="table.reg_number">Reg No</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600" data-i18n="table.district">District</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600" data-i18n="table.division">Division</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600" data-i18n="table.reorg_status">Status</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 text-center w-28" data-i18n="table.actions">Actions</th>
                </tr>
            </thead>
            <tbody id="pub-clubs-body">
                <tr>
                    <td colspan="7" class="text-center py-10 text-gray-400" data-i18n="message.loading">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Cards (mobile) -->
    <div id="pub-clubs-cards" class="sm:hidden space-y-3"></div>

    <!-- Pagination -->
    <div class="flex items-center justify-center gap-2 mt-5 flex-wrap" id="pub-pagination" style="display:none!important">
        <button id="pub-prev-btn" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <div id="pub-page-numbers" class="flex gap-1 flex-wrap justify-center"></div>
        <button id="pub-next-btn" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

</main>

<!-- Club Detail Modal -->
<div id="pub-modal" class="fixed inset-0 z-50 flex items-center justify-center" style="display:none!important" aria-modal="true" role="dialog">
    <div id="pub-modal-backdrop" class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
    <div class="relative w-full max-w-lg mx-4 z-10 rounded-2xl overflow-hidden shadow-[0_25px_60px_rgba(0,0,0,0.45)]">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 rounded-lg p-2">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white" data-i18n="public.club_detail_title">Club Details</h3>
            </div>
            <button id="pub-modal-close" class="text-white/70 hover:text-white transition" aria-label="Close">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="bg-white p-6">
            <div class="mb-5 pb-4 border-b border-gray-100">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1" data-i18n="table.club_name">Club Name</p>
                <p id="md-name" class="text-lg font-bold text-gray-900 leading-snug"></p>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                        </svg>
                        <span data-i18n="public.reg_number">Reg No</span>
                    </p>
                    <div class="flex items-center gap-2">
                        <p id="md-reg" class="font-bold text-blue-700 font-mono text-sm"></p>
                        <button id="md-copy-reg" title="Copy registration number" class="ml-auto text-gray-400 hover:text-blue-600 transition flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span data-i18n="public.registration_date">Reg Date</span>
                    </p>
                    <p id="md-date" class="font-semibold text-gray-800"></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span data-i18n="table.district">District</span>
                    </p>
                    <p id="md-district" class="font-semibold text-gray-800"></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <span data-i18n="table.division">Division</span>
                    </p>
                    <p id="md-division" class="font-semibold text-gray-800"></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 col-span-2">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span data-i18n="public.gs_division">Grama Sewa Division</span>
                    </p>
                    <p id="md-gs" class="font-semibold text-gray-800"></p>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide" data-i18n="public.reorg_status">Reorg Status</p>
                <div id="md-status"></div>
            </div>
        </div>
    </div>
</div>

<?php
$scripts = ['../assets/js/clubs-public.js'];
include '../includes/footer.php';
?>
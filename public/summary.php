<?php
$pageTitle = 'page.summary_title';
$pageHeading = 'page.summary_title';
$activePage = 'summary';
include '../includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <h2 class="page-title center" data-i18n="page.summary_title">සාරාංශය</h2>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="stat-card" style="border-left-color: #3b82f6;">
            <div class="stat-label" data-i18n="stats.total_clubs">මුළු සමාජ ගණන</div>
            <div class="stat-value text-blue-600" id="totalClubs">0</div>
        </div>
        <div class="stat-card" style="border-left-color: #059669;">
            <div class="stat-label" data-i18n="stats.active_clubs">සක්රීය සමාජ</div>
            <div class="stat-value text-green-600" id="activeClubs">0</div>
        </div>
        <div class="stat-card" style="border-left-color: #d97706;">
            <div class="stat-label" data-i18n="stats.expired_clubs">කල් ඉකුත් සමාජ</div>
            <div class="stat-value text-amber-600" id="expiredClubs">0</div>
        </div>
        <div class="stat-card" style="border-left-color: #7c3aed;">
            <div class="stat-label" data-i18n="stats.total_reorgs">මුළු ප්‍රතිසංවිධාන</div>
            <div class="stat-value text-purple-600" id="totalReorgs">0</div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="section-card">
            <h3 class="section-heading" data-i18n="stats.by_district">දිස්ත්රික්කය අනුව</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="districtChart"></canvas>
            </div>
        </div>
        <div class="section-card">
            <h3 class="section-heading" data-i18n="stats.active_clubs">සක්රීය සමාජ</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="section-card mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="section-heading" data-i18n="stats.recent_registrations">මෑත ලියාපදිංචි කිරීම්</h3>
            <div class="flex gap-2 flex-wrap">
                <button id="filterMonth" class="filter-btn px-3 py-1 text-sm rounded bg-gray-200 hover:bg-gray-300" data-filter="month" data-i18n="filter.month">මෙම මාසය</button>
                <button id="filter3Months" class="filter-btn px-3 py-1 text-sm rounded bg-gray-200 hover:bg-gray-300" data-filter="3months" data-i18n="filter.3months">3 මාස</button>
                <button id="filterYear" class="filter-btn px-3 py-1 text-sm rounded bg-gray-200 hover:bg-gray-300" data-filter="year" data-i18n="filter.year">මෙම වසර</button>
                <button id="filter5Years" class="filter-btn px-3 py-1 text-sm rounded bg-gray-200 hover:bg-gray-300" data-filter="5years" data-i18n="filter.5years">5 අවුරුද්දු</button>
                <button id="filter10Years" class="filter-btn px-3 py-1 text-sm rounded bg-gray-200 hover:bg-gray-300" data-filter="10years" data-i18n="filter.10years">10 අවුරුද්දු</button>
                <button id="filterAllTime" class="filter-btn px-3 py-1 text-sm rounded bg-blue-500 text-white hover:bg-blue-600" data-filter="alltime" data-i18n="filter.alltime">සම්පූර්ණ කාලය</button>
            </div>
        </div>
        <div class="relative" style="height: 350px;">
            <canvas id="registrationChart"></canvas>
        </div>
    </div>
</main>

<!-- ═══════════════════════════════════════════════════════════════════════════
     GENERATE STATISTICAL REPORT BUTTON — triggers print modal
     (existing summary section above is NOT modified)
════════════════════════════════════════════════════════════════════════════ -->
<div class="container mx-auto px-4 pb-10 flex justify-end">
    <button id="openStatReportBtn"
            class="flex items-center gap-2 px-5 py-2.5 text-white text-sm font-medium rounded-lg shadow active:scale-95 transition-all"
            style="background:#4f46e5;" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'"
            data-i18n="button.generate_stat_report">
        <!-- printer icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        <span data-i18n="button.generate_stat_report">Generate Statistical Report</span>
    </button>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     STATISTICAL REPORT PRINT MODAL
════════════════════════════════════════════════════════════════════════════ -->
<div id="statReportModal" class="hidden fixed inset-0 z-50 overflow-auto" style="background:#f8f9fa;">

    <!-- ── Toolbar (hidden on print) ── -->
    <div id="statReportToolbar" class="sticky top-0 z-10 no-print" style="background:#fff;border-bottom:1px solid #e5e7eb;">
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:10px 24px;justify-content:space-between;">

            <!-- Title -->
            <span style="font-size:14px;font-weight:600;color:#111827;" data-i18n="button.generate_stat_report">Statistical Report</span>

            <!-- Language switcher -->
            <div style="display:flex;gap:3px;" id="prLangSwitcher">
                <button class="pr-lang-btn" data-lang="si" onclick="switchPrLang('si')">සිං</button>
                <button class="pr-lang-btn" data-lang="en" onclick="switchPrLang('en')">EN</button>
                <button class="pr-lang-btn" data-lang="ta" onclick="switchPrLang('ta')">த</button>
            </div>

            <!-- Tab switcher -->
            <div style="display:flex;gap:3px;" id="prTabSwitcher">
                <button class="pr-tab-btn" data-tab="combined"        onclick="switchPrTab('combined')"        data-i18n="report.tab_combined">Combined</button>
                <button class="pr-tab-btn" data-tab="registrations"   onclick="switchPrTab('registrations')"   data-i18n="report.tab_registrations">Registrations</button>
                <button class="pr-tab-btn" data-tab="reorganizations" onclick="switchPrTab('reorganizations')" data-i18n="report.tab_reorganizations">Reorganizations</button>
            </div>

            <!-- Actions -->
            <div style="display:flex;gap:8px;">
                <button id="printReportBtn" class="pr-action-btn" data-i18n="button.print">Print</button>
                <button id="closePrintModalBtn" class="pr-action-btn pr-action-ghost" data-i18n="button.close">Close</button>
            </div>
        </div>
    </div>

    <!-- ── Report content ── -->
    <div id="statReportContent" style="max-width:960px;margin:0 auto;padding:20px 16px;">

        <!-- Loading spinner -->
        <div id="statReportLoading" class="flex flex-col items-center justify-center py-24 gap-4">
            <svg class="animate-spin h-8 w-8" style="color:#6b7280;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p style="color:#9ca3af;font-size:13px;" data-i18n="message.loading">Loading...</p>
        </div>

        <!-- Report body (hidden until data loads) -->
        <div id="statReportBody" class="hidden">

            <!-- ══════════════════════════════════════════════════════════════ -->
            <!-- TAB 1: COMBINED REPORT                                         -->
            <!-- ══════════════════════════════════════════════════════════════ -->
            <div id="prTabCombined" class="pr-page">

                <!-- Header -->
                <div class="pr-header">
                    <div>
                        <div class="pr-org" data-i18n="page.welcome_subtitle">Department of Sports — Southern Province</div>
                        <h1 class="pr-title" data-i18n="report.stat_report_title">Statistical Report — Registration &amp; Reorganization</h1>
                    </div>
                    <div class="pr-meta" id="prDateCombined"></div>
                </div>

                <!-- KPI row -->
                <div class="pr-kpi-row">
                    <div class="pr-kpi" style="border-top-color:#3b82f6;">
                        <div class="pr-kpi-num" style="color:#3b82f6;" id="prTotalClubs">—</div>
                        <div class="pr-kpi-lbl" data-i18n="stats.total_clubs">Total Clubs</div>
                    </div>
                    <div class="pr-kpi" style="border-top-color:#10b981;">
                        <div class="pr-kpi-num" style="color:#10b981;" id="prActiveClubs">—</div>
                        <div class="pr-kpi-lbl" data-i18n="stats.active_clubs">Active</div>
                    </div>
                    <div class="pr-kpi" style="border-top-color:#f59e0b;">
                        <div class="pr-kpi-num" style="color:#f59e0b;" id="prExpiredClubs">—</div>
                        <div class="pr-kpi-lbl" data-i18n="stats.expired_clubs">Expired</div>
                    </div>
                    <div class="pr-kpi" style="border-top-color:#8b5cf6;">
                        <div class="pr-kpi-num" style="color:#8b5cf6;" id="prTotalReorgs">—</div>
                        <div class="pr-kpi-lbl" data-i18n="stats.total_reorgs">Reorganizations</div>
                    </div>
                </div>

                <!-- Yearly charts -->
                <div class="pr-section">
                    <div class="pr-sh" data-i18n="report.yearly_trends">Yearly Trends (Last 5 Years)</div>
                    <div class="pr-grid2">
                        <div>
                            <div class="pr-clbl" data-i18n="report.yearly_registrations">Yearly Registrations</div>
                            <div class="pr-cwrap"><canvas id="printYearlyRegChart"></canvas></div>
                        </div>
                        <div>
                            <div class="pr-clbl" data-i18n="report.yearly_reorgs">Yearly Reorganizations</div>
                            <div class="pr-cwrap"><canvas id="printYearlyReorgChart"></canvas></div>
                        </div>
                    </div>
                </div>

                <!-- Monthly charts -->
                <div class="pr-section">
                    <div class="pr-sh" id="statReportMonthlyHeading" data-i18n="report.monthly_trends">Monthly Trends</div>
                    <div class="pr-grid2">
                        <div>
                            <div class="pr-clbl" data-i18n="report.monthly_registrations">Monthly Registrations</div>
                            <div class="pr-cwrap"><canvas id="printMonthlyRegChart"></canvas></div>
                        </div>
                        <div>
                            <div class="pr-clbl" data-i18n="report.monthly_reorgs">Monthly Reorganizations</div>
                            <div class="pr-cwrap"><canvas id="printMonthlyReorgChart"></canvas></div>
                        </div>
                    </div>
                </div>

                <!-- Comparison + district table -->
                <div class="pr-section">
                    <div class="pr-grid-mixed">
                        <div>
                            <div class="pr-sh" data-i18n="report.comparison_title">Comparison</div>
                            <div class="pr-cwrap" style="height:190px;"><canvas id="printComparisonChart"></canvas></div>
                        </div>
                        <div>
                            <div class="pr-sh" data-i18n="report.district_breakdown">District Breakdown</div>
                            <table class="pr-table">
                                <thead><tr>
                                    <th data-i18n="table.district">District</th>
                                    <th data-i18n="stats.total_clubs">Reg.</th>
                                    <th data-i18n="stats.total_reorgs">Reorg.</th>
                                </tr></thead>
                                <tbody id="printDistrictTableBody"></tbody>
                                <tfoot><tr>
                                    <td data-i18n="table.total">Total</td>
                                    <td id="prDistrictTotalReg">—</td>
                                    <td id="prDistrictTotalReorg">—</td>
                                </tr></tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Signature -->
                <div class="pr-sig">
                    <div class="pr-sig-block"><div class="pr-sig-line"></div><span data-i18n="footer.prepared_by">Prepared By</span></div>
                    <div class="pr-sig-block"><div class="pr-sig-line"></div><span data-i18n="footer.checked_by">Checked By</span></div>
                    <div class="pr-sig-block"><div class="pr-sig-line"></div><span data-i18n="footer.approved_by">Approved By</span></div>
                </div>

            </div><!-- /#prTabCombined -->

            <!-- ══════════════════════════════════════════════════════════════ -->
            <!-- TAB 2: REGISTRATIONS REPORT                                    -->
            <!-- ══════════════════════════════════════════════════════════════ -->
            <div id="prTabRegistrations" class="pr-page hidden">

                <div class="pr-header">
                    <div>
                        <div class="pr-org" data-i18n="page.welcome_subtitle">Department of Sports — Southern Province</div>
                        <h1 class="pr-title" data-i18n="report.reg_stat_report_title">Registration Statistical Report</h1>
                    </div>
                    <div class="pr-meta" id="prDateRegistrations"></div>
                </div>

                <div class="pr-kpi-row">
                    <div class="pr-kpi" style="border-top-color:#3b82f6;">
                        <div class="pr-kpi-num" style="color:#3b82f6;" id="prRegTotalClubs">—</div>
                        <div class="pr-kpi-lbl" data-i18n="stats.total_clubs">Total Clubs</div>
                    </div>
                    <div class="pr-kpi" style="border-top-color:#10b981;">
                        <div class="pr-kpi-num" style="color:#10b981;" id="prRegActiveClubs">—</div>
                        <div class="pr-kpi-lbl" data-i18n="stats.active_clubs">Active</div>
                    </div>
                    <div class="pr-kpi" style="border-top-color:#f59e0b;">
                        <div class="pr-kpi-num" style="color:#f59e0b;" id="prRegExpiredClubs">—</div>
                        <div class="pr-kpi-lbl" data-i18n="stats.expired_clubs">Expired</div>
                    </div>
                </div>

                <div class="pr-section">
                    <div class="pr-grid-mixed">
                        <div>
                            <div class="pr-sh" data-i18n="report.yearly_trends">Yearly Trends</div>
                            <div class="pr-clbl" data-i18n="report.yearly_registrations">Yearly Registrations</div>
                            <div class="pr-cwrap"><canvas id="prRegOnlyYearlyChart"></canvas></div>
                            <div class="pr-sh" style="margin-top:14px;" id="prRegMonthlyHeading" data-i18n="report.monthly_trends">Monthly Trends</div>
                            <div class="pr-clbl" data-i18n="report.monthly_registrations">Monthly Registrations</div>
                            <div class="pr-cwrap"><canvas id="prRegOnlyMonthlyChart"></canvas></div>
                        </div>
                        <div>
                            <div class="pr-sh" data-i18n="report.district_breakdown">District Breakdown</div>
                            <table class="pr-table">
                                <thead><tr>
                                    <th data-i18n="table.district">District</th>
                                    <th data-i18n="stats.total_clubs">Registrations</th>
                                </tr></thead>
                                <tbody id="prRegDistrictTableBody"></tbody>
                                <tfoot><tr>
                                    <td data-i18n="table.total">Total</td>
                                    <td id="prRegDistrictTotal">—</td>
                                </tr></tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="pr-sig">
                    <div class="pr-sig-block"><div class="pr-sig-line"></div><span data-i18n="footer.prepared_by">Prepared By</span></div>
                    <div class="pr-sig-block"><div class="pr-sig-line"></div><span data-i18n="footer.checked_by">Checked By</span></div>
                    <div class="pr-sig-block"><div class="pr-sig-line"></div><span data-i18n="footer.approved_by">Approved By</span></div>
                </div>

            </div><!-- /#prTabRegistrations -->

            <!-- ══════════════════════════════════════════════════════════════ -->
            <!-- TAB 3: REORGANIZATIONS REPORT                                  -->
            <!-- ══════════════════════════════════════════════════════════════ -->
            <div id="prTabReorganizations" class="pr-page hidden">

                <div class="pr-header">
                    <div>
                        <div class="pr-org" data-i18n="page.welcome_subtitle">Department of Sports — Southern Province</div>
                        <h1 class="pr-title" data-i18n="report.reorg_stat_report_title">Reorganization Statistical Report</h1>
                    </div>
                    <div class="pr-meta" id="prDateReorganizations"></div>
                </div>

                <div class="pr-kpi-row">
                    <div class="pr-kpi" style="border-top-color:#8b5cf6;">
                        <div class="pr-kpi-num" style="color:#8b5cf6;" id="prReorgTotalReorgs">—</div>
                        <div class="pr-kpi-lbl" data-i18n="stats.total_reorgs">Total Reorganizations</div>
                    </div>
                </div>

                <div class="pr-section">
                    <div class="pr-grid-mixed">
                        <div>
                            <div class="pr-sh" data-i18n="report.yearly_trends">Yearly Trends</div>
                            <div class="pr-clbl" data-i18n="report.yearly_reorgs">Yearly Reorganizations</div>
                            <div class="pr-cwrap"><canvas id="prReorgOnlyYearlyChart"></canvas></div>
                            <div class="pr-sh" style="margin-top:14px;" id="prReorgMonthlyHeading" data-i18n="report.monthly_trends">Monthly Trends</div>
                            <div class="pr-clbl" data-i18n="report.monthly_reorgs">Monthly Reorganizations</div>
                            <div class="pr-cwrap"><canvas id="prReorgOnlyMonthlyChart"></canvas></div>
                        </div>
                        <div>
                            <div class="pr-sh" data-i18n="report.district_breakdown">District Breakdown</div>
                            <table class="pr-table">
                                <thead><tr>
                                    <th data-i18n="table.district">District</th>
                                    <th data-i18n="stats.total_reorgs">Reorganizations</th>
                                </tr></thead>
                                <tbody id="prReorgDistrictTableBody"></tbody>
                                <tfoot><tr>
                                    <td data-i18n="table.total">Total</td>
                                    <td id="prReorgDistrictTotal">—</td>
                                </tr></tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="pr-sig">
                    <div class="pr-sig-block"><div class="pr-sig-line"></div><span data-i18n="footer.prepared_by">Prepared By</span></div>
                    <div class="pr-sig-block"><div class="pr-sig-line"></div><span data-i18n="footer.checked_by">Checked By</span></div>
                    <div class="pr-sig-block"><div class="pr-sig-line"></div><span data-i18n="footer.approved_by">Approved By</span></div>
                </div>

            </div><!-- /#prTabReorganizations -->

        </div><!-- /#statReportBody -->
    </div><!-- /#statReportContent -->
</div><!-- /#statReportModal -->

<script src="../assets/js/vendor/chart.min.js"></script>
<?php
$scripts = ['../assets/js/summary.js'];
include '../includes/footer.php';
?>

<style>
    /* ══════════════════════════════════════════════════════════════════════════
       STATISTICAL REPORT — CLEAN MINIMAL STYLES
    ══════════════════════════════════════════════════════════════════════════ */

    /* ── Toolbar buttons ─────────────────────────────────────────────────── */
    .pr-action-btn {
        font-size: 13px; font-weight: 500; cursor: pointer;
        padding: 5px 14px; border-radius: 5px;
        border: 1px solid #d1d5db; background: #fff; color: #374151;
    }
    .pr-action-btn:hover { background: #f3f4f6; }
    .pr-action-ghost { background: transparent; color: #6b7280; }
    .pr-action-ghost:hover { background: #f3f4f6; }

    .pr-lang-btn, .pr-tab-btn {
        font-size: 12px; font-weight: 500; cursor: pointer;
        padding: 4px 10px; border-radius: 4px;
        border: 1px solid #e5e7eb; background: #fff; color: #6b7280;
        transition: all .15s;
    }
    .pr-lang-btn:hover, .pr-tab-btn:hover { border-color: #9ca3af; color: #374151; }
    .pr-lang-btn.pr-active, .pr-tab-btn.pr-active {
        background: #1e40af; color: #fff; border-color: #1e40af;
    }

    /* ── Page card ───────────────────────────────────────────────────────── */
    .pr-page {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        overflow: hidden;
    }

    /* ── Header ──────────────────────────────────────────────────────────── */
    .pr-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        padding: 20px 24px 16px;
        border-bottom: 2px solid #1e40af;
    }
    .pr-org {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #9ca3af;
        margin-bottom: 4px;
    }
    .pr-title {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin: 0;
        line-height: 1.3;
    }
    .pr-meta {
        font-size: 11px;
        color: #9ca3af;
        white-space: nowrap;
        padding-bottom: 2px;
    }

    /* ── KPI row ─────────────────────────────────────────────────────────── */
    .pr-kpi-row {
        display: flex;
        gap: 1px;
        background: #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
    }
    .pr-kpi {
        flex: 1;
        background: #fff;
        padding: 14px 16px 12px;
        border-top: 3px solid #e5e7eb;
        text-align: center;
    }
    .pr-kpi-num {
        font-size: 26px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 3px;
    }
    .pr-kpi-lbl {
        font-size: 11px;
        color: #6b7280;
    }

    /* ── Section ─────────────────────────────────────────────────────────── */
    .pr-section {
        padding: 16px 24px;
        border-bottom: 1px solid #f3f4f6;
    }
    .pr-section:last-of-type { border-bottom: none; }
    .pr-sh {
        font-size: 11px;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 5px;
    }

    /* ── Chart helpers ───────────────────────────────────────────────────── */
    .pr-grid2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .pr-grid-mixed {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 20px;
        align-items: start;
    }
    .pr-clbl {
        font-size: 10px;
        color: #9ca3af;
        text-align: center;
        margin-bottom: 4px;
        letter-spacing: .04em;
    }
    .pr-cwrap {
        position: relative;
        height: 190px;
    }
    .pr-cwrap canvas { width: 100% !important; height: 100% !important; }

    /* ── Table ───────────────────────────────────────────────────────────── */
    .pr-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    .pr-table thead th {
        text-align: left;
        font-size: 10px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: 6px 8px;
        border-bottom: 1px solid #e5e7eb;
    }
    .pr-table thead th:not(:first-child) { text-align: right; }
    .pr-table tbody td {
        padding: 5px 8px;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
    }
    .pr-table tbody td:not(:first-child) { text-align: right; }
    .pr-table tbody tr:last-child td { border-bottom: none; }
    .pr-table tfoot td {
        padding: 6px 8px;
        font-weight: 600;
        color: #111827;
        border-top: 1px solid #e5e7eb;
    }
    .pr-table tfoot td:not(:first-child) { text-align: right; }

    /* ── Signature ───────────────────────────────────────────────────────── */
    .pr-sig {
        display: flex;
        justify-content: space-around;
        padding: 16px 24px 20px;
        border-top: 1px solid #f3f4f6;
    }
    .pr-sig-block { text-align: center; }
    .pr-sig-line {
        width: 140px;
        height: 1px;
        background: #d1d5db;
        margin: 0 auto 6px;
    }
    .pr-sig-block span {
        font-size: 10px;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    /* ── Backward-compat alias ───────────────────────────────────────────── */
    .pr-report-page { background: white; border-radius: 6px; overflow: hidden; }

    /* LEGACY CLASSES REMOVED — kept only as comment for reference */
    /* .pr-letterhead */
    /* ── Print: A4 landscape, one page ──────────────────────────────────── */
    @media print {
        @page { size: A4 portrait; margin: 0.5cm; }
        body > *:not(#statReportModal) { display: none !important; }
        #statReportModal { display: block !important; position: static !important; overflow: visible !important; background: white !important; }
        .no-print { display: none !important; }
        #statReportContent { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .pr-page { border: none !important; border-radius: 0 !important; }
        .pr-header { padding: 8px 14px 6px !important; }
        .pr-title { font-size: 14px !important; }
        .pr-org { font-size: 7px !important; }
        .pr-meta { font-size: 8px !important; }
        .pr-kpi-row { gap: 1px !important; }
        .pr-kpi { padding: 6px 10px 5px !important; }
        .pr-kpi-num { font-size: 16px !important; }
        .pr-kpi-lbl { font-size: 7px !important; }
        .pr-section { padding: 6px 14px !important; }
        .pr-sh { font-size: 8px !important; margin-bottom: 4px !important; }
        .pr-grid2 { gap: 8px !important; }
        .pr-grid-mixed { gap: 10px !important; }
        .pr-cwrap { height: 110px !important; }
        #prTabRegistrations .pr-cwrap,
        #prTabReorganizations .pr-cwrap { height: 140px !important; }
        .pr-clbl { font-size: 7px !important; }
        .pr-table { font-size: 8px !important; }
        .pr-table thead th, .pr-table tbody td, .pr-table tfoot td { padding: 2px 6px !important; }
        .pr-table thead th { font-size: 7px !important; }
        .pr-sig { padding: 8px 14px 10px !important; }
        .pr-sig-line { width: 100px !important; }
        .pr-sig-block span { font-size: 7px !important; }
        #statReportModal { page-break-inside: avoid; break-inside: avoid; }
    }
</style>
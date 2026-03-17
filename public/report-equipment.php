<?php
$pageTitle = 'report.type_equipment';
$pageHeading = 'report.type_equipment';
$activePage = 'reports';
include '../includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="mb-4"><a href="reports.php" class="text-blue-600 hover:underline font-medium" data-i18n="button.back">← ආපසු</a></div>

    <div class="section-card mb-6 no-print">
        <h2 class="section-heading" data-i18n="report.type_equipment">උපකරණ ලැයිස්තුව වාර්තාව</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div>
                <label class="form-label">Report Year</label>
                <select id="year" class="form-select" required>
                    <option value="">Select year</option>
                </select>
            </div>
            <div>
                <label class="form-label" data-i18n="report.select_equipment">උපකරණ වර්ගය</label>
                <select id="equipment" class="form-select">
                    <option value="" data-i18n="report.all_equipment">සියලු උපකරණ</option>
                </select>
            </div>
            <div>
                <label class="form-label" data-i18n="form.district">දිස්ත්රික්කය</label>
                <select id="district" class="form-select">
                    <option value="" data-i18n="filter.all_districts">සියලු දිස්ත්රික්ක</option>
                </select>
            </div>
            <div>
                <label class="form-label" data-i18n="filter.division">කොට්ඨාසය</label>
                <select id="division" class="form-select">
                    <option value="" data-i18n="filter.all_divisions">සියලු කොට්ඨාසයන්</option>
                </select>
            </div>
            <div>
                <label class="form-label" data-i18n="filter.gs_division">Grama Sewa Division</label>
                <select id="gsDivision" class="form-select">
                    <option value="" data-i18n="filter.all_gs_divisions">All GS Divisions</option>
                </select>
            </div>
        </div>
        <div class="flex flex-wrap gap-3">
            <button onclick="generateReport()" class="btn btn-primary" data-i18n="button.generate_report">වාර්තාව උත්පාදනය කරන්න</button>
            <button onclick="printReportWithDate()" class="btn btn-success" data-i18n="button.print">මුද්‍රණය කරන්න</button>
        </div>
    </div>

    <div id="reportOutput" class="content-card"></div>
    <div class="flex justify-between items-center mt-4 px-4 no-print">
        <div id="reportPaginationInfo" class="text-sm text-gray-600"></div>
        <div id="reportPagination" class="flex gap-2"></div>
    </div>
</main>

<style>
    @media print {
        @page {
            margin: 10mm;
            size: A4 landscape;
        }

        body {
            background: white;
            font-size: 9pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print,
        nav,
        .gov-header,
        footer,
        .accessibility-fab,
        .mb-4,
        .bg-white.rounded-lg.shadow.p-6.mb-6 {
            display: none !important;
        }

        .container,
        main {
            max-width: none !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #reportOutput {
            box-shadow: none !important;
            border: 2px solid #1e3a8a !important;
            padding: 15px !important;
            min-height: 180mm;
            border-radius: 8px;
            box-sizing: border-box;
            position: relative;
        }

        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1e3a8a;
        }

        .print-header .dept-name {
            font-size: 11pt;
            font-weight: bold;
            color: #4b5563;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .print-header h1 {
            font-size: 22pt;
            font-weight: 900;
            color: #1e3a8a;
            margin: 5px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 10px;
            table-layout: fixed;
        }

        th {
            background-color: #1e3a8a !important;
            color: white !important;
            padding: 6px 5px !important;
            border: 1px solid #1e3a8a !important;
            font-size: 9pt;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        td {
            border: 1px solid #d1d5db !important;
            padding: 6px 5px !important;
            font-size: 9pt;
            color: #000 !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            min-width: 0;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f9fafb !important;
        }

        .print-footer {
            display: flex !important;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 30px;
            padding: 15px 50px 0 50px;
            border-top: 1px solid #eee;
        }

        .signatures {
            display: flex !important;
            justify-content: space-between;
            align-items: flex-end;
            width: 100%;
        }

        .sig-block {
            text-align: center;
            width: 200px;
        }

        .sig-line {
            border-bottom: 1px dotted #1e3a8a;
            margin-bottom: 5px;
            height: 30px;
        }

        .sig-label {
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
        }
    }
</style>

<?php
$scripts = ['../assets/js/report-equipment.js'];
include '../includes/footer.php';
?>
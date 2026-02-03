<?php
$pageTitle = 'report.type_registered';
$pageHeading = 'report.type_registered';
$activePage = 'reports';
include '../includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="mb-4"><a href="reports.php" class="text-blue-600 hover:underline font-medium" data-i18n="button.back">← ආපසු</a></div>

    <div class="section-card mb-6 no-print">
        <h2 class="section-heading" data-i18n="report.type_registered">ලියාපදිංචි සමාජ වාර්තාව</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="form-label" data-i18n="form.district">දිස්ත්රික්කය</label>
                <select id="district" class="form-select">
                    <option value="" data-i18n="filter.all_districts">සියලු දිස්ත්රික්ක</option>
                </select>
            </div>
            <div>
                <label class="form-label" data-i18n="report.date_range">දින පරාසය</label>
                <select id="dateRange" class="form-select">
                    <option value="all" data-i18n="report.all_time">සියලු කාලය</option>
                    <option value="year" data-i18n="report.this_year">මෙම වර්ෂය</option>
                    <option value="month" data-i18n="report.this_month">මෙම මාසය</option>
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

<div id="printContainer" style="display: none;"></div>

<style>
    #printContainer {
        display: none;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            margin: 0;
            padding: 0;
            background: white;
        }

        body>*:not(#printContainer) {
            display: none !important;
        }

        #printContainer {
            display: block !important;
        }

        .print-page {
            border: 2px solid #1e3a8a;
            padding: 4mm 3mm;
            margin: 0;
            page-break-after: always;
            page-break-inside: avoid;
            position: relative;
            box-sizing: border-box;
            width: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 277mm;
        }

        .print-page:last-child {
            page-break-after: auto;
        }

        .page-number-footer {
            position: absolute;
            bottom: 2mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7pt;
            color: #374151;
            font-weight: 500;
        }

        .print-header {
            text-align: center;
            margin-bottom: 4px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 2px;
        }

        .print-header .dept-name {
            font-size: 8pt;
            font-weight: bold;
            color: #4b5563;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .print-header h1 {
            font-size: 14pt;
            font-weight: 900;
            color: #1e3a8a;
            text-transform: uppercase;
            margin: 2px 0;
            letter-spacing: 0.5px;
            line-height: 1;
        }

        .print-header .report-subtitle {
            font-size: 9pt;
            font-weight: bold;
            color: #000;
            margin-top: 4px;
            background: #f3f4f6;
            padding: 2px 10px;
            border-radius: 12px;
            display: inline-block;
            border: 1px solid #d1d5db;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        #printContainer table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.5pt;
            margin-top: 4px;
            margin-bottom: 0;
            table-layout: fixed;
            line-height: 1.1;
            flex: 1;
        }

        #printContainer table thead {
            display: table-header-group;
        }

        #printContainer table tbody {
            display: table-row-group;
        }

        #printContainer table tr {
            page-break-inside: avoid;
        }

        #printContainer table th {
            background-color: #1e3a8a !important;
            color: white !important;
            font-weight: bold;
            font-size: 6.5pt;
            padding: 2px 3px;
            border: 1px solid #1e3a8a;
            text-align: left;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        #printContainer table td {
            padding: 2px 3px;
            border: 1px solid #666;
            font-size: 6.5pt;
            color: #000;
            line-height: 1.1;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 80px;
        }

        #printContainer table tr:nth-child(even) {
            background-color: #f9fafb !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-footer {
            margin-top: auto;
            page-break-inside: avoid;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            margin-top: 20px;
        }

        .sig-block {
            width: 150px;
            text-align: center;
        }

        .sig-line {
            border-bottom: 1px solid #000;
            margin-bottom: 3px;
            height: 20px;
        }

        .sig-label {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
        }
    }
</style>

<?php
$scripts = ['../assets/js/report-registered.js'];
include '../includes/footer.php';
?>
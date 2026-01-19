<?php
$pageTitle = 'report.type_equipment';
$pageHeading = 'report.type_equipment';
$activePage = 'reports';
include '../includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="mb-4"><a href="reports.php" class="text-blue-600" data-i18n="button.back">← ආපසු</a></div>
    
    <div class="bg-white rounded-lg shadow p-6 mb-6 no-print">
        <h2 class="text-2xl font-bold mb-6" data-i18n="report.type_equipment">උපකරණ ලැයිස්තුව වාර්තාව</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium mb-2" data-i18n="report.select_equipment">උපකරණ වර්ගය</label>
                <select id="equipment" class="w-full px-4 py-2 border rounded">
                    <option value="" data-i18n="report.all_equipment">සියලු උපකරණ</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2" data-i18n="form.district">දිස්ත්රික්කය</label>
                <select id="district" class="w-full px-4 py-2 border rounded">
                    <option value="" data-i18n="filter.all_districts">සියලු දිස්ත්රික්ක</option>
                </select>
            </div>
        </div>

        <div class="flex gap-4">
            <button onclick="generateReport()" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" data-i18n="button.generate_report">වාර්තාව උත්පාදනය කරන්න</button>
            <button onclick="window.print()" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700" data-i18n="button.print">මුද්රණය කරන්න</button>
        </div>
    </div>

    <div id="reportOutput" class="bg-white rounded-lg shadow p-6"></div>
</main>

<style>@media print { .no-print { display: none !important; } }</style>

<?php
$scripts = ['../assets/js/report-equipment.js'];
include '../includes/footer.php';
?>

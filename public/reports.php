<?php
$pageTitle = 'nav.reports';
$pageHeading = 'nav.reports';
$activePage = 'reports';
include '../includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold mb-8 text-center" data-i18n="page.reports_title">වාර්තා උත්පාදනය</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="report-reorganized.php" class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition transform hover:-translate-y-1">
            <div class="text-center">
                <div class="text-5xl mb-4">📋</div>
                <h3 class="text-xl font-bold mb-2" data-i18n="report.type_reorganized">ප්රතිසංවිධාන සමාජ</h3>
                <p class="text-gray-600 text-sm" data-i18n="report.desc_reorganized">වර්ෂය සහ දිස්ත්රික්කය අනුව ප්රතිසංවිධාන සමාජ වාර්තාව</p>
            </div>
        </a>

        <a href="report-registered.php" class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition transform hover:-translate-y-1">
            <div class="text-center">
                <div class="text-5xl mb-4">📝</div>
                <h3 class="text-xl font-bold mb-2" data-i18n="report.type_registered">ලියාපදිංචි සමාජ</h3>
                <p class="text-gray-600 text-sm" data-i18n="report.desc_registered">දිස්ත්රික්කය සහ දින පරාසය අනුව ලියාපදිංචි සමාජ වාර්තාව</p>
            </div>
        </a>

        <a href="report-equipment.php" class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition transform hover:-translate-y-1">
            <div class="text-center">
                <div class="text-5xl mb-4">⚽</div>
                <h3 class="text-xl font-bold mb-2" data-i18n="report.type_equipment">උපකරණ ලැයිස්තුව</h3>
                <p class="text-gray-600 text-sm" data-i18n="report.desc_equipment">සමාජ සහ උපකරණ වර්ගය අනුව උපකරණ වාර්තාව</p>
            </div>
        </a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

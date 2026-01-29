<?php
$pageTitle = 'nav.reports';
$pageHeading = 'nav.reports';
$activePage = 'reports';
include '../includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <h2 class="page-title center" data-i18n="page.reports_title">වාර්තා උත්පාදනය</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="report-reorganized.php" class="link-card">
            <div class="text-center">
                <div class="text-5xl mb-4">📋</div>
                <h3 class="link-card-title" data-i18n="report.type_reorganized">ප්‍රතිසංවිධාන සමාජ</h3>
                <p class="link-card-desc" data-i18n="report.desc_reorganized">වර්ෂය සහ දිස්ත්රික්කය අනුව ප්‍රතිසංවිධාන සමාජ වාර්තාව</p>
            </div>
        </a>
        <a href="report-registered.php" class="link-card">
            <div class="text-center">
                <div class="text-5xl mb-4">📝</div>
                <h3 class="link-card-title" data-i18n="report.type_registered">ලියාපදිංචි සමාජ</h3>
                <p class="link-card-desc" data-i18n="report.desc_registered">දිස්ත්රික්කය සහ දින පරාසය අනුව ලියාපදිංචි සමාජ වාර්තාව</p>
            </div>
        </a>
        <a href="report-equipment.php" class="link-card">
            <div class="text-center">
                <div class="text-5xl mb-4">⚽</div>
                <h3 class="link-card-title" data-i18n="report.type_equipment">උපකරණ ලැයිස්තුව</h3>
                <p class="link-card-desc" data-i18n="report.desc_equipment">සමාජ සහ උපකරණ වර්ගය අනුව උපකරණ වාර්තාව</p>
            </div>
        </a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
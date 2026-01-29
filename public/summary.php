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
            <div class="stat-label" data-i18n="stats.total_reorgs">මුළු ප්රතිසංවිධාන</div>
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
        <h3 class="section-heading" data-i18n="stats.recent_registrations">මෑත ලියාපදිංචි කිරීම්</h3>
        <div class="relative" style="height: 350px;">
            <canvas id="registrationChart"></canvas>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php
$scripts = ['../assets/js/summary.js'];
include '../includes/footer.php';
?>

<style>
/* Ensure charts maintain aspect ratio and responsiveness */
canvas {
    max-height: 100%;
    width: 100% !important;
    height: 100% !important;
}

/* Adjust for smaller screens */
@media (max-width: 768px) {
    .relative[style*="height"] {
        height: 250px !important;
    }
}

/* Adjust for very small screens */
@media (max-width: 480px) {
    .relative[style*="height"] {
        height: 220px !important;
    }
}
</style>
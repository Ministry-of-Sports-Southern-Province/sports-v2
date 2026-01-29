<?php
$pageTitle = 'nav.reorganizations';
$pageHeading = 'nav.reorganizations';
$activePage = 'reorganizations';
include '../includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4" data-i18n="search.title">සෙවීම සහ පෙරහන</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" id="searchInput" data-i18n-placeholder="placeholder.search_club_reorg" placeholder="සමාජ නම හෝ ලියාපදිංචි අංකය..." class="px-4 py-2 border rounded">
            <select id="districtFilter" class="px-4 py-2 border rounded">
                <option value="" data-i18n="filter.all_districts">සියලු දිස්ත්රික්ක</option>
            </select>
            <select id="statusFilter" class="px-4 py-2 border rounded">
                <option value="" data-i18n="filter.all_statuses">සියලු තත්ත්වයන්</option>
                <option value="expired" data-i18n="status.expired">කල් ඉකුත්</option>
                <option value="active" data-i18n="status.active">සක්රීය</option>
            </select>
            <button onclick="filterClubs()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" data-i18n="button.search">සෙවීම</button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-i18n="table.reg_number">ලියාපදිංචි අංකය</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-i18n="table.club_name">සමාජ නාමය</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-i18n="table.district">දිස්ත්රික්කය</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-i18n="form.last_reorg_date">අවසාන දිනය</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-i18n="form.reorg_status">තත්ත්වය</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-i18n="table.actions">ක්රියාමාර්ග</th>
                </tr>
            </thead>
            <tbody id="clubsTable" class="bg-white divide-y divide-gray-200">
                <tr><td colspan="7" class="px-6 py-4 text-center" data-i18n="message.loading">පූරණය වෙමින්...</td></tr>
            </tbody>
        </table>
    </div>
    <div class="flex justify-between items-center mt-4 px-4">
        <div id="paginationInfo" class="text-sm text-gray-600"></div>
        <div id="pagination" class="flex gap-2"></div>
    </div>
</main>

<?php
$scripts = ['../assets/js/reorganizations.js'];
include '../includes/footer.php';
?>

<script>
// Make user role available to JavaScript
window.currentUserRole = '<?php echo htmlspecialchars(getCurrentRole() ?? 'admin', ENT_QUOTES, 'UTF-8'); ?>';
</script>

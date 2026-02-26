<?php
$pageTitle = 'nav.reorganizations';
$pageHeading = 'nav.reorganizations';
$activePage = 'reorganizations';
include '../includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="section-card mb-6">
        <h2 class="section-heading" data-i18n="search.title">සෙවීම සහ පෙරහන</h2>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label class="form-label" data-i18n="placeholder.search_club_reorg">සමාජ නම හෝ ලියාපදිංචි අංකය</label>
                <input type="text" id="searchInput" class="form-input" data-i18n-placeholder="placeholder.search_club_reorg" placeholder="සමාජ නම හෝ ලියාපදිංචි අංකය...">
            </div>
            <div>
                <label class="form-label" data-i18n="table.district">දිස්ත්රික්කය</label>
                <select id="districtFilter" class="form-select">
                    <option value="" data-i18n="filter.all_districts">සියලු දිස්ත්රික්ක</option>
                </select>
            </div>
            <div>
                <label class="form-label" data-i18n="form.reorg_status">තත්ත්වය</label>
                <select id="statusFilter" class="form-select">
                    <option value="" data-i18n="filter.all_statuses">සියලු තත්ත්වයන්</option>
                    <option value="expired" data-i18n="status.expired">කල් ඉකුත්</option>
                    <option value="active" data-i18n="status.active">සක්රීය</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="filterClubs()" class="btn btn-primary w-full" data-i18n="button.search">සෙවීම</button>
            </div>
        </div>
    </div>

    <div class="data-table-wrapper">
        <div class="overflow-x-auto">
            <table class="data-table min-w-full">
                <thead>
                    <tr>
                        <th>#</th>
                        <th data-i18n="table.reg_number">ලියාපදිංචි අංකය</th>
                        <th data-i18n="table.club_name">සමාජ නාමය</th>
                        <th data-i18n="table.district">දිස්ත්රික්කය</th>
                        <th data-i18n="form.last_reorg_date">අවසාන දිනය</th>
                        <th data-i18n="form.reorg_status">තත්ත්වය</th>
                        <th data-i18n="table.actions">ක්රියාමාර්ග</th>
                    </tr>
                </thead>
                <tbody id="clubsTable">
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center" data-i18n="message.loading">පූරණය වෙමින්...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="flex justify-between items-center mt-4 px-4">
        <div id="paginationInfo" class="text-sm text-gray-600"></div>
        <div id="pagination" class="flex gap-2"></div>
    </div>
</main>

<?php
$scripts = ['../assets/js/shared-history-modal.js', '../assets/js/reorganizations.js'];
include '../includes/footer.php';
?>

<script>
    // Make user role available to JavaScript
    window.currentUserRole = '<?php echo htmlspecialchars(getCurrentRole() ?? 'admin', ENT_QUOTES, 'UTF-8'); ?>';
</script>
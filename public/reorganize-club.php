<?php

/**
 * Club Reorganization Page
 * Allows updating club information during reorganization
 */

$pageTitle = 'page.reorganize_club';
$pageHeading = 'page.reorganize_club';
$activePage = 'dashboard';

include '../includes/header.php';

// Get club ID from URL
$clubId = $_GET['id'] ?? null;

if (!$clubId) {
    header('Location: dashboard.php');
    exit;
}
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="page-title" data-i18n="page.reorganize_club">Reorganize Club</h1>
            <a href="dashboard.php" class="btn btn-outline">
                <span data-i18n="button.back">Back</span>
            </a>
        </div>

        <!-- Reorganization Form -->
        <form id="reorganizationForm" class="section-card">
            <input type="hidden" id="clubId" value="<?php echo htmlspecialchars($clubId); ?>">

            <!-- Current Information Display -->
            <div id="currentInfo" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="text-lg font-semibold mb-3 text-blue-900" data-i18n="form.current_information">Current Information</h3>
                <div id="currentInfoContent" class="text-sm text-gray-700">
                    <p data-i18n="message.loading">Loading...</p>
                </div>
            </div>

            <!-- Reorganization Date -->
            <div class="form-group">
                <label class="form-label required" data-i18n="form.reorganization_date">Reorganization Date</label>
                <input type="date" id="reorgDate" class="form-input" required>
                <p class="form-help" data-i18n="help.reorganization_date">Date when the club was reorganized</p>
            </div>

            <!-- New Club Information -->
            <h3 class="section-heading" data-i18n="form.new_information">New Information</h3>

            <!-- Club Name -->
            <div class="form-group">
                <label class="form-label required" data-i18n="form.club_name">Club Name</label>
                <input type="text" id="clubName" class="form-input" required>
            </div>

            <!-- Location -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="form-label required" data-i18n="form.district">District</label>
                    <select id="district" class="form-select" disabled style="background-color: #f3f4f6; cursor: not-allowed;">
                        <option value="" data-i18n="placeholder.select">Select</option>
                    </select>
                    <p class="form-help text-xs text-gray-500">District cannot be changed during reorganization</p>
                </div>
                <div class="form-group">
                    <label class="form-label required" data-i18n="form.division">Division</label>
                    <select id="division" class="form-select" required>
                        <option value="" data-i18n="placeholder.select_district_first">Select district first</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label required" data-i18n="form.gs_division">GS Division</label>
                    <select id="gsDivision" class="form-select" required>
                        <option value="" data-i18n="placeholder.select_division_first">Select division first</option>
                    </select>
                </div>
            </div>

            <!-- Chairman Information -->
            <h3 class="section-heading" data-i18n="form.chairman_info">Chairman Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label required" data-i18n="form.chairman_name">Chairman Name</label>
                    <input type="text" id="chairmanName" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label required" data-i18n="form.chairman_phone">Chairman Phone</label>
                    <input type="tel" id="chairmanPhone" class="form-input" maxlength="10" pattern="[0-9]{10}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label required" data-i18n="form.chairman_address">Chairman Address</label>
                <textarea id="chairmanAddress" class="form-input" rows="2" required></textarea>
            </div>

            <!-- Secretary Information -->
            <h3 class="section-heading" data-i18n="form.secretary_info">Secretary Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label required" data-i18n="form.secretary_name">Secretary Name</label>
                    <input type="text" id="secretaryName" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label required" data-i18n="form.secretary_phone">Secretary Phone</label>
                    <input type="tel" id="secretaryPhone" class="form-input" maxlength="10" pattern="[0-9]{10}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label required" data-i18n="form.secretary_address">Secretary Address</label>
                <textarea id="secretaryAddress" class="form-input" rows="2" required></textarea>
            </div>

            <!-- Notes -->
            <div class="form-group">
                <label class="form-label" data-i18n="form.notes">Notes (Optional)</label>
                <textarea id="notes" class="form-input" rows="3" placeholder="Any additional notes about the reorganization"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-3">
                <a href="dashboard.php" class="btn btn-outline" data-i18n="button.cancel">Cancel</a>
                <button type="submit" class="btn btn-primary" data-i18n="button.reorganize">Reorganize Club</button>
            </div>
        </form>

        <!-- Reorganization History -->
        <div class="section-card mt-6">
            <h3 class="section-heading" data-i18n="form.reorganization_history">Reorganization History</h3>
            <div id="historyContainer">
                <p class="text-gray-500" data-i18n="message.loading">Loading...</p>
            </div>
        </div>
    </div>
</main>

<?php
$scripts = ['../assets/js/shared-history-modal.js', '../assets/js/reorganize-club.js'];
include '../includes/footer.php';
?>
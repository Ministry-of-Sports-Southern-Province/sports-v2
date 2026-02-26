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
                    <label class="form-label required" data-i18n="form.gn_division">GN Division</label>
                    <select id="gnDivision" class="form-select" required>
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

<!-- Reorganization History Modal -->
<div id="historyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 hidden" onclick="closeHistoryModal(event)">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900" data-i18n="modal.reorg_details_title">Reorganization Details</h3>
            <button onclick="closeHistoryModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Content -->
        <div class="p-6">
            <!-- Reorganization Date and Notes -->
            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
                <p class="font-semibold text-blue-900">
                    <span data-i18n="modal.reorg_date_label">Reorganization Date:</span>
                    <span id="modalReorgDate"></span>
                </p>
                <div id="modalNotes" class="text-sm text-gray-700 mt-2" style="display:none;">
                    <strong data-i18n="modal.notes_label">Notes:</strong>
                    <span id="modalNotesText"></span>
                </div>
            </div>

            <!-- Previous and Current Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Previous Information -->
                <div class="border rounded-lg p-4 bg-red-50">
                    <h4 class="font-bold text-red-900 mb-4 flex items-center" data-i18n="modal.previous_before">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Previous (Before)
                    </h4>

                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 uppercase" data-i18n="label.club_name">Club Name</p>
                            <p class="text-gray-900" id="modalPrevName"></p>
                        </div>

                        <div class="border-t pt-3">
                            <p class="text-xs font-semibold text-gray-600 uppercase mb-2" data-i18n="label.chairman">Chairman</p>
                            <p class="text-gray-900 font-medium" id="modalPrevChairman"></p>
                            <p class="text-sm text-gray-600 mt-1" id="modalPrevChairmanAddr"></p>
                            <p class="text-sm text-gray-600">📞 <span id="modalPrevChairmanPhone"></span></p>
                        </div>

                        <div class="border-t pt-3">
                            <p class="text-xs font-semibold text-gray-600 uppercase mb-2" data-i18n="label.secretary">Secretary</p>
                            <p class="text-gray-900 font-medium" id="modalPrevSecretary"></p>
                            <p class="text-sm text-gray-600 mt-1" id="modalPrevSecretaryAddr"></p>
                            <p class="text-sm text-gray-600">📞 <span id="modalPrevSecretaryPhone"></span></p>
                        </div>
                    </div>
                </div>

                <!-- Current Information -->
                <div class="border rounded-lg p-4 bg-green-50">
                    <h4 class="font-bold text-green-900 mb-4 flex items-center" data-i18n="modal.current_after">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Current (After)
                    </h4>

                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 uppercase" data-i18n="label.club_name">Club Name</p>
                            <p class="text-gray-900" id="modalCurrentName"></p>
                        </div>

                        <div class="border-t pt-3">
                            <p class="text-xs font-semibold text-gray-600 uppercase mb-2" data-i18n="label.chairman">Chairman</p>
                            <p class="text-gray-900 font-medium" id="modalCurrentChairman"></p>
                            <p class="text-sm text-gray-600 mt-1" data-i18n="label.current_info">Current address and phone</p>
                        </div>

                        <div class="border-t pt-3">
                            <p class="text-xs font-semibold text-gray-600 uppercase mb-2" data-i18n="label.secretary">Secretary</p>
                            <p class="text-gray-900 font-medium" id="modalCurrentSecretary"></p>
                            <p class="text-sm text-gray-600 mt-1" data-i18n="label.current_info">Current address and phone</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="sticky bottom-0 bg-gray-50 border-t px-6 py-4 flex justify-between">
            <button id="deleteModalBtn" onclick="deleteReorganizationFromModal()" class="btn btn-danger">
                <span data-i18n="button.delete_reorg_record">Delete</span>
            </button>
            <button onclick="closeHistoryModal()" class="btn btn-primary" data-i18n="button.close">
                Close
            </button>
        </div>
    </div>
</div>

<?php
$scripts = ['../assets/js/reorganize-club.js'];
include '../includes/footer.php';
?>
<?php

/**
 * Edit Club Page
 * Form for editing existing club information
 */

// Page configuration
$pageTitle = 'heading.edit_club';
$pageHeading = 'heading.edit_club';
$activePage = 'dashboard';
$basePath = '../';

$customStyles = '.ts-wrapper { width: 100% !important; }';

// Additional CSS/JS links for this page
$additionalLinks = [
    '<link href="' . htmlspecialchars($basePath) . 'assets/css/vendor/tom-select.css" rel="stylesheet">'
];

// Require admin role - only admins can edit clubs
require_once '../includes/auth.php';
requireAdmin();

// Include header
include '../includes/header.php';
?>

<!-- Main Content -->
<main class="container mx-auto px-4 py-8 max-w-4xl">
    <form id="editClubForm" class="space-y-6">

        <!-- Location Information Section -->
        <div class="section-card">
            <h2 class="section-heading" data-i18n="form.location_information">ස්ථාන තොරතුරු</h2>
            <p class="text-sm text-amber-600 mb-4" data-i18n="form.select_district_first">⚠️ කරුණාකර මුලින්ම දිස්ත්‍රික්කය තෝරන්න</p>
            <div class="mb-4">
                <label class="form-label" data-i18n="form.district">දිස්ත්‍රික්කය</label>
                <select id="district" name="district" required class="form-select" data-i18n-placeholder="placeholder.select">
                    <option value=""></option>
                </select>
                <span id="districtError" class="form-error hidden"></span>
            </div>
            <div class="mb-4">
                <label class="form-label" data-i18n="form.division">ප්‍රාදේශීය ලේකම් කොට්ඨාසය</label>
                <select id="division" name="division" required class="form-select" data-i18n-placeholder="placeholder.select">
                    <option value=""></option>
                </select>
                <span id="divisionError" class="form-error hidden"></span>
            </div>
            <div class="mb-4">
                <label class="form-label" data-i18n="form.gs_division">Grama Sewa Division</label>
                <select id="gsDivision" name="gsDivision" class="form-select" data-i18n-placeholder="placeholder.select">
                    <option value=""></option>
                </select>
                <span id="gsDivisionError" class="form-error hidden"></span>
            </div>
        </div>

        <!-- Club Information Section -->
        <div class="section-card">
            <h2 class="section-heading" data-i18n="form.club_information">සමාජ තොරතුරු</h2>
            <div class="mb-4">
                <label class="form-label" data-i18n="form.reg_number">ලියාපදිංචි අංකය</label>
                <input type="text" id="regNumberFull" name="regNumber" required class="form-input">
                <p class="form-hint">Registration number can be edited</p>
            </div>
            <div class="mb-4">
                <label class="form-label" data-i18n="form.club_name">ක්‍රීඩා සමාජයේ නම</label>
                <input type="text" id="clubName" name="clubName" required class="form-input" data-i18n-placeholder="placeholder.enter_club_name">
                <span id="clubNameError" class="form-error hidden"></span>
            </div>
        </div>

        <!-- Chairman Information Section -->
        <div class="section-card">
            <h2 class="section-heading" data-i18n="form.chairman_information">සභාපති තොරතුරු</h2>
            <div class="mb-4">
                <label class="form-label" data-i18n="form.chairman_name">සභාපතිගේ නම</label>
                <input type="text" id="chairmanName" name="chairmanName" class="form-input" data-i18n-placeholder="placeholder.enter_name">
                <span id="chairmanNameError" class="form-error hidden"></span>
            </div>
            <div class="mb-4">
                <label class="form-label" data-i18n="form.chairman_address">සභාපතිගේ ලිපිනය</label>
                <textarea id="chairmanAddress" name="chairmanAddress" rows="3" class="form-textarea" data-i18n-placeholder="placeholder.enter_address"></textarea>
                <span id="chairmanAddressError" class="form-error hidden"></span>
            </div>
            <div class="mb-4">
                <label class="form-label" data-i18n="form.chairman_phone">සභාපතිගේ දුරකථන අංකය</label>
                <input type="tel" id="chairmanPhone" name="chairmanPhone" maxlength="10" pattern="[0-9]{10}" class="form-input" data-i18n-placeholder="placeholder.enter_phone">
                <span id="chairmanPhoneError" class="form-error hidden"></span>
            </div>
        </div>

        <!-- Secretary Information Section -->
        <div class="section-card">
            <h2 class="section-heading" data-i18n="form.secretary_information">ලේකම් තොරතුරු</h2>
            <div class="mb-4">
                <label class="form-label" data-i18n="form.secretary_name">ලේකම්ගේ නම</label>
                <input type="text" id="secretaryName" name="secretaryName" class="form-input" data-i18n-placeholder="placeholder.enter_name">
                <span id="secretaryNameError" class="form-error hidden"></span>
            </div>
            <div class="mb-4">
                <label class="form-label" data-i18n="form.secretary_address">ලේකම්ගේ ලිපිනය</label>
                <textarea id="secretaryAddress" name="secretaryAddress" rows="3" class="form-textarea" data-i18n-placeholder="placeholder.enter_address"></textarea>
                <span id="secretaryAddressError" class="form-error hidden"></span>
            </div>
            <div class="mb-4">
                <label class="form-label" data-i18n="form.secretary_phone">ලේකම්ගේ දුරකථන අංකය</label>
                <input type="tel" id="secretaryPhone" name="secretaryPhone" maxlength="10" pattern="[0-9]{10}" class="form-input" data-i18n-placeholder="placeholder.enter_phone">
                <span id="secretaryPhoneError" class="form-error hidden"></span>
            </div>
        </div>

        <!-- Equipment Section -->
        <div class="section-card">
            <h2 class="section-heading" data-i18n="form.equipment_information">ක්‍රීඩා උපකරණ</h2>
            <p class="text-sm text-slate-600 mb-4" data-i18n="form.equipment_note">සමාජයේ ඇති උපකරණ තෝරන්න (අත්‍යවශ්‍ය නොවේ)</p>
            <div>
                <label class="form-label" data-i18n="form.select_equipment">උපකරණ තෝරන්න</label>
                <select id="equipmentSelect" name="equipment[]" multiple class="form-select" data-i18n-placeholder="placeholder.type_to_search">
                </select>
            </div>
            <div id="equipmentList" class="mt-3 space-y-2"></div>
        </div>

        <!-- Registration Date Section -->
        <div class="section-card">
            <h2 class="section-heading" data-i18n="form.registration_date">ලියාපදිංචි දිනය</h2>
            <div class="mb-4">
                <input type="date" id="registrationDate" name="registrationDate" required class="form-input">
                <p class="form-hint">Registration date can be edited</p>
            </div>
        </div>

        <!-- Past Reorganization Dates Section -->
        <div class="section-card">
            <h2 class="section-heading" data-i18n="form.reorganization_dates">පසුගිය ප්‍රතිසංවිධාන දිනයන්</h2>
            <p class="text-sm text-slate-600 mb-4" data-i18n="form.reorganization_note">සමාජය ප්‍රතිසංවිධානය කළ දිනයන් (අත්‍යවශ්‍ය නොවේ)</p>
            <div id="reorgDatesContainer" class="space-y-3">
                <!-- Reorganization dates will be added here dynamically -->
            </div>
            <button type="button" id="addReorgDateBtn" class="btn btn-outline mt-3">
                <span data-i18n="button.add_reorg_date">+ ප්‍රතිසංවිධාන දිනයක් එකතු කරන්න</span>
            </button>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-4 pt-2">
            <button type="button" onclick="window.location.href='dashboard.php'" class="btn btn-outline" data-i18n="button.cancel">අවලංගු කරන්න</button>
            <button type="submit" id="submitBtn" class="btn btn-primary" data-i18n="button.update_club">යාවත්කාලීන කරන්න</button>
        </div>
    </form>
</main>

<?php
// Scripts to include (in order)
$scripts = [
    '../assets/js/vendor/tom-select.complete.min.js',
    '../assets/js/edit-club.js'
];

// Include footer
include '../includes/footer.php';
?>
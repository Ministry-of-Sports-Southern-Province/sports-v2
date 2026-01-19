<?php

/**
 * Registration Page
 * Form for registering new sports clubs
 */

// Page configuration
$pageTitle = 'page.register_title';
$pageHeading = 'page.register_title';
$activePage = 'register';

// Custom styles for this page
$customStyles = '
        .ts-wrapper {
            width: 100% !important;
        }
        .section-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
';

// Additional CSS/JS links for this page
$additionalLinks = [
    '<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">'
];

// Include header
include '../includes/header.php';
?>

<!-- Main Content -->
<main class="container mx-auto px-4 py-8 max-w-4xl">
    <form id="registrationForm" class="space-y-6">

        <!-- Location Information Section -->
        <div class="section-card">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2" data-i18n="form.location_information">ස්ථාන තොරතුරු</h2>
            <p class="text-sm text-amber-600 mb-4" data-i18n="form.select_district_first">⚠️ කරුණාකර මුලින්ම දිස්ත්‍රික්කය තෝරන්න</p>

            <!-- District -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.district">දිස්ත්‍රික්කය</label>
                <select id="district" name="district" required data-i18n-placeholder="placeholder.select">
                    <option value=""></option>
                </select>
                <span id="districtError" class="text-red-600 text-sm mt-1 hidden"></span>
            </div>

            <!-- Division -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.division">ප්‍රාදේශීය ලේකම් කොට්ඨාසය</label>
                <select id="division" name="division" required data-i18n-placeholder="placeholder.select">
                    <option value=""></option>
                </select>
                <span id="divisionError" class="text-red-600 text-sm mt-1 hidden"></span>
            </div>

            <!-- GN Division -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.gn_division">ග්‍රාම නිලධාරී වසම</label>
                <select id="gnDivision" name="gnDivision" required data-i18n-placeholder="placeholder.select">
                    <option value=""></option>
                </select>
                <span id="gnDivisionError" class="text-red-600 text-sm mt-1 hidden"></span>
            </div>
        </div>

        <!-- Club Information Section -->
        <div class="section-card">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2" data-i18n="form.club_information">සමාජ තොරතුරු</h2>

            <!-- Registration Number -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.reg_number">ලියාපදිංචි අංකය</label>
                <div class="flex gap-2">
                    <input type="text" id="regNumberPrefix" readonly
                        class="flex-grow bg-gray-100 border border-gray-300 rounded px-3 py-2 text-gray-700"
                        placeholder="දපස/ක්‍රිඩා/" value="දපස/ක්‍රිඩා/">
                    <input type="text" id="regNumberManual" name="regNumberManual" required disabled
                        class="w-40 border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                        pattern="[0-9]+" data-i18n-placeholder="placeholder.enter_number">
                    <div id="regNumberStatus" class="flex items-center px-2"></div>
                </div>
                <input type="hidden" id="regNumberFull" name="regNumber">
                <p id="regNumberHelper" class="text-xs text-gray-500 mt-1" data-i18n="form.select_district_first_helper">කරුණාකර මුලින්ම දිස්ත්‍රික්කය තෝරන්න</p>
                <span id="regNumberError" class="text-red-600 text-sm mt-1 hidden"></span>
            </div>

            <!-- Club Name -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.club_name">ක්‍රීඩා සමාජයේ නම</label>
                <input type="text" id="clubName" name="clubName" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    data-i18n-placeholder="placeholder.enter_club_name">
                <span id="clubNameError" class="text-red-600 text-sm mt-1 hidden"></span>
            </div>
        </div>

        <!-- Chairman Information Section -->
        <div class="section-card">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2" data-i18n="form.chairman_information">සභාපති තොරතුරු</h2>

            <!-- Chairman Name -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.chairman_name">සභාපතිගේ නම</label>
                <input type="text" id="chairmanName" name="chairmanName" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    data-i18n-placeholder="placeholder.enter_name">
                <span id="chairmanNameError" class="text-red-600 text-sm mt-1 hidden"></span>
            </div>

            <!-- Chairman Address -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.chairman_address">සභාපතිගේ ලිපිනය</label>
                <textarea id="chairmanAddress" name="chairmanAddress" rows="3" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    data-i18n-placeholder="placeholder.enter_address"></textarea>
                <span id="chairmanAddressError" class="text-red-600 text-sm mt-1 hidden"></span>
            </div>

            <!-- Chairman Phone -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.chairman_phone">සභාපතිගේ දුරකථන අංකය</label>
                <input type="tel" id="chairmanPhone" name="chairmanPhone" required maxlength="10" pattern="[0-9]{10}"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    data-i18n-placeholder="placeholder.enter_phone">
                <span id="chairmanPhoneError" class="text-red-600 text-sm mt-1 hidden"></span>
            </div>
        </div>

        <!-- Secretary Information Section -->
        <div class="section-card">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2" data-i18n="form.secretary_information">ලේකම් තොරතුරු</h2>

            <!-- Secretary Name -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.secretary_name">ලේකම්ගේ නම</label>
                <input type="text" id="secretaryName" name="secretaryName" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    data-i18n-placeholder="placeholder.enter_name">
                <span id="secretaryNameError" class="text-red-600 text-sm mt-1 hidden"></span>
            </div>

            <!-- Secretary Address -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.secretary_address">ලේකම්ගේ ලිපිනය</label>
                <textarea id="secretaryAddress" name="secretaryAddress" rows="3" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    data-i18n-placeholder="placeholder.enter_address"></textarea>
                <span id="secretaryAddressError" class="text-red-600 text-sm mt-1 hidden"></span>
            </div>

            <!-- Secretary Phone -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.secretary_phone">ලේකම්ගේ දුරකථන අංකය</label>
                <input type="tel" id="secretaryPhone" name="secretaryPhone" required maxlength="10" pattern="[0-9]{10}"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    data-i18n-placeholder="placeholder.enter_phone">
                <span id="secretaryPhoneError" class="text-red-600 text-sm mt-1 hidden"></span>
            </div>
        </div>

        <!-- Equipment Section -->
        <div class="section-card">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2" data-i18n="form.equipment_information">ක්‍රීඩා උපකරණ</h2>
            <p class="text-sm text-gray-600 mb-4" data-i18n="form.equipment_note">සමාජයේ ඇති උපකරණ තෝරන්න (අත්‍යවශ්‍ය නොවේ)</p>

            <!-- Equipment Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.select_equipment">උපකරණ තෝරන්න</label>
                <select id="equipmentSelect" name="equipment[]" multiple data-i18n-placeholder="placeholder.type_to_search">
                </select>
            </div>

            <div id="equipmentList" class="mt-3 space-y-2">
                <!-- Selected equipment items with quantities will appear here -->
            </div>
        </div>

        <!-- Registration Date Section -->
        <div class="section-card">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2" data-i18n="form.registration_date">ලියාපදිංචි දිනය</h2>

            <!-- Date Options -->
            <div class="mb-4">
                <div class="flex items-center mb-2">
                    <input type="radio" id="dateAuto" name="dateType" value="auto" checked class="mr-2">
                    <label for="dateAuto" class="text-sm text-gray-700" data-i18n="form.date_option_auto">වත්මන් දිනය භාවිතා කරන්න</label>
                </div>
                <div class="flex items-center">
                    <input type="radio" id="dateManual" name="dateType" value="manual" class="mr-2">
                    <label for="dateManual" class="text-sm text-gray-700" data-i18n="form.date_option_manual">පසුගිය ලියාපදිංචි දිනය ඇතුළත් කරන්න</label>
                </div>
            </div>

            <!-- Manual Date Input -->
            <div id="manualDateContainer" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.select_date">දිනය තෝරන්න</label>
                <input type="date" id="registrationDate" name="registrationDate"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <span id="registrationDateError" class="text-red-600 text-sm mt-1 hidden"></span>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4">
            <button type="button" onclick="window.location.href='dashboard.php'"
                class="px-6 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition"
                data-i18n="button.cancel">අවලංගු කරන්න</button>
            <button type="submit" id="submitBtn"
                class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition font-medium"
                data-i18n="button.submit">සමාජය ලියාපදිංචි කරන්න</button>
        </div>
    </form>
</main>

<?php
// Scripts to include (in order)
$scripts = [
    'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js',
    '../assets/js/register.js'
];

// Include footer
include '../includes/footer.php';
?>
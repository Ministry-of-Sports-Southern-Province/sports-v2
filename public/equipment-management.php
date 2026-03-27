<?php

/**
 * Equipment Management Page
 * Allows admins to manage year-wise sports equipment for clubs
 */

// Page configuration
$pageTitle = 'page.equipment_management_title';
$pageHeading = 'page.equipment_management_title';
$activePage = 'equipment-management';

$customStyles = '
    .tab-button {
        padding: 0.75rem 1.5rem;
        border: none;
        background: transparent;
        border-bottom: 3px solid transparent;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .tab-button:hover {
        color: #3b82f6;
    }
    
    .tab-button.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
    
    .equipment-management-container {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
    }
    
    @media (max-width: 1200px) {
        .equipment-management-container {
            grid-template-columns: 1fr;
        }
    }
    
    .form-card {
        background: #fff;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-card h2 {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #3b82f6;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        color: #4b5563;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 1rem;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .btn-group {
        display: flex;
        gap: 0.5rem;
        margin-top: 1.5rem;
    }
    
    .equipment-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 0.375rem;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    
    .equipment-table thead {
        background: #f3f4f6;
        border-bottom: 2px solid #3b82f6;
    }
    
    .equipment-table th {
        padding: 0.75rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
    }
    
    .equipment-table td {
        padding: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
        color: #1f2937;
    }
    
    .equipment-table tbody tr:hover {
        background-color: #f9fafb;
    }
    
    .equipment-item-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .equipment-item-actions button {
        padding: 0.4rem 0.8rem;
        font-size: 0.75rem;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-edit {
        background: #3b82f6;
        color: white;
    }
    
    .btn-edit:hover {
        background: #2563eb;
    }
    
    .btn-delete {
        background: #ef4444;
        color: white;
    }
    
    .btn-delete:hover {
        background: #dc2626;
    }
    
    .year-filter {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }
    
    .year-filter button {
        padding: 0.5rem 1rem;
        border: 1px solid #d1d5db;
        background: #fff;
        border-radius: 0.375rem;
        cursor: pointer;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    
    .year-filter button.active {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
    
    .year-filter button:hover {
        border-color: #3b82f6;
    }
    
    .no-data {
        text-align: center;
        color: #9ca3af;
        padding: 2rem;
    }
    
    .equipment-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-card {
        background: white;
        border-left: 4px solid #3b82f6;
        padding: 1.25rem;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }
    
    .stat-card.card-blue {
        border-left-color: #3b82f6;
    }
    
    .stat-card.card-green {
        border-left-color: #10b981;
    }
    
    .stat-card.card-amber {
        border-left-color: #f59e0b;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #64748b;
        margin-top: 0.5rem;
        font-weight: 500;
    }
    
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 50;
        align-items: center;
        justify-content: center;
    }
    
    .modal.active {
        display: flex;
    }
    
    .modal-content {
        background: white;
        padding: 2rem;
        border-radius: 0.5rem;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
    }
    
    .modal-content h3 {
        margin-bottom: 1rem;
        color: #1e293b;
    }
';

$additionalLinks = [
    '<link href="' . htmlspecialchars($basePath ?? '../', ENT_QUOTES, 'UTF-8') . 'assets/css/vendor/tom-select.css" rel="stylesheet">'
];

require_once '../includes/auth.php';
requireAdmin();

include '../includes/header.php';
?>

<main class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="mb-4 flex justify-between items-center no-print">
        <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 font-medium" data-i18n="button.back">← ආපසු</a>
        <h1 class="text-3xl font-bold" data-i18n="page.equipment_management_title">Equipment Management</h1>
        <div></div>
    </div>

    <!-- Tab Navigation -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="flex gap-4">
            <button id="tabClubEquipment" onclick="switchTab('club-equipment')" class="tab-button active" data-i18n="tab.club_equipment">Club Equipment</button>
            <button id="tabEquipmentTypes" onclick="switchTab('equipment-types')" class="tab-button" data-i18n="tab.equipment_types">Equipment Types</button>
        </nav>
    </div>

    <!-- Tab Content: Club Equipment -->
    <div id="tabContentClubEquipment" class="tab-content active">
        <div class="equipment-management-container">
            <!-- Left Panel: Add Equipment Form -->
            <div class="form-card">
                <h2 data-i18n="form.add_equipment">Add Equipment</h2>

                <div class="form-group">
                    <label data-i18n="form.club_name">Club</label>
                    <select id="clubSelect" class="form-select">
                        <option value="" data-i18n="form.select_club">--- Select Club ---</option>
                    </select>
                </div>

                <div class="form-group">
                    <label data-i18n="form.equipment_type">Equipment Type</label>
                    <select id="equipmentTypeSelect" class="form-select" required>
                        <option value="" data-i18n="form.select_equipment">--- Select Equipment ---</option>
                    </select>
                </div>

                <div class="form-group">
                    <label data-i18n="form.quantity">Quantity</label>
                    <input type="number" id="quantityInput" min="1" value="1" required disabled>
                </div>

                <div class="form-group">
                    <label data-i18n="form.year">Year</label>
                    <input type="number" id="yearInput" min="1900" max="2100" placeholder="YYYY" required disabled>
                </div>

                <button id="addEquipmentBtn" onclick="addEquipment()" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition font-medium" data-i18n="button.add">Add</button>
            </div>

            <!-- Right Panel: Equipment History -->
            <div class="form-card">
                <h2 data-i18n="form.equipment_history">Equipment History</h2>

                <div id="noClubSelected" class="no-data">
                    <p data-i18n="message.select_club_first">Select a club from the form to view equipment history</p>
                </div>

                <div id="equipmentContent" style="display: none;">
                    <div class="equipment-stats" id="statsContainer"></div>

                    <div class="year-filter" id="yearFilterContainer"></div>

                    <table class="equipment-table" id="equipmentTable">
                        <thead>
                            <tr>
                                <th data-i18n="table.year">Year</th>
                                <th data-i18n="table.equipment_type">Equipment Type</th>
                                <th data-i18n="table.quantity">Quantity</th>
                                <th data-i18n="table.added_date">Added Date</th>
                                <th data-i18n="table.actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="equipmentTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content: Equipment Types -->
    <div id="tabContentEquipmentTypes" class="tab-content" style="display: none;">
        <div class="equipment-management-container">
            <!-- Left Panel: Add Equipment Type Form -->
            <div class="form-card">
                <h2 data-i18n="form.add_equipment_type">Add Equipment Type</h2>

                <div class="form-group">
                    <label data-i18n="form.equipment_type_name">Equipment Type Name</label>
                    <input type="text" id="equipmentTypeNameInput" class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="e.g., Cricket Bat, Football" required>
                </div>

                <button onclick="addEquipmentType()" class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition font-medium" data-i18n="button.add_type">Add Type</button>
            </div>

            <!-- Right Panel: Equipment Types List -->
            <div class="form-card">
                <h2 data-i18n="form.equipment_types_list">Equipment Types List</h2>

                <div class="mb-3">
                    <input type="text" id="searchEquipmentTypes" class="w-full px-3 py-2 border border-gray-300 rounded" placeholder="Search equipment types..." onkeyup="searchEquipmentTypes()">
                </div>

                <table class="equipment-table" id="equipmentTypesTable">
                    <thead>
                        <tr>
                            <th data-i18n="table.name">Name</th>
                            <th data-i18n="table.type">Type</th>
                            <th data-i18n="table.actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="equipmentTypesTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Edit Equipment Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3 data-i18n="form.edit_equipment">Edit Equipment</h3>
        <div class="form-group">
            <label data-i18n="form.year">Year</label>
            <input type="number" id="editYearInput" min="1900" max="2100" placeholder="YYYY" required>
        </div>
        <div class="form-group">
            <label data-i18n="form.quantity">Quantity</label>
            <input type="number" id="editQuantityInput" min="1" value="1">
        </div>
        <div class="btn-group">
            <button onclick="saveEquipmentEdit()" class="flex-1 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition" data-i18n="button.save">Save</button>
            <button onclick="closeEditModal()" class="flex-1 px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition" data-i18n="button.cancel">Cancel</button>
        </div>
    </div>
</div>

<!-- Edit Equipment Type Modal -->
<div id="editEquipmentTypeModal" class="modal">
    <div class="modal-content">
        <h3 data-i18n="form.edit_equipment_type">Edit Equipment Type</h3>
        <div class="form-group">
            <label data-i18n="form.equipment_type_name">Equipment Type Name</label>
            <input type="text" id="editEquipmentTypeNameInput" class="w-full px-3 py-2 border border-gray-300 rounded" required>
        </div>
        <div class="btn-group">
            <button onclick="saveEquipmentTypeEdit()" class="flex-1 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition" data-i18n="button.save">Save</button>
            <button onclick="closeEquipmentTypeEditModal()" class="flex-1 px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition" data-i18n="button.cancel">Cancel</button>
        </div>
    </div>
</div>

<?php
$scripts = [
    '../assets/js/vendor/tom-select.complete.min.js',
    '../assets/js/equipment-management.js?v=' . time()
];
include '../includes/footer.php';
?>
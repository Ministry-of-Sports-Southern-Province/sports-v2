<?php

/**
 * Admin Settings Page
 * Manage admin and viewer accounts
 */

// Page configuration
$pageTitle = 'page.admin_settings_title';
$pageHeading = 'page.admin_settings_title';
$activePage = 'admin-settings';

// Require admin role
require_once '../includes/auth.php';
requireAdmin();

// Custom styles for this page
$customStyles = '
    .user-card {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 1.5rem;
    }
    .role-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .role-admin {
        background-color: #dbeafe;
        color: #1e40af;
    }
    .role-viewer {
        background-color: #f3f4f6;
        color: #4b5563;
    }
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-active {
        background-color: #d1fae5;
        color: #065f46;
    }
    .status-inactive {
        background-color: #fee2e2;
        color: #991b1b;
    }
    table tbody tr:hover {
        background-color: #f9fafb;
    }
';

// Include header
include '../includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-3xl font-bold text-gray-800" data-i18n="page.admin_settings_title">User Management</h2>
        <button onclick="openAddUserModal()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md font-medium flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span data-i18n="button.add_user">Add User</span>
        </button>
    </div>

    <!-- Users Table -->
    <div class="user-card">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.username">Username</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.full_name">Full Name</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.email">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.role">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.status">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.last_login">Last Login</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" data-i18n="table.actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <span data-i18n="message.loading">Loading...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Add/Edit User Modal -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800" id="modalTitle" data-i18n="form.add_user">Add User</h3>
                <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="userForm" class="space-y-4">
                <input type="hidden" id="userId" name="id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.username">Username</label>
                    <input type="text" id="username" name="username" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        data-i18n-placeholder="placeholder.enter_username">
                    <span id="usernameError" class="text-red-600 text-sm mt-1 hidden"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.full_name">Full Name</label>
                    <input type="text" id="fullName" name="full_name" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        data-i18n-placeholder="placeholder.enter_full_name">
                    <span id="fullNameError" class="text-red-600 text-sm mt-1 hidden"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.email">Email</label>
                    <input type="email" id="email" name="email"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        data-i18n-placeholder="placeholder.enter_email">
                    <span id="emailError" class="text-red-600 text-sm mt-1 hidden"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.role">Role</label>
                    <select id="role" name="role" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="viewer" data-i18n="role.viewer">Viewer</option>
                        <option value="admin" data-i18n="role.admin">Admin</option>
                    </select>
                </div>

                <div id="passwordField">
                    <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="form.password">Password</label>
                    <input type="password" id="password" name="password"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        data-i18n-placeholder="placeholder.enter_password">
                    <p class="text-xs text-gray-500 mt-1" data-i18n="form.password_optional_edit">Leave blank to keep current password</p>
                    <span id="passwordError" class="text-red-600 text-sm mt-1 hidden"></span>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="isActive" name="is_active" checked
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="isActive" class="ml-2 text-sm text-gray-700" data-i18n="form.is_active">Active</label>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <button type="button" onclick="closeUserModal()"
                        class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition"
                        data-i18n="button.cancel">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition font-medium"
                        data-i18n="button.save">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Scripts to include
$scripts = ['../assets/js/admin-settings.js'];

// Include footer
include '../includes/footer.php';
?>

<script>
// Make current user ID available to JavaScript
window.currentUserId = <?php echo getCurrentAdmin()['id']; ?>;
</script>

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

// Custom styles for this page (badges; card uses global section-card)
$customStyles = '
    .user-card {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e2e8f0;
        padding: 1.5rem 1.75rem;
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
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <h2 class="page-title m-0" data-i18n="page.admin_settings_title">User Management</h2>
        <button onclick="openAddUserModal()" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span data-i18n="button.add_user">Add User</span>
        </button>
    </div>

    <!-- Users Table -->
    <div class="user-card data-table-wrapper">
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th data-i18n="table.username">Username</th>
                        <th data-i18n="table.full_name">Full Name</th>
                        <th data-i18n="table.email">Email</th>
                        <th data-i18n="table.role">Role</th>
                        <th data-i18n="table.status">Status</th>
                        <th data-i18n="table.last_login">Last Login</th>
                        <th data-i18n="table.actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
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
<div id="userModal" class="modal-overlay hidden">
    <div class="modal-panel mx-4">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle" data-i18n="form.add_user">Add User</h3>
            <button type="button" onclick="closeUserModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="userForm" class="space-y-4">
                <input type="hidden" id="userId" name="id">
                <div>
                    <label class="form-label" data-i18n="form.username">Username</label>
                    <input type="text" id="username" name="username" required class="form-input" data-i18n-placeholder="placeholder.enter_username">
                    <span id="usernameError" class="form-error hidden"></span>
                </div>
                <div>
                    <label class="form-label" data-i18n="form.full_name">Full Name</label>
                    <input type="text" id="fullName" name="full_name" required class="form-input" data-i18n-placeholder="placeholder.enter_full_name">
                    <span id="fullNameError" class="form-error hidden"></span>
                </div>
                <div>
                    <label class="form-label" data-i18n="form.email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" data-i18n-placeholder="placeholder.enter_email">
                    <span id="emailError" class="form-error hidden"></span>
                </div>
                <div>
                    <label class="form-label" data-i18n="form.role">Role</label>
                    <select id="role" name="role" required class="form-select">
                        <option value="viewer" data-i18n="role.viewer">Viewer</option>
                        <option value="admin" data-i18n="role.admin">Admin</option>
                    </select>
                </div>
                <div id="passwordField">
                    <label class="form-label" data-i18n="form.password">Password</label>
                    <input type="password" id="password" name="password" class="form-input" data-i18n-placeholder="placeholder.enter_password">
                    <p class="form-hint" data-i18n="form.password_optional_edit">Leave blank to keep current password</p>
                    <span id="passwordError" class="form-error hidden"></span>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="isActive" name="is_active" checked class="w-4 h-4 text-blue-600 rounded">
                    <label for="isActive" class="text-sm text-slate-700" data-i18n="form.is_active">Active</label>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" onclick="closeUserModal()" class="btn btn-outline" data-i18n="button.cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary" data-i18n="button.save">Save</button>
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

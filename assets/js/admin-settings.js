document.addEventListener("DOMContentLoaded", function () {
  loadUsers();
  setupFormSubmission();
});

/**
 * Load all users
 */
function loadUsers() {
  fetch("../api/admin-users.php")
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        displayUsers(data.data);
      } else {
        showError(data.message || "Failed to load users");
      }
    })
    .catch((err) => {
      console.error(err);
      showError("Error loading users");
    });
}

/**
 * Display users in table
 */
function displayUsers(users) {
  const tbody = document.getElementById("usersTableBody");

  if (users.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500"><span data-i18n="table.no_data">No data available</span></td></tr>';
    if (window.i18n && typeof window.i18n.updateContent === "function") {
      window.i18n.updateContent();
    }
    return;
  }

  tbody.innerHTML = users
    .map((user) => {
      const roleClass = user.role === "admin" ? "role-admin" : "role-viewer";
      const statusClass = user.is_active
        ? "status-active"
        : "status-inactive";
      const statusText = user.is_active ? "Active" : "Inactive";
      const lastLogin = user.last_login
        ? new Date(user.last_login).toLocaleDateString()
        : "Never";
      const isCurrentUser = window.currentUserId == user.id;

      return `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${escapeHtml(
                  user.username
                )}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${escapeHtml(
                  user.full_name
                )}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${escapeHtml(
                  user.email || "-"
                )}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span class="role-badge ${roleClass}">${escapeHtml(
        user.role
      )}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${lastLogin}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex items-center gap-3">
                        <button onclick="editUser(${user.id})" 
                                class="text-blue-600 hover:text-blue-800 transition" 
                                title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        ${
                          !isCurrentUser
                            ? `
                        <button onclick="deleteUser(${user.id}, '${escapeHtml(
                              user.username
                            ).replace(/'/g, "\\'")}')" 
                                class="text-red-600 hover:text-red-800 transition" 
                                title="Delete">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                        `
                            : ""
                        }
                    </div>
                </td>
            </tr>
        `;
    })
    .join("");

  // Update translations for dynamically added content
  if (window.i18n && typeof window.i18n.updateContent === "function") {
    window.i18n.updateContent();
  }
}

/**
 * Open add user modal
 */
function openAddUserModal() {
  const modal = document.getElementById("userModal");
  const form = document.getElementById("userForm");
  const title = document.getElementById("modalTitle");
  const passwordField = document.getElementById("passwordField");

  // Reset form
  form.reset();
  document.getElementById("userId").value = "";
  clearErrors();

  // Set modal title
  if (window.i18n) {
    title.setAttribute("data-i18n", "form.add_user");
    title.textContent = window.i18n.t("form.add_user") || "Add User";
  } else {
    title.textContent = "Add User";
  }

  // Show password field and make it required for new users
  passwordField.style.display = "block";
  document.getElementById("password").required = true;

  // Show modal
  modal.classList.remove("hidden");
}

/**
 * Edit user
 */
function editUser(userId) {
  fetch(`../api/admin-users.php`)
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        const user = data.data.find((u) => u.id == userId);
        if (user) {
          openEditUserModal(user);
        } else {
          showError("User not found");
        }
      } else {
        showError(data.message || "Failed to load user");
      }
    })
    .catch((err) => {
      console.error(err);
      showError("Error loading user");
    });
}

/**
 * Open edit user modal
 */
function openEditUserModal(user) {
  const modal = document.getElementById("userModal");
  const form = document.getElementById("userForm");
  const title = document.getElementById("modalTitle");
  const passwordField = document.getElementById("passwordField");

  // Populate form
  document.getElementById("userId").value = user.id;
  document.getElementById("username").value = user.username;
  document.getElementById("fullName").value = user.full_name;
  document.getElementById("email").value = user.email || "";
  document.getElementById("role").value = user.role;
  document.getElementById("isActive").checked = user.is_active == 1;
  document.getElementById("password").value = "";

  clearErrors();

  // Set modal title
  if (window.i18n) {
    title.setAttribute("data-i18n", "form.edit_user");
    title.textContent = window.i18n.t("form.edit_user") || "Edit User";
  } else {
    title.textContent = "Edit User";
  }

  // Hide password field for edit (optional)
  passwordField.style.display = "block";
  document.getElementById("password").required = false;

  // Disable username field for edit
  document.getElementById("username").disabled = true;

  // Show modal
  modal.classList.remove("hidden");
}

/**
 * Close user modal
 */
function closeUserModal() {
  const modal = document.getElementById("userModal");
  const form = document.getElementById("userForm");

  modal.classList.add("hidden");
  form.reset();
  clearErrors();
  document.getElementById("username").disabled = false;
}

/**
 * Setup form submission
 */
function setupFormSubmission() {
  const form = document.getElementById("userForm");

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const userId = document.getElementById("userId").value;
    const isEdit = userId !== "";

    const formData = {
      username: document.getElementById("username").value.trim(),
      full_name: document.getElementById("fullName").value.trim(),
      email: document.getElementById("email").value.trim(),
      role: document.getElementById("role").value,
      is_active: document.getElementById("isActive").checked ? 1 : 0,
    };

    // Only include password if provided
    const password = document.getElementById("password").value;
    if (password || !isEdit) {
      formData.password = password;
    }

    if (isEdit) {
      formData.id = userId;
    }

    // Validate
    if (!formData.username && !isEdit) {
      showFieldError("username", "Username is required");
      return;
    }

    if (!formData.full_name) {
      showFieldError("fullName", "Full name is required");
      return;
    }

    if (formData.email && !isValidEmail(formData.email)) {
      showFieldError("email", "Invalid email format");
      return;
    }

    if (!formData.password && !isEdit) {
      showFieldError("password", "Password is required");
      return;
    }

    // Submit
    const url = "../api/admin-users.php";
    const method = isEdit ? "PUT" : "POST";

    fetch(url, {
      method: method,
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(formData),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          closeUserModal();
          loadUsers();
          showSuccess(
            isEdit
              ? "User updated successfully"
              : "User created successfully"
          );
        } else {
          showError(data.message || "Operation failed");
        }
      })
      .catch((err) => {
        console.error(err);
        showError("An error occurred");
      });
  });
}

/**
 * Delete user
 */
function deleteUser(userId, username) {
  const confirmMessage = window.i18n
    ? window.i18n.t("message.confirm_delete_user") + "\n\n" + username
    : `Are you sure you want to delete this user?\n\n${username}`;

  if (!confirm(confirmMessage)) {
    return;
  }

  fetch(`../api/admin-users.php?id=${userId}`, {
    method: "DELETE",
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        loadUsers();
        showSuccess("User deleted successfully");
      } else {
        showError(data.message || "Failed to delete user");
      }
    })
    .catch((err) => {
      console.error(err);
      showError("An error occurred while deleting user");
    });
}

/**
 * Show field error
 */
function showFieldError(fieldId, message) {
  const errorElement = document.getElementById(fieldId + "Error");
  if (errorElement) {
    errorElement.textContent = message;
    errorElement.classList.remove("hidden");
  }
}

/**
 * Clear all errors
 */
function clearErrors() {
  const errorElements = document.querySelectorAll("[id$='Error']");
  errorElements.forEach((el) => {
    el.classList.add("hidden");
    el.textContent = "";
  });
}

/**
 * Show success message
 */
function showSuccess(message) {
  alert(message);
}

/**
 * Show error message
 */
function showError(message) {
  alert(message);
}

/**
 * Validate email
 */
function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  };
  return text ? String(text).replace(/[&<>"']/g, (m) => map[m]) : "";
}

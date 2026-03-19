/**
 * Equipment Management JavaScript
 * Handles year-wise equipment tracking for sports clubs
 */

let currentClubId = null;
let currentEquipmentData = [];
let selectedYearFilter = "all";
let clubSelectTom = null;
const preselectedClubId = new URLSearchParams(window.location.search).get(
  "club_id",
);

document.addEventListener("DOMContentLoaded", function () {
  // Set today's date as default in date input
  document.getElementById("dateInput").valueAsDate = new Date();

  // Load initial data
  loadClubs();
  loadEquipmentTypes();

  // Club selection event
  document.getElementById("clubSelect").addEventListener("change", function () {
    if (clubSelectTom) {
      return;
    }

    currentClubId = this.value;
    if (currentClubId) {
      loadEquipmentHistory(currentClubId);
    } else {
      resetEquipmentDisplay();
    }
  });
});

/**
 * Load list of all clubs
 */
function loadClubs() {
  fetch("../api/clubs-list.php")
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        const select = document.getElementById("clubSelect");
        // Keep the default option
        data.data.forEach((club) => {
          const opt = document.createElement("option");
          opt.value = club.id;
          opt.textContent = `${club.name} (${club.reg_number})`;
          select.appendChild(opt);
        });

        initializeClubSearch();

        if (preselectedClubId) {
          select.value = preselectedClubId;
          currentClubId = preselectedClubId;
          if (clubSelectTom) {
            clubSelectTom.setValue(preselectedClubId, true);
          }
          loadEquipmentHistory(preselectedClubId);
        }
      }
    })
    .catch((err) => console.error("Error loading clubs:", err));
}

/**
 * Initialize searchable club dropdown
 */
function initializeClubSearch() {
  const selectElement = document.getElementById("clubSelect");

  if (typeof TomSelect === "undefined" || !selectElement) {
    return;
  }

  if (clubSelectTom) {
    clubSelectTom.destroy();
  }

  clubSelectTom = new TomSelect(selectElement, {
    create: false,
    maxOptions: 500,
    searchField: ["text"],
    placeholder: window.i18n
      ? window.i18n.t("form.select_club")
      : "Select Club",
    allowEmptyOption: true,
  });

  clubSelectTom.on("change", function (value) {
    currentClubId = value;
    if (currentClubId) {
      loadEquipmentHistory(currentClubId);
    } else {
      resetEquipmentDisplay();
    }
  });
}

/**
 * Load equipment types for the dropdown
 */
function loadEquipmentTypes() {
  fetch("../api/equipment-types.php")
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        const select = document.getElementById("equipmentTypeSelect");
        data.data.forEach((eq) => {
          const opt = document.createElement("option");
          opt.value = eq.id;
          opt.textContent = eq.name;
          select.appendChild(opt);
        });
      }
    })
    .catch((err) => console.error("Error loading equipment types:", err));
}

/**
 * Load equipment history for a club
 */
function loadEquipmentHistory(clubId) {
  fetch(`../api/equipment-management.php?club_id=${clubId}&include_year=1`)
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        currentEquipmentData = data.data;
        selectedYearFilter = "all";
        renderEquipmentHistory();
        showEquipmentContent();
      } else {
        showEquipmentContent();
        renderEquipmentHistory();
      }
    })
    .catch((err) => {
      console.error("Error loading equipment:", err);
      showEquipmentContent();
    });
}

/**
 * Render equipment history with filters
 */
function renderEquipmentHistory() {
  const tbody = document.getElementById("equipmentTableBody");
  const statsContainer = document.getElementById("statsContainer");
  const yearFilterContainer = document.getElementById("yearFilterContainer");

  // Extract unique years from equipment data
  const years = [...new Set(currentEquipmentData.map((e) => e.year))].sort(
    (a, b) => b - a,
  );

  // Render year filter buttons
  yearFilterContainer.innerHTML =
    '<label style="width: 100%; font-weight: 600; color: #4b5563; margin-bottom: 0.5rem;">Filter by Year:</label>';
  const allBtn = document.createElement("button");
  allBtn.textContent = "All Years";
  allBtn.className = selectedYearFilter === "all" ? "active" : "";
  allBtn.onclick = () => filterByYear("all");
  yearFilterContainer.appendChild(allBtn);

  years.forEach((year) => {
    const btn = document.createElement("button");
    btn.textContent = year;
    btn.className = selectedYearFilter === year.toString() ? "active" : "";
    btn.onclick = () => filterByYear(year);
    yearFilterContainer.appendChild(btn);
  });

  // Filter equipment based on selected year
  let filteredData = currentEquipmentData;
  if (selectedYearFilter !== "all") {
    filteredData = currentEquipmentData.filter(
      (e) => e.year.toString() === selectedYearFilter,
    );
  }

  // Calculate stats
  const totalEquipment = filteredData.length;
  const equipmentTypes = [
    ...new Set(filteredData.map((e) => e.equipment_type_id)),
  ].length;
  const totalQuantity = filteredData.reduce(
    (sum, e) => sum + parseInt(e.quantity),
    0,
  );

  // Render stats
  statsContainer.innerHTML = `
    <div class="stat-card card-blue">
      <div class="stat-value">${totalEquipment}</div>
      <div class="stat-label" data-i18n="stat.total_entries">Total Entries</div>
    </div>
    <div class="stat-card card-green">
      <div class="stat-value">${equipmentTypes}</div>
      <div class="stat-label" data-i18n="stat.equipment_types">Equipment Types</div>
    </div>
    <div class="stat-card card-amber">
      <div class="stat-value">${totalQuantity}</div>
      <div class="stat-label" data-i18n="stat.total_quantity">Total Quantity</div>
    </div>
  `;

  // Render equipment table
  if (filteredData.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="5" class="text-center text-gray-500 py-4" data-i18n="message.no_equipment">No equipment found</td></tr>';
  } else {
    tbody.innerHTML = filteredData
      .map(
        (eq, idx) => `
      <tr>
        <td>${eq.year}</td>
        <td>${escapeHtml(eq.equipment_name)}</td>
        <td>${eq.quantity}</td>
        <td>${formatDate(eq.created_at)}</td>
        <td>
          <div class="equipment-item-actions">
            <button class="btn-edit" onclick="openEditModal(${eq.id}, ${eq.quantity})" data-i18n="button.edit">Edit</button>
            <button class="btn-delete" onclick="deleteEquipment(${eq.id})" data-i18n="button.delete">Delete</button>
          </div>
        </td>
      </tr>
    `,
      )
      .join("");
  }

  // Apply translations to new elements
  if (window.i18n && window.i18n.applyTranslations) {
    setTimeout(() => window.i18n.applyTranslations(), 0);
  }
}

/**
 * Filter equipment by year
 */
function filterByYear(year) {
  selectedYearFilter = year;
  renderEquipmentHistory();
}

/**
 * Show equipment content section
 */
function showEquipmentContent() {
  document.getElementById("noClubSelected").style.display = "none";
  document.getElementById("equipmentContent").style.display = "block";
}

/**
 * Reset equipment display
 */
function resetEquipmentDisplay() {
  currentEquipmentData = [];
  selectedYearFilter = "all";
  document.getElementById("noClubSelected").style.display = "block";
  document.getElementById("equipmentContent").style.display = "none";
}

/**
 * Add new equipment
 */
function addEquipment() {
  const clubId = document.getElementById("clubSelect").value;
  const equipmentTypeId = document.getElementById("equipmentTypeSelect").value;
  const quantity = document.getElementById("quantityInput").value;
  const date = document.getElementById("dateInput").value;

  // Validate input
  if (!clubId || !equipmentTypeId || !quantity) {
    alert(
      window.i18n
        ? window.i18n.t("message.fill_all_fields")
        : "Please fill all fields",
    );
    return;
  }

  if (quantity < 1) {
    alert(
      window.i18n
        ? window.i18n.t("message.invalid_quantity")
        : "Quantity must be at least 1",
    );
    return;
  }

  // Format date to include time
  const dateObj = new Date(date);
  const formattedDate = dateObj.toISOString().split("T")[0] + " 12:00:00";

  // Send request
  fetch("../api/equipment-management.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      club_id: clubId,
      equipment_type_id: equipmentTypeId,
      quantity: parseInt(quantity),
      date: formattedDate,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        // Reset form
        document.getElementById("quantityInput").value = "1";
        document.getElementById("dateInput").valueAsDate = new Date();
        document.getElementById("equipmentTypeSelect").value = "";

        // Reload equipment history
        loadEquipmentHistory(clubId);

        // Show success message
        alert(
          window.i18n
            ? window.i18n.t("message.equipment_added_success")
            : "Equipment added successfully",
        );
      } else {
        alert(data.message || "Failed to add equipment");
      }
    })
    .catch((err) => {
      console.error("Error:", err);
      alert(
        window.i18n
          ? window.i18n.t("message.error_generic")
          : "An error occurred",
      );
    });
}

/**
 * Open edit modal for equipment
 */
let editingEquipmentId = null;

function openEditModal(equipmentId, currentQuantity) {
  editingEquipmentId = equipmentId;
  document.getElementById("editQuantityInput").value = currentQuantity;
  document.getElementById("editModal").classList.add("active");
}

/**
 * Close edit modal
 */
function closeEditModal() {
  document.getElementById("editModal").classList.remove("active");
  editingEquipmentId = null;
}

/**
 * Save equipment edit
 */
function saveEquipmentEdit() {
  if (!editingEquipmentId) return;

  const quantity = document.getElementById("editQuantityInput").value;

  if (!quantity || quantity < 1) {
    alert(
      window.i18n
        ? window.i18n.t("message.invalid_quantity")
        : "Quantity must be at least 1",
    );
    return;
  }

  fetch("../api/equipment-management.php", {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      id: editingEquipmentId,
      quantity: parseInt(quantity),
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        closeEditModal();
        loadEquipmentHistory(currentClubId);
        alert(
          window.i18n
            ? window.i18n.t("message.equipment_updated_success")
            : "Equipment updated successfully",
        );
      } else {
        alert(data.message || "Failed to update equipment");
      }
    })
    .catch((err) => {
      console.error("Error:", err);
      alert(
        window.i18n
          ? window.i18n.t("message.error_generic")
          : "An error occurred",
      );
    });
}

/**
 * Delete equipment
 */
function deleteEquipment(equipmentId) {
  if (
    !confirm(
      window.i18n
        ? window.i18n.t("message.confirm_delete_equipment")
        : "Are you sure you want to delete this equipment?",
    )
  ) {
    return;
  }

  fetch("../api/equipment-management.php", {
    method: "DELETE",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id: equipmentId }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        loadEquipmentHistory(currentClubId);
        alert(
          window.i18n
            ? window.i18n.t("message.equipment_deleted_success")
            : "Equipment deleted successfully",
        );
      } else {
        alert(data.message || "Failed to delete equipment");
      }
    })
    .catch((err) => {
      console.error("Error:", err);
      alert(
        window.i18n
          ? window.i18n.t("message.error_generic")
          : "An error occurred",
      );
    });
}

/**
 * Utility: Format date
 */
function formatDate(dateString) {
  if (!dateString) return "-";
  const date = new Date(dateString);
  return date.toLocaleDateString("si-LK");
}

/**
 * Utility: Escape HTML
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

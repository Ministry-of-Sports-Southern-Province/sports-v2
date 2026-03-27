/**
 * Equipment Management JavaScript
 * Handles year-wise equipment tracking for sports clubs
 */

let currentClubId = null;
let currentEquipmentData = [];
let selectedYearFilter = "all";
let clubSelectTom = null;
let equipmentTypeTom = null;
let allEquipmentTypes = [];
let editingEquipmentTypeId = null;
const preselectedClubId = new URLSearchParams(window.location.search).get(
  "club_id",
);

document.addEventListener("DOMContentLoaded", function () {
  // Set current year as default in year input
  document.getElementById("yearInput").value = new Date().getFullYear();

  // Disable quantity/year until club/type selected
  document.getElementById("quantityInput").disabled = true;
  document.getElementById("yearInput").disabled = true;

  // Load initial data
  loadClubs();
  initializeEquipmentTypeSelect();
  loadAllEquipmentTypes();

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
  initializeClubSearch();

  if (preselectedClubId) {
    loadPreselectedClub(preselectedClubId);
  }

  // Fallback: if Tom Select is unavailable, load full list into native select.
  if (!clubSelectTom) {
    fetch("../api/clubs-list.php?print_all=1")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const select = document.getElementById("clubSelect");
          data.data.forEach((club) => {
            const opt = document.createElement("option");
            opt.value = club.id;
            opt.textContent = `${club.name} (${club.reg_number})`;
            select.appendChild(opt);
          });
        }
      })
      .catch((err) => console.error("Error loading clubs:", err));
  }
}

/**
 * Load and select club for deep-linking (?club_id=)
 */
function loadPreselectedClub(clubId) {
  fetch(`../api/clubs.php?id=${encodeURIComponent(clubId)}`)
    .then((res) => res.json())
    .then((data) => {
      if (!data.success || !data.data) {
        return;
      }

      const club = data.data;
      const clubIdValue = String(club.id);
      const clubText = `${club.name} (${club.reg_number})`;

      if (clubSelectTom) {
        clubSelectTom.addOption({ value: clubIdValue, text: clubText });
        clubSelectTom.setValue(clubIdValue, true);
      } else {
        const select = document.getElementById("clubSelect");
        const existing = Array.from(select.options).find(
          (opt) => opt.value === clubIdValue,
        );
        if (!existing) {
          const opt = document.createElement("option");
          opt.value = clubIdValue;
          opt.textContent = clubText;
          select.appendChild(opt);
        }
        select.value = clubIdValue;
      }

      currentClubId = clubIdValue;
      loadEquipmentHistory(clubIdValue);
      handleEquipmentTypeChange(
        equipmentTypeTom ? equipmentTypeTom.getValue() : null,
      );
    })
    .catch((err) => console.error("Error loading preselected club:", err));
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
    maxOptions: 50,
    loadThrottle: 300,
    valueField: "value",
    labelField: "text",
    searchField: ["text"],
    placeholder: window.i18n
      ? window.i18n.t("placeholder.type_to_search")
      : "Type to search...",
    allowEmptyOption: true,
    shouldLoad: function (query) {
      return query.length >= 2;
    },
    load: function (query, callback) {
      if (!query || query.length < 2) {
        callback();
        return;
      }

      fetch(
        `../api/clubs-list.php?search=${encodeURIComponent(
          query,
        )}&page=1&limit=50`,
      )
        .then((response) => response.json())
        .then((data) => {
          if (!data.success || !Array.isArray(data.data)) {
            callback();
            return;
          }

          callback(
            data.data.map((club) => ({
              value: String(club.id),
              text: `${club.name} (${club.reg_number})`,
            })),
          );
        })
        .catch(() => callback());
    },
  });

  clubSelectTom.on("change", function (value) {
    currentClubId = value;
    if (currentClubId) {
      loadEquipmentHistory(currentClubId);
    } else {
      resetEquipmentDisplay();
    }
    handleEquipmentTypeChange(
      equipmentTypeTom ? equipmentTypeTom.getValue() : null,
    );
  });
}

/**
 * Initialize equipment type TomSelect dropdown (no create support)
 */
function initializeEquipmentTypeSelect() {
  const selectElement = document.getElementById("equipmentTypeSelect");
  if (typeof TomSelect === "undefined" || !selectElement) {
    return;
  }

  if (equipmentTypeTom) {
    equipmentTypeTom.destroy();
  }

  equipmentTypeTom = new TomSelect(selectElement, {
    create: false,
    valueField: "value",
    labelField: "text",
    searchField: ["text"],
    placeholder: window.i18n
      ? window.i18n.t("placeholder.type_to_search")
      : "Type to search...",
    allowEmptyOption: true,
    maxOptions: 100,
    loadThrottle: 300,
    shouldLoad: function (query) {
      return query.length >= 0;
    },
    load: function (query, callback) {
      const url = query
        ? `../api/equipment-types.php?search=${encodeURIComponent(query)}`
        : "../api/equipment-types.php";

      fetch(url)
        .then((response) => response.json())
        .then((data) => {
          if (!data.success || !Array.isArray(data.data)) {
            callback();
            return;
          }

          callback(
            data.data.map((eq) => ({
              value: String(eq.id),
              text: eq.name,
            })),
          );
        })
        .catch(() => callback());
    },
    onChange: function (value) {
      handleEquipmentTypeChange(value);
    },
  });

  // Trigger initial state
  handleEquipmentTypeChange(equipmentTypeTom.getValue());
}

function loadEquipmentTypes() {
  // Kept for backward compatibility when using native select (if TomSelect fails)
  fetch("../api/equipment-types.php")
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        const select = document.getElementById("equipmentTypeSelect");
        const previousSelection = select.value;
        select.innerHTML =
          '<option value="" data-i18n="form.select_equipment">--- Select Equipment ---</option>';

        data.data.forEach((eq) => {
          const opt = document.createElement("option");
          opt.value = eq.id;
          opt.textContent = eq.name;
          select.appendChild(opt);
        });

        if (previousSelection) {
          select.value = previousSelection;
        }

        handleEquipmentTypeChange(select.value);
      }
    })
    .catch((err) => console.error("Error loading equipment types:", err));
}

/**
 * Handle equipment type selection state
 */
function handleEquipmentTypeChange(value) {
  const hasClub = !!currentClubId;
  const hasType = !!value;
  const quantityInput = document.getElementById("quantityInput");
  const yearInput = document.getElementById("yearInput");

  if (quantityInput && yearInput) {
    if (hasClub && hasType) {
      quantityInput.disabled = false;
      yearInput.disabled = false;
    } else {
      quantityInput.disabled = true;
      yearInput.disabled = true;
    }
  }
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
            <button class="btn-edit" onclick="openEditModal(${eq.id}, ${eq.quantity}, ${eq.year})" data-i18n="button.edit">Edit</button>
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
  const equipmentTypeValue = equipmentTypeTom
    ? equipmentTypeTom.getValue()
    : document.getElementById("equipmentTypeSelect").value;
  const quantity = document.getElementById("quantityInput").value;
  const year = document.getElementById("yearInput").value;

  // Validate input
  if (!clubId || !equipmentTypeValue || !quantity || !year) {
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

  const formattedDate = `${year}-01-01 12:00:00`;

  const requestBody = {
    club_id: clubId,
    equipment_type_id: equipmentTypeValue,
    quantity: parseInt(quantity),
    year: parseInt(year),
    date: formattedDate,
  };

  // Send request
  fetch("../api/equipment-management.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(requestBody),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        // Reset form
        document.getElementById("quantityInput").value = "1";
        document.getElementById("yearInput").value = new Date().getFullYear();
        if (equipmentTypeTom) {
          equipmentTypeTom.clear();
        } else {
          document.getElementById("equipmentTypeSelect").value = "";
        }
        handleEquipmentTypeChange(null);

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

function openEditModal(equipmentId, currentQuantity, currentYear) {
  editingEquipmentId = equipmentId;
  document.getElementById("editQuantityInput").value = currentQuantity;
  document.getElementById("editYearInput").value = currentYear;
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
  const year = document.getElementById("editYearInput").value;

  if (!quantity || quantity < 1) {
    alert(
      window.i18n
        ? window.i18n.t("message.invalid_quantity")
        : "Quantity must be at least 1",
    );
    return;
  }

  if (!year || year < 1900 || year > 2100) {
    alert(
      window.i18n
        ? window.i18n.t("message.invalid_year")
        : "Please enter a valid year",
    );
    return;
  }

  fetch("../api/equipment-management.php", {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      id: editingEquipmentId,
      quantity: parseInt(quantity),
      year: parseInt(year),
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

/**
 * Tab switching
 */
function switchTab(tabName) {
  // Hide all tab contents
  document.querySelectorAll('.tab-content').forEach(tab => {
    tab.classList.remove('active');
    tab.style.display = 'none';
  });
  
  // Remove active class from all tab buttons
  document.querySelectorAll('.tab-button').forEach(btn => {
    btn.classList.remove('active');
  });
  
  // Show selected tab content
  if (tabName === 'club-equipment') {
    const tabContent = document.getElementById('tabContentClubEquipment');
    tabContent.classList.add('active');
    tabContent.style.display = 'block';
    document.getElementById('tabClubEquipment').classList.add('active');
  } else if (tabName === 'equipment-types') {
    const tabContent = document.getElementById('tabContentEquipmentTypes');
    tabContent.classList.add('active');
    tabContent.style.display = 'block';
    document.getElementById('tabEquipmentTypes').classList.add('active');
    loadAllEquipmentTypes();
  }
}

/**
 * Load all equipment types
 */
function loadAllEquipmentTypes() {
  fetch('../api/equipment-types.php')
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        allEquipmentTypes = data.data;
        renderEquipmentTypes(allEquipmentTypes);
      }
    })
    .catch(err => console.error('Error loading equipment types:', err));
}

/**
 * Render equipment types table
 */
function renderEquipmentTypes(types) {
  const tbody = document.getElementById('equipmentTypesTableBody');
  
  if (types.length === 0) {
    tbody.innerHTML = '<tr><td colspan="3" class="text-center text-gray-500 py-4">No equipment types found</td></tr>';
    return;
  }
  
  tbody.innerHTML = types.map(type => `
    <tr>
      <td>${escapeHtml(type.name)}</td>
      <td>
        <span class="px-2 py-1 text-xs rounded ${type.is_standard ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'}">
          ${type.is_standard ? 'Standard' : 'Custom'}
        </span>
      </td>
      <td>
        <div class="equipment-item-actions">
          <button class="btn-edit" onclick="openEditEquipmentTypeModal(${type.id}, '${escapeHtml(type.name).replace(/'/g, "\\'")}')">Edit</button>
          ${!type.is_standard ? `<button class="btn-delete" onclick="deleteEquipmentType(${type.id})">Delete</button>` : ''}
        </div>
      </td>
    </tr>
  `).join('');
}

/**
 * Search equipment types
 */
function searchEquipmentTypes() {
  const searchTerm = document.getElementById('searchEquipmentTypes').value.toLowerCase();
  const filtered = allEquipmentTypes.filter(type => 
    type.name.toLowerCase().includes(searchTerm)
  );
  renderEquipmentTypes(filtered);
}

/**
 * Add equipment type
 */
function addEquipmentType() {
  const name = document.getElementById('equipmentTypeNameInput').value.trim();
  
  if (!name) {
    alert('Please enter equipment type name');
    return;
  }
  
  fetch('../api/equipment-types.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ name: name })
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        document.getElementById('equipmentTypeNameInput').value = '';
        loadAllEquipmentTypes();
        
        // Refresh the equipment type dropdown in club equipment tab
        if (equipmentTypeTom) {
          equipmentTypeTom.addOption({
            value: String(data.data.id),
            text: data.data.name
          });
        }
        
        alert('Equipment type added successfully');
      } else {
        alert(data.message || 'Failed to add equipment type');
      }
    })
    .catch(err => {
      console.error('Error:', err);
      alert('An error occurred');
    });
}

/**
 * Open edit equipment type modal
 */
function openEditEquipmentTypeModal(id, name) {
  editingEquipmentTypeId = id;
  document.getElementById('editEquipmentTypeNameInput').value = name;
  document.getElementById('editEquipmentTypeModal').classList.add('active');
}

/**
 * Close edit equipment type modal
 */
function closeEquipmentTypeEditModal() {
  document.getElementById('editEquipmentTypeModal').classList.remove('active');
  editingEquipmentTypeId = null;
}

/**
 * Save equipment type edit
 */
function saveEquipmentTypeEdit() {
  if (!editingEquipmentTypeId) return;
  
  const name = document.getElementById('editEquipmentTypeNameInput').value.trim();
  
  if (!name) {
    alert('Please enter equipment type name');
    return;
  }
  
  fetch('../api/equipment-types.php', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: editingEquipmentTypeId, name: name })
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        closeEquipmentTypeEditModal();
        loadAllEquipmentTypes();
        
        // Update the equipment type dropdown in club equipment tab
        if (equipmentTypeTom) {
          equipmentTypeTom.updateOption(String(editingEquipmentTypeId), {
            value: String(editingEquipmentTypeId),
            text: name
          });
        }
        
        alert('Equipment type updated successfully');
      } else {
        alert(data.message || 'Failed to update equipment type');
      }
    })
    .catch(err => {
      console.error('Error:', err);
      alert('An error occurred');
    });
}

/**
 * Delete equipment type
 */
function deleteEquipmentType(id) {
  if (!confirm('Are you sure you want to delete this equipment type? This will also remove all associated equipment records.')) {
    return;
  }
  
  fetch('../api/equipment-types.php', {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: id })
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        loadAllEquipmentTypes();
        
        // Remove from equipment type dropdown in club equipment tab
        if (equipmentTypeTom) {
          equipmentTypeTom.removeOption(String(id));
        }
        
        alert('Equipment type deleted successfully');
      } else {
        alert(data.message || 'Failed to delete equipment type');
      }
    })
    .catch(err => {
      console.error('Error:', err);
      alert('An error occurred');
    });
}

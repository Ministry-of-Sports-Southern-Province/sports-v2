/**
 * Club Reorganization JavaScript
 */

const apiBase = "../api";
let currentClubData = null;

document.addEventListener("DOMContentLoaded", function () {
  const clubId = document.getElementById("clubId").value;

  loadClubData(clubId);
  loadReorganizationHistory(clubId);
  initializeLocationDropdowns();

  document
    .getElementById("reorganizationForm")
    .addEventListener("submit", handleSubmit);
});

/**
 * Load current club data
 */
function loadClubData(clubId) {
  fetch(`${apiBase}/clubs.php?id=${clubId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data) {
        currentClubData = data.data;
        displayCurrentInfo(data.data);
        populateForm(data.data);
      } else {
        alert(
          window.i18n
            ? window.i18n.t("message.load_error")
            : "Failed to load club data",
        );
        window.location.href = "dashboard.php";
      }
    })
    .catch((error) => {
      console.error("Error loading club:", error);
      alert(
        window.i18n
          ? window.i18n.t("message.load_error")
          : "Error loading club data",
      );
    });
}

/**
 * Display current club information
 */
function displayCurrentInfo(club) {
  const container = document.getElementById("currentInfoContent");
  container.innerHTML = `
        <div class="info-grid">
      <div class="detail-card">
        <h2 data-i18n="form.club_information">Club Information</h2>
        <div class="info-row">
          <div><label data-i18n="form.reg_number">Registration Number</label><p>${escapeHtml(
            club.reg_number,
          )}</p></div>
          <div><label data-i18n="form.registration_date">Registration Date</label><p>${formatDate(
            club.registration_date,
          )}</p></div>
        </div>
        <div class="info-row">
          <div class="full-width"><label data-i18n="form.club_name">Club Name</label><p>${escapeHtml(
            club.name,
          )}</p></div>
        </div>
      </div>

      <div class="detail-card">
        <h2 data-i18n="form.location_information">Location Information</h2>
        <div class="info-row">
          <div><label data-i18n="form.district">District</label><p>${escapeHtml(
            club.district_name || "-",
          )}</p></div>
          <div><label data-i18n="form.division">Division</label><p>${escapeHtml(
            club.division_name || "-",
          )}</p></div>
        </div>
        <div class="info-row">
          <div class="full-width"><label data-i18n="form.gn_division">GN Division</label><p>${escapeHtml(
            club.gn_division_name || "-",
          )}</p></div>
        </div>
      </div>

      <div class="detail-card">
        <h2 data-i18n="form.chairman_information">Chairman Information</h2>
        <div class="info-row">
          <div><label data-i18n="form.chairman_name">Chairman Name</label><p>${escapeHtml(
            club.chairman_name,
          )}</p></div>
          <div><label data-i18n="form.chairman_phone">Chairman Phone</label><p>${escapeHtml(
            club.chairman_phone,
          )}</p></div>
        </div>
        <div class="info-row">
          <div class="full-width"><label data-i18n="form.chairman_address">Chairman Address</label><p>${escapeHtml(
            club.chairman_address,
          )}</p></div>
        </div>
      </div>

      <div class="detail-card">
        <h2 data-i18n="form.secretary_information">Secretary Information</h2>
        <div class="info-row">
          <div><label data-i18n="form.secretary_name">Secretary Name</label><p>${escapeHtml(
            club.secretary_name,
          )}</p></div>
          <div><label data-i18n="form.secretary_phone">Secretary Phone</label><p>${escapeHtml(
            club.secretary_phone,
          )}</p></div>
        </div>
        <div class="info-row">
          <div class="full-width"><label data-i18n="form.secretary_address">Secretary Address</label><p>${escapeHtml(
            club.secretary_address,
          )}</p></div>
        </div>
      </div>
    </div>
    `;

  // Fetch and display location names
  if (club.district_id) {
    fetch(`${apiBase}/locations.php?type=district&id=${club.district_id}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success && data.data) {
          document.getElementById("currentDistrict").textContent =
            data.data.name;
        } else {
          document.getElementById("currentDistrict").textContent = "-";
        }
      })
      .catch(() => {
        document.getElementById("currentDistrict").textContent = "-";
      });
  } else {
    document.getElementById("currentDistrict").textContent = "-";
  }

  if (club.division_id) {
    fetch(`${apiBase}/locations.php?type=division&id=${club.division_id}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success && data.data) {
          document.getElementById("currentDivision").textContent =
            data.data.name;
        } else {
          document.getElementById("currentDivision").textContent = "-";
        }
      })
      .catch(() => {
        document.getElementById("currentDivision").textContent = "-";
      });
  } else {
    document.getElementById("currentDivision").textContent = "-";
  }

  if (club.gn_division_id) {
    fetch(`${apiBase}/locations.php?type=gn_division&id=${club.gn_division_id}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success && data.data) {
          document.getElementById("currentGnDivision").textContent =
            data.data.name;
        } else {
          document.getElementById("currentGnDivision").textContent = "-";
        }
      })
      .catch(() => {
        document.getElementById("currentGnDivision").textContent = "-";
      });
  } else {
    document.getElementById("currentGnDivision").textContent = "-";
  }
}

/**
 * Populate form with current data
 */
function populateForm(club) {
  document.getElementById("clubName").value = club.name;
  document.getElementById("chairmanName").value = club.chairman_name;
  document.getElementById("chairmanAddress").value = club.chairman_address;
  document.getElementById("chairmanPhone").value = club.chairman_phone;
  document.getElementById("secretaryName").value = club.secretary_name;
  document.getElementById("secretaryAddress").value = club.secretary_address;
  document.getElementById("secretaryPhone").value = club.secretary_phone;

  // Set today's date as default reorganization date
  document.getElementById("reorgDate").valueAsDate = new Date();

  // Load location data if available
  if (club.district_id) {
    document.getElementById("district").value = club.district_id;
    if (club.division_id) {
      loadDivisions(club.district_id, club.division_id);
    }
  }
}

/**
 * Load location hierarchy (district -> division -> GN division)
 */
function loadLocationHierarchy(gnDivisionId) {
  fetch(`${apiBase}/locations.php?type=gn_division&id=${gnDivisionId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data) {
        const gnDiv = data.data;
        const divisionId = gnDiv.division_id;

        // Load division to get district
        return fetch(`${apiBase}/locations.php?type=division&id=${divisionId}`);
      }
      throw new Error("GN Division not found");
    })
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data) {
        const division = data.data;
        const districtId = division.district_id;

        // Set district (disabled, just for display)
        document.getElementById("district").value = districtId;

        // Load divisions for this district
        loadDivisions(districtId, division.id);
      }
    })
    .catch((error) => {
      console.error("Error loading location hierarchy:", error);
    });
}

/**
 * Initialize location dropdowns
 */
function initializeLocationDropdowns() {
  // Load districts
  fetch(`${apiBase}/locations.php?type=district`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const select = document.getElementById("district");
        data.data.forEach((district) => {
          const option = document.createElement("option");
          option.value = district.id;
          option.textContent = district.name;
          select.appendChild(option);
        });

        // Pre-select district if available
        if (currentClubData && currentClubData.district_id) {
          select.value = currentClubData.district_id;
        }
      }
    })
    .catch((error) => {
      console.error("Error loading districts:", error);
    });

  // Division change handler (district is disabled)
  document.getElementById("division").addEventListener("change", function () {
    loadGNDivisions(this.value);
  });
}

/**
 * Load divisions for selected district
 */
function loadDivisions(districtId, selectedDivisionId = null) {
  const divisionSelect = document.getElementById("division");
  const gnDivisionSelect = document.getElementById("gnDivision");

  divisionSelect.innerHTML = '<option value="">Select division</option>';
  gnDivisionSelect.innerHTML =
    '<option value="">Select division first</option>';

  if (!districtId) return;

  fetch(`${apiBase}/locations.php?type=division&parent_id=${districtId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        data.data.forEach((division) => {
          const option = document.createElement("option");
          option.value = division.id;
          option.textContent = division.name;
          divisionSelect.appendChild(option);
        });

        if (selectedDivisionId) {
          divisionSelect.value = selectedDivisionId;
          loadGNDivisions(selectedDivisionId, currentClubData?.gn_division_id);
        }
      }
    })
    .catch((error) => {
      console.error("Error loading divisions:", error);
    });
}

/**
 * Load GN divisions for selected division
 */
function loadGNDivisions(divisionId, selectedGNDivisionId = null) {
  const gnDivisionSelect = document.getElementById("gnDivision");
  gnDivisionSelect.innerHTML = '<option value="">Select GN division</option>';

  if (!divisionId) return;

  fetch(`${apiBase}/locations.php?type=gn_division&parent_id=${divisionId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        data.data.forEach((gnDivision) => {
          const option = document.createElement("option");
          option.value = gnDivision.id;
          option.textContent = gnDivision.name;
          gnDivisionSelect.appendChild(option);
        });

        if (selectedGNDivisionId) {
          gnDivisionSelect.value = selectedGNDivisionId;
        }
      }
    });
}

/**
 * Handle form submission
 */
function handleSubmit(e) {
  e.preventDefault();

  const clubId = document.getElementById("clubId").value;
  const formData = {
    club_id: clubId,
    reorg_date: document.getElementById("reorgDate").value,
    name: document.getElementById("clubName").value,
    chairman_name: document.getElementById("chairmanName").value,
    chairman_address: document.getElementById("chairmanAddress").value,
    chairman_phone: document.getElementById("chairmanPhone").value,
    secretary_name: document.getElementById("secretaryName").value,
    secretary_address: document.getElementById("secretaryAddress").value,
    secretary_phone: document.getElementById("secretaryPhone").value,
    gn_division_id: document.getElementById("gnDivision").value,
    notes: document.getElementById("notes").value,
  };

  // Disable submit button
  const submitBtn = e.target.querySelector('button[type="submit"]');
  submitBtn.disabled = true;
  submitBtn.textContent = "Processing...";

  fetch(`${apiBase}/reorganize-club.php`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(formData),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert(
          window.i18n
            ? window.i18n.t("message.reorganize_success")
            : "Club reorganized successfully!",
        );
        window.location.href = "dashboard.php";
      } else {
        alert(
          data.message ||
            (window.i18n
              ? window.i18n.t("message.reorganize_error")
              : "Failed to reorganize club"),
        );
        submitBtn.disabled = false;
        submitBtn.textContent = "Reorganize Club";
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert(
        window.i18n
          ? window.i18n.t("message.error_reorganizing_club")
          : "An error occurred while reorganizing the club",
      );
      submitBtn.disabled = false;
      submitBtn.textContent = "Reorganize Club";
    });
}

/**
 * Load reorganization history
 */
function loadReorganizationHistory(clubId) {
  fetch(`${apiBase}/reorganize-club.php?club_id=${clubId}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.text();
    })
    .then((text) => {
      try {
        const data = JSON.parse(text);
        if (data.success) {
          displayHistory(data.data, clubId);
        } else {
          document.getElementById("historyContainer").innerHTML =
            '<p class="text-gray-500">No reorganization history</p>';
        }
      } catch (e) {
        console.error("Failed to parse JSON response:", e);
        console.error("Response was:", text.substring(0, 200));
        document.getElementById("historyContainer").innerHTML =
          '<p class="text-red-500">Error loading history. Please check the server logs.</p>';
      }
    })
    .catch((error) => {
      console.error("Error loading history:", error);
      document.getElementById("historyContainer").innerHTML =
        '<p class="text-gray-500">No reorganization history</p>';
    });
}

/**
 * Display reorganization history
 */
function displayHistory(history, clubId) {
  const container = document.getElementById("historyContainer");

  if (history.length === 0) {
    container.innerHTML =
      '<p class="text-gray-500">No reorganization history</p>';
    return;
  }

  container.innerHTML = history
    .map(
      (item, index) => `
        <div class="border-l-4 border-blue-500 pl-4 py-3 mb-4 bg-gray-50 rounded">
            <div class="flex justify-between items-center">
                <div class="flex-1 cursor-pointer hover:bg-gray-100 -ml-4 pl-4 py-2" onclick="showHistoryDetailModal(${index}, ${clubId})">
                    <p class="font-semibold text-blue-900">${formatDate(item.reorg_date)}</p>
                    <p class="text-sm text-gray-600 mt-1">
                        ${escapeHtml(item.prev_name)} → ${escapeHtml(item.current_name)}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="showHistoryDetailModal(${index}, ${clubId})" class="text-blue-600 hover:text-blue-800 text-sm font-medium px-3 py-1">
                        View Details →
                    </button>
                </div>
            </div>
        </div>
    `,
    )
    .join("");

  // Store history data globally for shared modal
  window.clubReorgHistory = history;
}

// showHistoryModal, closeHistoryModal, deleteReorganizationFromModal are replaced by shared-history-modal.js
window.onHistoryDeleteSuccess = function (clubId) {
  loadReorganizationHistory(clubId);
};

/**
 * Format date
 */
function formatDate(dateString) {
  if (!dateString) return "-";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}

/**
 * Escape HTML
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

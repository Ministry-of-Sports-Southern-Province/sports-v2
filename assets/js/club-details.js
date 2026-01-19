/**
 * Club Details JavaScript
 */

document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const clubId = urlParams.get("id");

  if (!clubId) {
    showError(
      window.i18n ? window.i18n.t("message.no_data") : "Club ID not provided",
    );
    return;
  }

  loadClubDetails(clubId);
});

function loadClubDetails(clubId) {
  fetch(`../api/club-details.php?id=${clubId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayClubDetails(data.data);
      } else {
        showError(
          data.message ||
            (window.i18n
              ? window.i18n.t("message.load_error")
              : "Failed to load club details"),
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showError(
        window.i18n
          ? window.i18n.t("message.error_generic")
          : "An error occurred while loading club details",
      );
    });
}

function displayClubDetails(club) {
  const container = document.getElementById("clubDetails");

  container.innerHTML = `
    <div class="print-header" style="display: none;">
      <h1 data-i18n="header.department_name">Southern Province Sports Department</h1>
      <div class="subtitle" data-i18n="header.department_name_en">Southern Province Sports Department</div>
      <div class="subtitle" style="font-weight: bold; margin-top: 3mm;" data-i18n="header.certificate_title">Sports Club Registration Certificate</div>
    </div>
    
    <style>
      @media print {
        .print-header { display: block !important; }
      }
    </style>

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

    ${
      club.equipment && club.equipment.length > 0
        ? `
    <div class="detail-card">
      <h2 data-i18n="form.equipment_information">Equipment</h2>
      <table>
        <thead>
          <tr>
            <th data-i18n="table.equipment">Equipment</th>
            <th data-i18n="table.quantity">Quantity</th>
          </tr>
        </thead>
        <tbody>
          ${club.equipment
            .map(
              (eq) => `
            <tr>
              <td>${escapeHtml(eq.name)}</td>
              <td>${eq.quantity}</td>
            </tr>
          `,
            )
            .join("")}
        </tbody>
      </table>
    </div>
    `
        : ""
    }
    
    <div class="detail-card">
      <h2 data-i18n="form.reorganization_information">Reorganization Information</h2>
      <div class="info-row">
        <div><label data-i18n="form.last_reorg_date">Last Reorganization Date</label><p>${club.last_reorg_date ? formatDate(club.last_reorg_date) : (window.i18n ? window.i18n.t("message.no_data") : "N/A")}</p></div>
        <div><label data-i18n="form.reorg_due_date">Next Reorganization Date</label><p>${club.reorg_due_date ? formatDate(club.reorg_due_date) : (window.i18n ? window.i18n.t("message.no_data") : "N/A")}</p></div>
      </div>
      <div class="info-row">
        <div><label data-i18n="form.reorg_status">Status</label><p><span class="px-2 py-1 rounded text-sm ${club.reorg_status === "active" ? "bg-green-100 text-green-800" : "bg-yellow-100 text-yellow-800"}" data-i18n="status.${club.reorg_status}">${club.reorg_status === "active" ? (window.i18n ? window.i18n.t("status.active") : "Active") : (window.i18n ? window.i18n.t("status.expired") : "Expired")}</span></p></div>
      </div>
      <div class="no-print mt-4">
        <button onclick="openReorgModal(${club.id})" class="px-4 py-2 ${club.reorg_status === "expired" ? "bg-green-600 hover:bg-green-700" : "bg-blue-600 hover:bg-blue-700"} text-white rounded transition">
          <span data-i18n="button.add_reorg">Add Reorganization</span>
        </button>
        ${club.last_reorg_date ? `<button onclick="deleteReorg(${club.id})" class="ml-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded transition"><span data-i18n="button.delete_reorg">Delete Last Date</span></button>` : ""}
        <button onclick="viewReorgHistory(${club.id})" class="ml-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded transition">
          <span data-i18n="button.view_history">View History</span>
        </button>
      </div>
    </div>
    
    <div class="print-footer" style="display: none;">
      <p data-i18n="footer.certificate_note">This certificate was issued by the Southern Province Sports Department</p>
    </div>
    
    <div id="reorgModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4" data-i18n="modal.add_reorg_title">Add Reorganization Date</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-2">
            <input type="radio" name="dateOption" value="auto" checked onchange="toggleDateInput()"> 
            <span data-i18n="form.date_option_auto">Use today's date</span>
          </label>
          <label class="block text-sm font-medium mb-2">
            <input type="radio" name="dateOption" value="manual" onchange="toggleDateInput()"> 
            <span data-i18n="form.date_option_manual">Enter a date</span>
          </label>
        </div>
        <div class="mb-4" id="dateInputContainer" style="display:none;">
          <label class="block text-sm font-medium mb-2" data-i18n="form.reorg_date">Reorganization Date</label>
          <input type="date" id="reorgDate" class="w-full px-3 py-2 border rounded">
        </div>
        <div class="flex justify-end gap-2">
          <button onclick="closeReorgModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" data-i18n="button.cancel">Cancel</button>
          <button onclick="saveReorg()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" data-i18n="button.save">Save</button>
        </div>
      </div>
    </div>
    
    <div id="historyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
        <h3 class="text-xl font-bold mb-4" data-i18n="modal.reorg_history_title">Reorganization History</h3>
        <div id="historyContent" class="mb-4"></div>
        <div class="flex justify-end">
          <button onclick="closeHistoryModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" data-i18n="button.close">Close</button>
        </div>
      </div>
    </div>
  `;

  // Apply translations with proper timing to ensure i18n is ready
  if (window.i18n && window.i18n.applyTranslations) {
    // Use requestAnimationFrame to ensure DOM is fully rendered
    requestAnimationFrame(() => {
      if (window.i18n && window.i18n.applyTranslations) {
        window.i18n.applyTranslations();
      }
    });
  } else {
    // If i18n not ready yet, wait a bit and try again
    setTimeout(() => {
      if (window.i18n && window.i18n.applyTranslations) {
        window.i18n.applyTranslations();
      }
    }, 50);
  }
}

let currentClubId = null;

function toggleDateInput() {
  const dateOption = document.querySelector(
    'input[name="dateOption"]:checked',
  ).value;
  const container = document.getElementById("dateInputContainer");
  const dateInput = document.getElementById("reorgDate");

  if (dateOption === "manual") {
    container.style.display = "block";
    dateInput.value = "";
  } else {
    container.style.display = "none";
    dateInput.value = new Date().toISOString().split("T")[0];
  }
}

function openReorgModal(clubId) {
  currentClubId = clubId;
  document.getElementById("reorgModal").classList.remove("hidden");
  document.querySelector('input[name="dateOption"][value="auto"]').checked =
    true;
  toggleDateInput();
}

function closeReorgModal() {
  document.getElementById("reorgModal").classList.add("hidden");
  currentClubId = null;
}

function saveReorg() {
  const dateOption = document.querySelector(
    'input[name="dateOption"]:checked',
  ).value;
  let reorgDate;

  if (dateOption === "auto") {
    reorgDate = new Date().toISOString().split("T")[0];
  } else {
    reorgDate = document.getElementById("reorgDate").value;
    if (!reorgDate) {
      alert(
        window.i18n
          ? window.i18n.t("message.invalid_date")
          : "Please enter a date",
      );
      return;
    }
  }

  fetch("../api/reorganizations.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ club_id: currentClubId, reorg_date: reorgDate }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        alert(
          window.i18n
            ? window.i18n.t("message.reorg_added_success")
            : "Reorganization date added successfully",
        );
        closeReorgModal();
        loadClubDetails(currentClubId);
      } else {
        alert(
          data.message ||
            (window.i18n
              ? window.i18n.t("message.error_generic")
              : "An error occurred"),
        );
      }
    })
    .catch((err) => {
      console.error(err);
      alert(
        window.i18n
          ? window.i18n.t("message.error_generic")
          : "An error occurred",
      );
    });
}

function deleteReorg(clubId) {
  if (
    !confirm(
      window.i18n
        ? window.i18n.t("message.confirm_delete_reorg")
        : "Are you sure you want to delete the reorganization date?",
    )
  )
    return;

  fetch("../api/reorganizations.php", {
    method: "DELETE",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ club_id: clubId }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        alert(
          window.i18n
            ? window.i18n.t("message.reorg_deleted_success")
            : "Reorganization date deleted successfully",
        );
        loadClubDetails(clubId);
      } else {
        alert(
          data.message ||
            (window.i18n
              ? window.i18n.t("message.error_generic")
              : "An error occurred"),
        );
      }
    })
    .catch((err) => {
      console.error(err);
      alert(
        window.i18n
          ? window.i18n.t("message.error_generic")
          : "An error occurred",
      );
    });
}

function viewReorgHistory(clubId) {
  fetch(`../api/reorganizations.php?club_id=${clubId}`)
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        const historyContent = document.getElementById("historyContent");
        if (data.data.length === 0) {
          historyContent.innerHTML =
            '<p class="text-center text-gray-500" data-i18n="message.no_reorg_history">No reorganization history</p>';
        } else {
          historyContent.innerHTML = `
          <table class="w-full">
            <thead>
              <tr class="bg-gray-100">
                <th class="p-2 text-left">#</th>
                <th class="p-2 text-left" data-i18n="form.reorg_date">Date</th>
              </tr>
            </thead>
            <tbody>
              ${data.data
                .map(
                  (r, i) => `
                <tr class="border-t">
                  <td class="p-2">${i + 1}</td>
                  <td class="p-2">${formatDate(r.reorg_date)}</td>
                </tr>
              `,
                )
                .join("")}
            </tbody>
          </table>
        `;
        }
        document.getElementById("historyModal").classList.remove("hidden");
        if (window.i18n && window.i18n.applyTranslations) {
          window.i18n.applyTranslations();
        }
      } else {
        alert(
          data.message ||
            (window.i18n
              ? window.i18n.t("message.error_generic")
              : "An error occurred"),
        );
      }
    })
    .catch((err) => {
      console.error(err);
      alert(
        window.i18n
          ? window.i18n.t("message.error_generic")
          : "An error occurred",
      );
    });
}

function closeHistoryModal() {
  document.getElementById("historyModal").classList.add("hidden");
}

function showError(message) {
  const container = document.getElementById("clubDetails");
  container.innerHTML = `
    <div class="detail-card">
      <div class="text-center text-red-600">
        <p>${escapeHtml(message)}</p>
        <a href="dashboard.php" class="mt-4 inline-block text-blue-600 hover:text-blue-800" data-i18n="button.back">← Back</a>
      </div>
    </div>
  `;
}

function formatDate(dateString) {
  if (!dateString) return "-";
  const date = new Date(dateString);
  return date.toLocaleDateString("si-LK");
}

function escapeHtml(text) {
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  };
  return text ? String(text).replace(/[&<>\"']/g, (m) => map[m]) : "";
}

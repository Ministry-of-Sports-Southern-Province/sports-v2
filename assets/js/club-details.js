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
      <div class="dept-name" data-i18n="header.department_name">Department of Sports Southern Province</div>
      
      <h1 data-i18n="header.certificate_title">Sports Club Registration Certificate</h1>
      
      <div class="club-name-display">${escapeHtml(club.name)}</div>
    </div>
    
    <style>
      @media print {
        @page { margin: 10mm; size: A4; }
        body { background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        
        /* Layout Resets */
        .container, main { max-width: none !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
        .no-print, nav, header, .accessibility-fab { display: none !important; }
        
        /* Main Border Container */
        #clubDetails { 
            border: 3px double #1e3a8a; 
            padding: 20px; 
            min-height: 270mm; 
            box-sizing: border-box; 
            position: relative; 
            margin: 0 auto;
        }

        /* Header */
        .print-header { display: block !important; text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; }
        .dept-name { font-size: 12pt; font-weight: bold; color: #4b5563; text-transform: uppercase; margin-bottom: 5px; }
        .print-header h1 { font-size: 22pt; font-weight: 900; color: #1e3a8a; text-transform: uppercase; margin: 5px 0; letter-spacing: 1px; }
        .club-name-display { font-size: 14pt; font-weight: bold; color: #000; background: #f3f4f6; padding: 6px 20px; border-radius: 20px; border: 1px solid #d1d5db; display: inline-block; margin-top: 5px; }

        /* Info Grid */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .detail-card { border: 1px solid #9ca3af; padding: 0; margin-bottom: 10px; page-break-inside: avoid; border-radius: 4px; overflow: hidden; }
        .detail-card h2 { background-color: #1e3a8a !important; color: white !important; padding: 5px 10px; font-size: 10pt; font-weight: bold; margin: 0 0 5px 0; text-transform: uppercase; border-bottom: 1px solid #1e3a8a; }
        .info-row { display: flex; gap: 10px; padding: 0 10px 5px 10px; margin-bottom: 2px; }
        .info-row > div { flex: 1; }
        .detail-card label { font-size: 7.5pt; font-weight: bold; color: #555; display: block; text-transform: uppercase; }
        .detail-card p { font-size: 9pt; font-weight: 600; color: #000; border-bottom: 1px dotted #ccc; margin: 0; padding-bottom: 1px; min-height: 16px; }

        /* Equipment Grid */
        .equipment-list { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; border: 1px solid #9ca3af; padding: 8px; margin-bottom: 20px; }
        .equipment-item { display: flex; justify-content: space-between; align-items: center; border: 1px solid #eee; padding: 4px 8px; border-radius: 4px; page-break-inside: avoid; }
        .equipment-item .eq-name { font-size: 8pt; font-weight: 600; color: #333; }
        .equipment-item .eq-qty { font-size: 8pt; font-weight: bold; background: #eee; padding: 1px 6px; border-radius: 4px; }

        /* Footer & Signatures */
        .print-footer { display: block !important; position: absolute; bottom: 0; left: 0; right: 0; padding: 0 20px 10px; width: 100%; box-sizing: border-box; }
        .signatures { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .sig-block { width: 200px; text-align: center; }
        .sig-line { border-bottom: 1px dotted #000; margin-bottom: 5px; height: 20px; }
        .sig-label { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
        .footer-bottom { border-top: 2px solid #1e3a8a; padding-top: 5px; text-align: center; font-size: 8pt; color: #555; }
      }
    </style>

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

    ${
      club.equipment && club.equipment.length > 0
        ? `
    <div class="detail-card">
      <h2 data-i18n="form.equipment_information">Equipment</h2>
      <div class="equipment-list">
        ${club.equipment
          .map(
            (eq) => `
            <div class="equipment-item">
              <span class="eq-name">${escapeHtml(eq.name)}</span>
              <span class="eq-qty">${eq.quantity}</span>
            </div>
          `,
          )
          .join("")}
      </div>
    </div>
    `
        : ""
    }
    
    <div class="detail-card">
      <h2 data-i18n="form.reorganization_information">Reorganization Information</h2>
      <div class="info-row">
        <div><label data-i18n="form.last_reorg_date">Last Reorganization Date</label><p>${club.last_reorg_date ? formatDate(club.last_reorg_date) : window.i18n ? window.i18n.t("message.no_data") : "N/A"}</p></div>
        <div><label data-i18n="form.reorg_due_date">Next Reorganization Date</label><p>${club.reorg_due_date ? formatDate(club.reorg_due_date) : window.i18n ? window.i18n.t("message.no_data") : "N/A"}</p></div>
      </div>
      <div class="info-row">
        <div><label data-i18n="form.reorg_status">Status</label><p><span class="px-2 py-1 rounded text-sm ${club.reorg_status === "active" ? "bg-green-100 text-green-800" : "bg-yellow-100 text-yellow-800"}" data-i18n="status.${club.reorg_status}">${club.reorg_status === "active" ? (window.i18n ? window.i18n.t("status.active") : "Active") : window.i18n ? window.i18n.t("status.expired") : "Expired"}</span></p></div>
      </div>
      <div class="no-print mt-4">
        ${
          window.currentUserRole === "admin"
            ? `
        <button onclick="openReorgModal(${club.id})" class="px-4 py-2 ${club.reorg_status === "expired" ? "bg-green-600 hover:bg-green-700" : "bg-blue-600 hover:bg-blue-700"} text-white rounded transition">
          <span data-i18n="button.add_reorg">Add Reorganization</span>
        </button>
        ${club.last_reorg_date ? `<button onclick="deleteReorg(${club.id})" class="ml-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded transition"><span data-i18n="button.delete_reorg">Delete Last Date</span></button>` : ""}
        `
            : ""
        }
        <button onclick="viewReorgHistory(${club.id})" class="ml-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded transition">
          <span data-i18n="button.view_history">View History</span>
        </button>
      </div>
    </div>
    
    <div class="print-footer" style="display: none;">
      <div class="signatures">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label" data-i18n="footer.created_by">Created By</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label" data-i18n="footer.approved_by">Approved By</div>
        </div>
      </div>
      <div class="footer-bottom">
        <p data-i18n="footer.certificate_note">This certificate was issued by the Department of Sports Southern Province</p>
      </div>
    </div>
    
    <div id="reorgModal" class="hidden fixed inset-0 bg-slate-900/40 flex items-center justify-center z-50">
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
          <label class="block text-sm font-medium mb-2 text-black" data-i18n="form.reorg_date">Reorganization Date</label>
          <input type="date" id="reorgDate" class="w-full px-3 py-2 border rounded">
        </div>
        <div class="flex justify-end gap-2">
          <button onclick="closeReorgModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" data-i18n="button.cancel">Cancel</button>
          <button onclick="saveReorg()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" data-i18n="button.save">Save</button>
        </div>
      </div>
    </div>
    
    <div id="historyModal" class="hidden fixed inset-0 bg-slate-900/40 flex items-center justify-center z-50">
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
  fetch(`../api/reorganize-club.php?club_id=${clubId}`)
    .then((res) => res.json())
    .then((data) => {
      if (data.success && data.data.length > 0) {
        showHistoryListModal(data.data, clubId);
      } else {
        alert(
          window.i18n
            ? window.i18n.t("message.no_reorg_history")
            : "No reorganization history found for this club.",
        );
      }
    })
    .catch((err) => {
      console.error(err);
      alert(
        window.i18n
          ? window.i18n.t("message.error_generic")
          : "Failed to load reorganization history.",
      );
    });
}

// deleteReorganizationRecord and history modals are provided by shared-history-modal.js
window.onHistoryDeleteSuccess = function (clubId) {
  loadClubDetails(clubId);
};

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

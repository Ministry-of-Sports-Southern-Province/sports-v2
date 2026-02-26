/**
 * Shared Reorganization History Modal
 *
 * Provides showHistoryListModal() and showHistoryDetailModal() used by:
 *   - club-details.js
 *   - reorganizations.js
 *   - reorganize-club.js
 *
 * After a successful deletion each page must define:
 *   window.onHistoryDeleteSuccess = function(clubId) { ... }
 */

function _shm_escapeHtml(text) {
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  };
  return text ? String(text).replace(/[&<>"']/g, (m) => map[m]) : "";
}

function _shm_formatDate(dateString) {
  if (!dateString) return "-";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}

function showHistoryListModal(history, clubId) {
  const modal = document.createElement("div");
  modal.className =
    "fixed inset-0 bg-gray-100 bg-opacity-80 flex items-center justify-center z-50 p-4";
  modal.onclick = (e) => {
    if (e.target === modal) modal.remove();
  };

  modal.innerHTML = `
    <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl" style="max-height: 90vh; display: flex; flex-direction: column;" onclick="event.stopPropagation()">
      <div class="bg-white border-b px-4 sm:px-6 py-4 flex justify-between items-center flex-shrink-0">
        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Reorganization History</h3>
        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div class="p-4 sm:p-6 overflow-y-auto flex-1">
        ${history
          .map(
            (item, index) => `
          <div class="border rounded-lg p-4 mb-4 hover:shadow-md transition">
            <div class="flex justify-between items-start">
              <div class="flex-1 cursor-pointer" onclick="showHistoryDetailModal(${index}, ${clubId})">
                <p class="font-semibold text-blue-900 text-lg">${_shm_formatDate(item.reorg_date)}</p>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                  <div>
                    <p class="text-gray-600 font-medium">Previous:</p>
                    <p class="text-gray-900">${_shm_escapeHtml(item.prev_name)}</p>
                    <p class="text-gray-600 text-xs mt-1">Chairman: ${_shm_escapeHtml(item.prev_chairman_name)}</p>
                  </div>
                  <div>
                    <p class="text-gray-600 font-medium">Current:</p>
                    <p class="text-gray-900">${_shm_escapeHtml(item.current_name)}</p>
                    <p class="text-gray-600 text-xs mt-1">Chairman: ${_shm_escapeHtml(item.current_chairman_name)}</p>
                  </div>
                </div>
                ${item.notes ? `<p class="text-xs text-gray-500 mt-2 italic">Notes: ${_shm_escapeHtml(item.notes)}</p>` : ""}
              </div>
              <div class="flex items-center gap-2 ml-4">
                <button onclick="showHistoryDetailModal(${index}, ${clubId})" class="text-blue-600 hover:text-blue-800">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
                <button onclick="deleteReorganizationRecord(${item.id}, ${clubId}, event)" class="text-red-600 hover:text-red-800 text-sm px-2 py-1">
                  Delete
                </button>
              </div>
            </div>
          </div>
        `,
          )
          .join("")}
      </div>

      <div class="bg-gray-50 border-t px-4 sm:px-6 py-4 flex justify-start flex-shrink-0">
        <button onclick="this.closest('.fixed').remove()" class="btn btn-primary">Close</button>
      </div>
    </div>
  `;

  window.clubReorgHistory = history;
  document.body.appendChild(modal);
}

function showHistoryDetailModal(index, clubId) {
  const item = window.clubReorgHistory[index];

  const modal = document.createElement("div");
  modal.className =
    "fixed inset-0 bg-gray-100 bg-opacity-80 flex items-center justify-center z-50 p-4";
  modal.onclick = (e) => {
    if (e.target === modal) modal.remove();
  };

  modal.innerHTML = `
    <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl" style="max-height: 90vh; display: flex; flex-direction: column;" onclick="event.stopPropagation()">
      <div class="bg-white border-b px-4 sm:px-6 py-4 flex justify-between items-center flex-shrink-0">
        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Reorganization Details</h3>
        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div class="p-4 sm:p-6 overflow-y-auto flex-1">
        <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
          <p class="font-semibold text-blue-900">Reorganization Date: ${_shm_formatDate(item.reorg_date)}</p>
          ${item.notes ? `<p class="text-sm text-gray-700 mt-2"><strong>Notes:</strong> ${_shm_escapeHtml(item.notes)}</p>` : ""}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Previous (Before) -->
          <div class="border rounded-lg p-4 bg-red-50">
            <h4 class="font-bold text-red-900 mb-4">Previous (Before)</h4>
            <div class="space-y-3">
              <div>
                <p class="text-xs font-semibold text-gray-600 uppercase">Club Name</p>
                <p class="text-gray-900">${_shm_escapeHtml(item.prev_name)}</p>
              </div>
              <div class="border-t pt-3">
                <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Chairman</p>
                <p class="text-gray-900 font-medium">${_shm_escapeHtml(item.prev_chairman_name)}</p>
                <p class="text-sm text-gray-600 mt-1">${_shm_escapeHtml(item.prev_chairman_address)}</p>
                <p class="text-sm text-gray-600">📞 ${_shm_escapeHtml(item.prev_chairman_phone)}</p>
              </div>
              <div class="border-t pt-3">
                <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Secretary</p>
                <p class="text-gray-900 font-medium">${_shm_escapeHtml(item.prev_secretary_name)}</p>
                <p class="text-sm text-gray-600 mt-1">${_shm_escapeHtml(item.prev_secretary_address)}</p>
                <p class="text-sm text-gray-600">📞 ${_shm_escapeHtml(item.prev_secretary_phone)}</p>
              </div>
            </div>
          </div>

          <!-- Current (After) -->
          <div class="border rounded-lg p-4 bg-green-50">
            <h4 class="font-bold text-green-900 mb-4">Current (After)</h4>
            <div class="space-y-3">
              <div>
                <p class="text-xs font-semibold text-gray-600 uppercase">Club Name</p>
                <p class="text-gray-900">${_shm_escapeHtml(item.current_name)}</p>
              </div>
              <div class="border-t pt-3">
                <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Chairman</p>
                <p class="text-gray-900 font-medium">${_shm_escapeHtml(item.current_chairman_name)}</p>
                <p class="text-sm text-gray-600 mt-1">${_shm_escapeHtml(item.current_chairman_address)}</p>
                <p class="text-sm text-gray-600">📞 ${_shm_escapeHtml(item.current_chairman_phone)}</p>
              </div>
              <div class="border-t pt-3">
                <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Secretary</p>
                <p class="text-gray-900 font-medium">${_shm_escapeHtml(item.current_secretary_name)}</p>
                <p class="text-sm text-gray-600 mt-1">${_shm_escapeHtml(item.current_secretary_address)}</p>
                <p class="text-sm text-gray-600">📞 ${_shm_escapeHtml(item.current_secretary_phone)}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-gray-50 border-t px-4 sm:px-6 py-4 flex justify-start gap-2 flex-shrink-0">
        <button onclick="this.closest('.fixed').remove(); showHistoryListModal(window.clubReorgHistory, ${clubId})" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">
          ← Back to List
        </button>
        <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Close</button>
      </div>
    </div>
  `;

  document.body.appendChild(modal);
}

function deleteReorganizationRecord(reorgId, clubId, event) {
  if (event) event.stopPropagation();

  if (
    !confirm(
      window.i18n
        ? window.i18n.t("message.confirm_delete_reorg_record")
        : "Are you sure you want to delete this reorganization record? This action cannot be undone.",
    )
  ) {
    return;
  }

  fetch("../api/reorganize-club.php", {
    method: "DELETE",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id: reorgId }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert(
          window.i18n
            ? window.i18n.t("message.reorganize_deleted_success")
            : "Reorganization deleted successfully!",
        );
        document.querySelectorAll(".fixed").forEach((modal) => modal.remove());
        if (typeof window.onHistoryDeleteSuccess === "function") {
          window.onHistoryDeleteSuccess(clubId);
        }
      } else {
        alert(
          data.message ||
            (window.i18n
              ? window.i18n.t("message.reorganize_delete_error")
              : "Failed to delete reorganization"),
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert(
        window.i18n
          ? window.i18n.t("message.error_generic")
          : "An error occurred while deleting the reorganization",
      );
    });
}

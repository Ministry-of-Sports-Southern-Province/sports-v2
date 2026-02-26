let currentPage = 1;
let rowsPerPage = 10;
let totalPages = 1;
let totalRows = 0;

document.addEventListener("DOMContentLoaded", function () {
  loadDistricts();
  loadClubs(1);

  document.getElementById("searchInput").addEventListener("input", function () {
    clearTimeout(window._reorgSearchTimer);
    window._reorgSearchTimer = setTimeout(function () {
      loadClubs(1);
    }, 300);
  });
  document
    .getElementById("districtFilter")
    .addEventListener("change", function () {
      loadClubs(1);
    });
  document
    .getElementById("statusFilter")
    .addEventListener("change", function () {
      loadClubs(1);
    });
});

function loadDistricts() {
  fetch("../api/locations.php?type=district")
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        const select = document.getElementById("districtFilter");
        select.innerHTML = "";
        const allOpt = document.createElement("option");
        allOpt.value = "";
        allOpt.setAttribute("data-i18n", "filter.all_districts");
        allOpt.textContent = "සියලු දිස්ත්රික්ක";
        select.appendChild(allOpt);
        data.data.forEach((d) => {
          const opt = document.createElement("option");
          opt.value = d.id;
          opt.textContent = d.name;
          select.appendChild(opt);
        });
      }
    });
}

function getReorgParams() {
  const params = new URLSearchParams();
  const search = document.getElementById("searchInput")?.value?.trim() || "";
  const districtId = document.getElementById("districtFilter")?.value || "";
  const status = document.getElementById("statusFilter")?.value || "";
  if (search) params.append("search", search);
  if (districtId) params.append("district_id", districtId);
  if (status) params.append("reorg_status", status);
  return params;
}

function loadClubs(page) {
  currentPage = page || 1;
  const tbody = document.getElementById("clubsTable");
  tbody.innerHTML =
    '<tr><td colspan="7" class="py-8 text-center text-slate-500" data-i18n="message.loading">පූරණය වෙමින්...</td></tr>';

  const params = getReorgParams();
  params.append("page", String(currentPage));
  params.append("limit", String(rowsPerPage));

  fetch("../api/clubs-list.php?" + params.toString())
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        displayClubs(data.data, currentPage);
        renderPagination(data.pagination || null);
        if (window.i18n && window.i18n.applyTranslations) {
          window.i18n.applyTranslations();
        }
      } else {
        tbody.innerHTML =
          '<tr><td colspan="7" class="py-8 text-center text-red-600">දෝෂයකි</td></tr>';
      }
    })
    .catch((err) => {
      console.error(err);
      tbody.innerHTML =
        '<tr><td colspan="7" class="py-8 text-center text-red-600">දෝෂයකි</td></tr>';
    });
}

function displayClubs(clubs, pageOneBased) {
  const tbody = document.getElementById("clubsTable");
  if (!clubs || clubs.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="7" class="py-8 text-center text-slate-500">දත්ත නොමැත</td></tr>';
    return;
  }
  const start = ((pageOneBased || 1) - 1) * rowsPerPage;
  tbody.innerHTML = clubs
    .map((club, i) => {
      const status = club.reorg_status || "expired";
      const statusClass =
        status === "active"
          ? "bg-green-100 text-green-800"
          : "bg-yellow-100 text-yellow-800";
      return `
            <tr>
                <td class="font-medium text-slate-900">${start + i + 1}</td>
                <td class="font-medium text-slate-900">${escapeHtml(club.reg_number)}</td>
                <td class="text-slate-900">${escapeHtml(club.name)}</td>
                <td class="text-slate-700">${escapeHtml(club.district_name || "-")}</td>
                <td class="text-slate-700">${club.last_reorg_date || "N/A"}</td>
                <td><span class="px-2 py-1 rounded text-sm font-medium ${statusClass}" data-i18n="status.${status}">${status === "active" ? "සක්රීය" : "කල් ඉකුත්"}</span></td>
                <td>
                    <button onclick="viewHistory(${club.id})" class="text-blue-600 hover:text-blue-800 mr-2" data-i18n="button.view_history">ඉතිහාසය</button>
                    ${
                      window.currentUserRole === "admin"
                        ? `
                    <button onclick="renewClub(${club.id})" class="text-green-600 hover:text-green-800 mr-2" data-i18n="button.renew">නවීකරණය</button>
                    <button onclick="deleteReorg(${club.id})" class="text-red-600 hover:text-red-800" data-i18n="button.delete">මකන්න</button>
                    `
                        : ""
                    }
                </td>
            </tr>
        `;
    })
    .join("");
}

function escapeHtml(text) {
  if (text == null) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function renderPagination(pagination) {
  const container = document.getElementById("pagination");
  const infoEl = document.getElementById("paginationInfo");
  if (!container) return;
  if (!pagination) {
    container.innerHTML = "";
    if (infoEl) infoEl.textContent = "";
    return;
  }
  totalPages = pagination.total_pages || 1;
  totalRows = pagination.total || 0;
  container.innerHTML = "";

  const prevBtn = document.createElement("button");
  prevBtn.textContent = "Prev";
  prevBtn.disabled = currentPage === 1;
  prevBtn.className =
    "px-3 py-1 border rounded " +
    (prevBtn.disabled
      ? "bg-gray-100 text-gray-400 cursor-not-allowed"
      : "bg-white hover:bg-gray-50");
  prevBtn.onclick = function () {
    loadClubs(currentPage - 1);
  };
  container.appendChild(prevBtn);

  const windowSize = 3;
  const start = Math.max(1, currentPage - windowSize);
  const end = Math.min(totalPages, currentPage + windowSize);

  if (start > 1) {
    const firstBtn = document.createElement("button");
    firstBtn.textContent = "1";
    firstBtn.className = "px-3 py-1 border rounded bg-white hover:bg-gray-50";
    firstBtn.onclick = function () {
      loadClubs(1);
    };
    container.appendChild(firstBtn);
    if (start > 2) {
      const dots = document.createElement("span");
      dots.textContent = "...";
      dots.className = "px-2 text-gray-500";
      container.appendChild(dots);
    }
  }
  for (let i = start; i <= end; i++) {
    const btn = document.createElement("button");
    btn.textContent = i;
    btn.className =
      "px-3 py-1 border rounded " +
      (i === currentPage
        ? "bg-blue-600 text-white"
        : "bg-white hover:bg-gray-50");
    btn.onclick = function () {
      loadClubs(i);
    };
    container.appendChild(btn);
  }
  if (end < totalPages) {
    if (end < totalPages - 1) {
      const dots = document.createElement("span");
      dots.textContent = "...";
      dots.className = "px-2 text-gray-500";
      container.appendChild(dots);
    }
    const lastBtn = document.createElement("button");
    lastBtn.textContent = String(totalPages);
    lastBtn.className = "px-3 py-1 border rounded bg-white hover:bg-gray-50";
    lastBtn.onclick = function () {
      loadClubs(totalPages);
    };
    container.appendChild(lastBtn);
  }
  const nextBtn = document.createElement("button");
  nextBtn.textContent = "Next";
  nextBtn.disabled = currentPage === totalPages;
  nextBtn.className =
    "px-3 py-1 border rounded " +
    (nextBtn.disabled
      ? "bg-gray-100 text-gray-400 cursor-not-allowed"
      : "bg-white hover:bg-gray-50");
  nextBtn.onclick = function () {
    loadClubs(currentPage + 1);
  };
  container.appendChild(nextBtn);

  if (infoEl) {
    const from = totalRows === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
    const to = Math.min(currentPage * rowsPerPage, totalRows);
    infoEl.textContent =
      window.i18n && window.i18n.t("pagination.showing")
        ? window.i18n
            .t("pagination.showing")
            .replace("{from}", from)
            .replace("{to}", to)
            .replace("{total}", totalRows)
        : "Showing " + from + "–" + to + " of " + totalRows;
  }
}

function escapeHtml(text) {
  if (text == null) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function formatDate(dateString) {
  if (!dateString) return "-";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" });
}

function filterClubs() {
  loadClubs(1);
}

function viewHistory(clubId) {
    // Fetch and show history in modal
    fetch(`../api/reorganize-club.php?club_id=${clubId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                showHistoryListModal(data.data, clubId);
            } else {
                alert('No reorganization history found for this club.');
            }
        })
        .catch(error => {
            console.error('Error loading history:', error);
            alert('Failed to load reorganization history.');
        });
}

function showHistoryListModal(history, clubId) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-100 bg-opacity-80 flex items-center justify-center z-50 p-4';
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
                ${history.map((item, index) => `
                    <div class="border rounded-lg p-4 mb-4 hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1 cursor-pointer" onclick="showHistoryDetailModal(${index}, ${clubId})">
                                <p class="font-semibold text-blue-900 text-lg">${formatDate(item.reorg_date)}</p>
                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-600 font-medium">Previous:</p>
                                        <p class="text-gray-900">${escapeHtml(item.prev_name)}</p>
                                        <p class="text-gray-600 text-xs mt-1">Chairman: ${escapeHtml(item.prev_chairman_name)}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600 font-medium">Current:</p>
                                        <p class="text-gray-900">${escapeHtml(item.current_name)}</p>
                                        <p class="text-gray-600 text-xs mt-1">Chairman: ${escapeHtml(item.current_chairman_name)}</p>
                                    </div>
                                </div>
                                ${item.notes ? `<p class="text-xs text-gray-500 mt-2 italic">Notes: ${escapeHtml(item.notes)}</p>` : ''}
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
                `).join('')}
            </div>
            
            <div class="bg-gray-50 border-t px-4 sm:px-6 py-4 flex justify-start flex-shrink-0">
                <button onclick="this.closest('.fixed').remove()" class="btn btn-primary">Close</button>
            </div>
        </div>
    `;
    
    // Store history globally for detail modal
    window.clubReorgHistory = history;
    
    document.body.appendChild(modal);
}

function showHistoryDetailModal(index, clubId) {
    const item = window.clubReorgHistory[index];
    
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-100 bg-opacity-80 flex items-center justify-center z-50 p-4';
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
                    <p class="font-semibold text-blue-900">Reorganization Date: ${formatDate(item.reorg_date)}</p>
                    ${item.notes ? `<p class="text-sm text-gray-700 mt-2"><strong>Notes:</strong> ${escapeHtml(item.notes)}</p>` : ''}
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border rounded-lg p-4 bg-red-50">
                        <h4 class="font-bold text-red-900 mb-4">Previous (Before)</h4>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase">Club Name</p>
                                <p class="text-gray-900">${escapeHtml(item.prev_name)}</p>
                            </div>
                            <div class="border-t pt-3">
                                <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Chairman</p>
                                <p class="text-gray-900 font-medium">${escapeHtml(item.prev_chairman_name)}</p>
                                <p class="text-sm text-gray-600 mt-1">${escapeHtml(item.prev_chairman_address)}</p>
                                <p class="text-sm text-gray-600">📞 ${escapeHtml(item.prev_chairman_phone)}</p>
                            </div>
                            <div class="border-t pt-3">
                                <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Secretary</p>
                                <p class="text-gray-900 font-medium">${escapeHtml(item.prev_secretary_name)}</p>
                                <p class="text-sm text-gray-600 mt-1">${escapeHtml(item.prev_secretary_address)}</p>
                                <p class="text-sm text-gray-600">📞 ${escapeHtml(item.prev_secretary_phone)}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border rounded-lg p-4 bg-green-50">
                        <h4 class="font-bold text-green-900 mb-4">Current (After)</h4>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase">Club Name</p>
                                <p class="text-gray-900">${escapeHtml(item.current_name)}</p>
                            </div>
                            <div class="border-t pt-3">
                                <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Chairman</p>
                                <p class="text-gray-900 font-medium">${escapeHtml(item.current_chairman_name)}</p>
                                <p class="text-sm text-gray-600 mt-1">Current address and phone</p>
                            </div>
                            <div class="border-t pt-3">
                                <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Secretary</p>
                                <p class="text-gray-900 font-medium">${escapeHtml(item.current_secretary_name)}</p>
                                <p class="text-sm text-gray-600 mt-1">Current address and phone</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 border-t px-4 sm:px-6 py-4 flex justify-start flex-shrink-0">
                <button onclick="this.closest('.fixed').remove(); showHistoryListModal(window.clubReorgHistory, ${clubId})" class="btn btn-outline">
                    ← Back to List
                </button>
                <button onclick="this.closest('.fixed').remove()" class="btn btn-primary">Close</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function renewClub(clubId) {
  window.location.href = "reorganize-club.php?id=" + clubId;
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
            : "Deleted",
        );
        loadClubs(currentPage);
      } else {
        alert(
          data.message ||
            (window.i18n ? window.i18n.t("message.error_generic") : "Error"),
        );
      }
    })
    .catch((err) => {
      console.error(err);
      alert(window.i18n ? window.i18n.t("message.error_generic") : "Error");
    });
}

/**
 * Delete a specific reorganization record
 */
function deleteReorganizationRecord(reorgId, clubId, event) {
  event.stopPropagation();
  
  if (!confirm("Are you sure you want to delete this reorganization record? This action cannot be undone.")) {
    return;
  }

  fetch("../api/reorganize-club.php", {
    method: "DELETE",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ id: reorgId }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Reorganization deleted successfully!");
        // Close any open modals
        document.querySelectorAll('.fixed').forEach(modal => modal.remove());
        // Reload the club list
        loadClubs(currentPage);
      } else {
        alert(data.message || "Failed to delete reorganization");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred while deleting the reorganization");
    });
}

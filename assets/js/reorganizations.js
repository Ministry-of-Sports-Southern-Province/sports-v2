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
  document.getElementById("districtFilter").addEventListener("change", function () {
    loadClubs(1);
  });
  document.getElementById("statusFilter").addEventListener("change", function () {
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
                    ${(window.currentUserRole === "admin") ? `
                    <button onclick="renewClub(${club.id})" class="text-green-600 hover:text-green-800 mr-2" data-i18n="button.renew">නවීකරණය</button>
                    <button onclick="deleteReorg(${club.id})" class="text-red-600 hover:text-red-800" data-i18n="button.delete">මකන්න</button>
                    ` : ""}
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
      (i === currentPage ? "bg-blue-600 text-white" : "bg-white hover:bg-gray-50");
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
    infoEl.textContent = (window.i18n && window.i18n.t("pagination.showing"))
      ? window.i18n.t("pagination.showing").replace("{from}", from).replace("{to}", to).replace("{total}", totalRows)
      : "Showing " + from + "–" + to + " of " + totalRows;
  }
}

function filterClubs() {
  loadClubs(1);
}

function viewHistory(clubId) {
  window.location.href = "club-details.php?id=" + clubId;
}

function renewClub(clubId) {
  if (!confirm("මෙම සමාජය ප්රතිසංවිධාන කිරීමට අවශ්‍යද?")) return;

  fetch("../api/reorganizations.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      club_id: clubId,
      reorg_date: new Date().toISOString().split("T")[0],
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        alert(window.i18n ? window.i18n.t("message.reorg_added_success") : "Success");
        loadClubs(currentPage);
      } else {
        alert(data.message || (window.i18n ? window.i18n.t("message.error_generic") : "Error"));
      }
    })
    .catch((err) => {
      console.error(err);
      alert(window.i18n ? window.i18n.t("message.error_generic") : "Error");
    });
}

function deleteReorg(clubId) {
  if (!confirm("අවසාන ප්රතිසංවිධාන දිනය ඉවත් කිරීමට අවශ්‍යද?")) return;

  fetch("../api/reorganizations.php", {
    method: "DELETE",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ club_id: clubId }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        alert(window.i18n ? window.i18n.t("message.reorg_deleted_success") : "Deleted");
        loadClubs(currentPage);
      } else {
        alert(data.message || (window.i18n ? window.i18n.t("message.error_generic") : "Error"));
      }
    })
    .catch((err) => {
      console.error(err);
      alert(window.i18n ? window.i18n.t("message.error_generic") : "Error");
    });
}

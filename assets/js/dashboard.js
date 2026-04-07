/* ================================
   API BASE (relative to public/ so filters & search work)
================================ */
var apiBase =
  typeof window.API_BASE !== "undefined" ? window.API_BASE : "../api";

/* ================================
   PAGINATION STATE
================================ */
let currentPage = 1;
let rowsPerPage = 10;
let totalPages = 1;
let totalRows = 0;

function getClubFiltersParams() {
  const search = document.getElementById("searchInput")?.value || "";
  const districtId = document.getElementById("filterDistrict")?.value || "";
  const divisionId = document.getElementById("filterDivision")?.value || "";
  const gsDivisionId = document.getElementById("filterGsDivision")?.value || "";
  const status = document.getElementById("filterStatus")?.value || "";

  const params = new URLSearchParams();
  if (search) params.append("search", search);
  if (districtId) params.append("district_id", districtId);
  if (divisionId) params.append("division_id", divisionId);
  if (gsDivisionId) params.append("gs_division_id", gsDivisionId);
  if (status) params.append("reorg_status", status);
  return params;
}

async function fetchClubs({
  page = 1,
  limit = rowsPerPage,
  printAll = false,
} = {}) {
  const params = getClubFiltersParams();
  if (printAll) {
    params.append("print_all", "1");
  } else {
    params.append("page", String(page));
    params.append("limit", String(limit));
  }

  const res = await fetch(`${apiBase}/clubs-list.php?${params.toString()}`);
  return await res.json();
}

/* ================================
   LOAD CLUBS (WITH PAGINATION)
================================ */
function loadClubs(page = 1) {
  currentPage = page;

  const tbody = document.getElementById("clubsTableBody");
  tbody.innerHTML = `
    <tr>
      <td colspan="12" class="px-6 py-4 text-center text-gray-500">
        <span data-i18n="message.loading">Loading...</span>
      </td>
    </tr>`;

  fetchClubs({ page: currentPage, limit: rowsPerPage })
    .then((data) => {
      if (data.success) {
        displayClubs(data.data);
        renderPagination(data.pagination || null);

        if (
          window.i18n &&
          typeof window.i18n.applyTranslations === "function"
        ) {
          window.i18n.applyTranslations();
        }
      } else {
        tbody.innerHTML = `
          <tr>
            <td colspan="12" class="text-center text-red-500 py-4">
              Failed to load data
            </td>
          </tr>`;
      }
    })
    .catch(() => {
      tbody.innerHTML = `
        <tr>
          <td colspan="12" class="text-center text-red-500 py-4">
            Server error
          </td>
        </tr>`;
    });
}

/* ================================
   PAGINATION UI
================================ */
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

  // Prev
  const prevBtn = document.createElement("button");
  prevBtn.textContent = "Prev";
  prevBtn.disabled = currentPage === 1;
  prevBtn.className =
    "px-3 py-1 border rounded " +
    (prevBtn.disabled
      ? "bg-gray-100 text-gray-400 cursor-not-allowed"
      : "bg-white hover:bg-gray-50");
  prevBtn.onclick = () => loadClubs(currentPage - 1);
  container.appendChild(prevBtn);

  // Pages (windowed)
  const windowSize = 3;
  const start = Math.max(1, currentPage - windowSize);
  const end = Math.min(totalPages, currentPage + windowSize);

  if (start > 1) {
    const firstBtn = document.createElement("button");
    firstBtn.textContent = "1";
    firstBtn.className = "px-3 py-1 border rounded bg-white hover:bg-gray-50";
    firstBtn.onclick = () => loadClubs(1);
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
      (i === currentPage ? "bg-blue-600 text-white" : "bg-white");
    btn.onclick = () => loadClubs(i);
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
    lastBtn.onclick = () => loadClubs(totalPages);
    container.appendChild(lastBtn);
  }

  // Next
  const nextBtn = document.createElement("button");
  nextBtn.textContent = "Next";
  nextBtn.disabled = currentPage === totalPages;
  nextBtn.className =
    "px-3 py-1 border rounded " +
    (nextBtn.disabled
      ? "bg-gray-100 text-gray-400 cursor-not-allowed"
      : "bg-white hover:bg-gray-50");
  nextBtn.onclick = () => loadClubs(currentPage + 1);
  container.appendChild(nextBtn);

  if (infoEl) {
    const startRow = totalRows === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
    const endRow = Math.min(currentPage * rowsPerPage, totalRows);
    infoEl.textContent = `Showing ${startRow}–${endRow} of ${totalRows} (Page ${currentPage} of ${totalPages})`;
  }
}

/* ================================
   SEARCH & FILTER EVENTS
================================ */
// (Initialized in the main DOMContentLoaded below)

/**
 * Load dashboard statistics
 */
function loadStatistics() {
  fetch(apiBase + "/statistics.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const stats = data.data;

        // Update total clubs
        const totalEl = document.getElementById("statTotalClubs");
        if (totalEl) totalEl.textContent = stats.total_clubs;

        // Dynamically create district cards
        if (stats.clubs_by_district && stats.clubs_by_district.length > 0) {
          const container = document.getElementById("statisticsContainer");

          // Remove only dynamically added district cards (keep the first Total Clubs card)
          const districtCards = container.querySelectorAll(
            ".stat-card:not(:first-child)",
          );
          districtCards.forEach((card) => card.remove());

          // Define colors for districts (cycle through these)
          const colors = [
            { border: "#10b981", text: "text-green-600" }, // Green
            { border: "#8b5cf6", text: "text-purple-600" }, // Purple
            { border: "#f59e0b", text: "text-amber-600" }, // Amber
            { border: "#ec4899", text: "text-pink-600" }, // Pink
            { border: "#06b6d4", text: "text-cyan-600" }, // Cyan
            { border: "#f97316", text: "text-orange-600" }, // Orange
          ];

          stats.clubs_by_district.forEach((district, index) => {
            const color = colors[index % colors.length];

            const card = document.createElement("div");
            card.className = "stat-card";
            card.style.borderLeftColor = color.border;

            card.innerHTML = `
              <div class="flex items-center justify-between">
                <div>
                  <p class="stat-label">${escapeHtml(district.district_name)}</p>
                  <p class="stat-value mt-1">${district.count}</p>
                </div>
                <div class="${color.text}">
                  <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
              </div>
            `;

            container.appendChild(card);
          });

          // Create charts
          createDistrictCharts(stats.clubs_by_district);
        }
      }
    })
    .catch((error) => console.error("Error loading statistics:", error));
}

/**
 * Create district charts (Pie, Bar, and Doughnut)
 */
let pieChartInstance = null;
let barChartInstance = null;
let doughnutChartInstance = null;

function createDistrictCharts(districtData) {
  const labels = districtData.map((d) => d.district_name);
  const data = districtData.map((d) => d.count);
  const total = data.reduce((a, b) => a + b, 0);

  // Define colors matching the stat cards
  const colors = [
    "#10b981", // Green
    "#8b5cf6", // Purple
    "#f59e0b", // Amber
    "#ec4899", // Pink
    "#06b6d4", // Cyan
    "#f97316", // Orange
  ];

  const backgroundColors = districtData.map(
    (_, index) => colors[index % colors.length],
  );

  // Destroy existing charts if they exist
  if (pieChartInstance) pieChartInstance.destroy();
  if (barChartInstance) barChartInstance.destroy();
  if (doughnutChartInstance) doughnutChartInstance.destroy();

  // Create Pie Chart
  const pieCtx = document.getElementById("districtPieChart");
  if (pieCtx) {
    pieChartInstance = new Chart(pieCtx, {
      type: "pie",
      data: {
        labels: labels,
        datasets: [
          {
            data: data,
            backgroundColor: backgroundColors,
            borderWidth: 2,
            borderColor: "#fff",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              font: {
                family:
                  "Noto Sans Sinhala, Noto Sans Tamil, Roboto, sans-serif",
                size: 10,
              },
              padding: 8,
              usePointStyle: true,
              boxWidth: 8,
            },
          },
          tooltip: {
            backgroundColor: "rgba(0, 0, 0, 0.8)",
            padding: 8,
            titleFont: {
              family: "Noto Sans Sinhala, Noto Sans Tamil, Roboto, sans-serif",
              size: 11,
            },
            bodyFont: {
              family: "Noto Sans Sinhala, Noto Sans Tamil, Roboto, sans-serif",
              size: 10,
            },
            callbacks: {
              label: function (context) {
                const value = context.parsed || 0;
                const percentage = ((value / total) * 100).toFixed(1);
                return `${value} (${percentage}%)`;
              },
            },
          },
        },
      },
    });
  }

  // Create Bar Chart
  const barCtx = document.getElementById("districtBarChart");
  if (barCtx) {
    barChartInstance = new Chart(barCtx, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            data: data,
            backgroundColor: backgroundColors,
            borderRadius: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: "rgba(0, 0, 0, 0.8)",
            padding: 8,
            titleFont: {
              family: "Noto Sans Sinhala, Noto Sans Tamil, Roboto, sans-serif",
              size: 11,
            },
            bodyFont: {
              family: "Noto Sans Sinhala, Noto Sans Tamil, Roboto, sans-serif",
              size: 10,
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1, font: { size: 9 } },
            grid: { color: "rgba(0, 0, 0, 0.05)" },
          },
          x: {
            ticks: {
              font: {
                family:
                  "Noto Sans Sinhala, Noto Sans Tamil, Roboto, sans-serif",
                size: 9,
              },
            },
            grid: { display: false },
          },
        },
      },
    });
  }

  // Create Doughnut Chart (Overview)
  const doughnutCtx = document.getElementById("overviewDoughnutChart");
  if (doughnutCtx) {
    doughnutChartInstance = new Chart(doughnutCtx, {
      type: "doughnut",
      data: {
        labels: labels,
        datasets: [
          {
            data: data,
            backgroundColor: backgroundColors,
            borderWidth: 2,
            borderColor: "#fff",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: "60%",
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: "rgba(0, 0, 0, 0.8)",
            padding: 8,
            titleFont: {
              family: "Noto Sans Sinhala, Noto Sans Tamil, Roboto, sans-serif",
              size: 11,
            },
            bodyFont: {
              family: "Noto Sans Sinhala, Noto Sans Tamil, Roboto, sans-serif",
              size: 10,
            },
            callbacks: {
              label: function (context) {
                const value = context.parsed || 0;
                const percentage = ((value / total) * 100).toFixed(1);
                return `${context.label}: ${value} (${percentage}%)`;
              },
            },
          },
        },
      },
      plugins: [
        {
          id: "centerText",
          beforeDraw: function (chart) {
            const width = chart.width;
            const height = chart.height;
            const ctx = chart.ctx;
            ctx.restore();
            const fontSize = (height / 100).toFixed(2);
            ctx.font = "bold " + fontSize + "em sans-serif";
            ctx.textBaseline = "middle";
            ctx.fillStyle = "#374151";
            const text = total.toString();
            const textX = Math.round((width - ctx.measureText(text).width) / 2);
            const textY = height / 2;
            ctx.fillText(text, textX, textY);
            ctx.font = "normal " + fontSize * 0.4 + "em sans-serif";
            ctx.fillStyle = "#6b7280";
            const subText = "Total";
            const subTextX = Math.round(
              (width - ctx.measureText(subText).width) / 2,
            );
            ctx.fillText(subText, subTextX, textY + 20);
            ctx.save();
          },
        },
      ],
    });
  }
}
/**
 * Dashboard JavaScript
 * Handles club listing, search, and filters
 */

// Debounce function for live search
let searchTimeout;
function debounce(func, delay) {
  return function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(func, delay);
  };
}

// Load clubs when page loads
document.addEventListener("DOMContentLoaded", function () {
  initializeDropdowns();
  loadClubs(1);
  loadStatistics();

  // Live search with debounce
  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    // Search as user types (with 500ms debounce)
    searchInput.addEventListener(
      "input",
      debounce(function () {
        loadClubs(1);
      }, 500),
    );

    // Also support Enter key for immediate search
    searchInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        clearTimeout(searchTimeout); // Cancel debounce
        loadClubs(1);
      }
    });
  }
});

/**
 * Initialize native dropdown filters
 */
function initializeDropdowns() {
  const districtSelect = document.getElementById("filterDistrict");
  const divisionSelect = document.getElementById("filterDivision");
  const gsDivisionSelect = document.getElementById("filterGsDivision");

  // Add change event listeners
  districtSelect.addEventListener("change", function () {
    handleDistrictChange(this.value);
  });

  divisionSelect.addEventListener("change", function () {
    handleDivisionChange(this.value);
  });

  gsDivisionSelect.addEventListener("change", function () {
    loadClubs(1);
  });

  const statusSelect = document.getElementById("filterStatus");
  if (statusSelect) {
    statusSelect.addEventListener("change", function () {
      loadClubs(1);
    });
  }

  // Load all districts
  fetch(apiBase + "/locations.php?type=district")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        data.data.forEach((district) => {
          const option = document.createElement("option");
          option.value = district.id;
          option.textContent = district.name;
          option.setAttribute("data-search", district.name.toLowerCase());
          districtSelect.appendChild(option);
        });
        // Enable search on district dropdown
        enableSelectSearch(districtSelect);
      }
    })
    .catch((error) => console.error("Error loading districts:", error));
}

/**
 * Enable real-time search on select dropdown
 */
function enableSelectSearch(selectElement) {
  let searchTimeout;
  let allOptions = Array.from(selectElement.options);

  selectElement.addEventListener("keydown", function (e) {
    // Prevent default arrow key behavior during search
    if (e.key.length === 1 || e.key === "Backspace") {
      e.preventDefault();
    }
  });

  selectElement.addEventListener("keyup", function (e) {
    if (e.key.length === 1 || e.key === "Backspace") {
      clearTimeout(searchTimeout);

      searchTimeout = setTimeout(() => {
        const searchTerm =
          (selectElement.getAttribute("data-current-search") || "") +
          (e.key.length === 1 ? e.key : "");

        if (e.key === "Backspace") {
          const currentSearch =
            selectElement.getAttribute("data-current-search") || "";
          selectElement.setAttribute(
            "data-current-search",
            currentSearch.slice(0, -1),
          );
        } else if (e.key.length === 1) {
          selectElement.setAttribute(
            "data-current-search",
            searchTerm.toLowerCase(),
          );
        }

        const search = selectElement.getAttribute("data-current-search") || "";

        if (search) {
          // Find matching option
          const matchingOption = allOptions.find(
            (opt) =>
              opt.getAttribute("data-search") &&
              opt.getAttribute("data-search").startsWith(search),
          );

          if (matchingOption) {
            selectElement.value = matchingOption.value;
            // Trigger change event if value changed
            selectElement.dispatchEvent(new Event("change"));
          }
        }

        // Clear search after 1 second
        setTimeout(() => {
          selectElement.setAttribute("data-current-search", "");
        }, 1000);
      }, 100);
    }
  });
}

/**
 * Handle district change
 */
function handleDistrictChange(districtId) {
  console.log("handleDistrictChange called with:", districtId);

  const divisionSelect = document.getElementById("filterDivision");
  const gsDivisionSelect = document.getElementById("filterGsDivision");

  // Clear division and GN division
  divisionSelect.innerHTML =
    '<option value="" data-i18n="placeholder.select_district_first"></option>';
  gsDivisionSelect.innerHTML =
    '<option value="" data-i18n="placeholder.select_division_first"></option>';

  // Update translations if i18n is available
  if (window.i18n) {
    window.i18n.updatePageTranslations();
  }

  if (districtId) {
    // Load divisions
    const url = `${apiBase}/locations.php?type=division&parent_id=${districtId}`;
    console.log("Fetching divisions from:", url);

    fetch(url)
      .then((response) => {
        console.log("Division response status:", response.status);
        return response.json();
      })
      .then((data) => {
        console.log("Division data received:", data);
        if (data.success && data.data) {
          data.data.forEach((division) => {
            const option = document.createElement("option");
            option.value = division.id;
            option.textContent = division.name;
            option.setAttribute("data-search", division.name.toLowerCase());
            divisionSelect.appendChild(option);
          });
          console.log(`Added ${data.data.length} divisions to dropdown`);
          enableSelectSearch(divisionSelect);
        }
      })
      .catch((error) => console.error("Error loading divisions:", error));
  }

  loadClubs(1);
}

/**
 * Handle division change
 */
function handleDivisionChange(divisionId) {
  console.log("handleDivisionChange called with:", divisionId);

  const gsDivisionSelect = document.getElementById("filterGsDivision");

  // Clear GN division
  gsDivisionSelect.innerHTML =
    '<option value="" data-i18n="placeholder.select_division_first"></option>';

  // Update translations if i18n is available
  if (window.i18n) {
    window.i18n.updatePageTranslations();
  }

  if (divisionId) {
    // Load GS divisions
    const url = `${apiBase}/locations.php?type=gs_division&parent_id=${divisionId}`;
    console.log("Fetching GS divisions from:", url);

    fetch(url)
      .then((response) => {
        console.log("GS Division response status:", response.status);
        return response.json();
      })
      .then((data) => {
        console.log("GS Division data received:", data);
        if (data.success && data.data) {
          data.data.forEach((gsDivision) => {
            const option = document.createElement("option");
            option.value = gsDivision.id;
            option.textContent = gsDivision.name;
            option.setAttribute("data-search", gsDivision.name.toLowerCase());
            gsDivisionSelect.appendChild(option);
          });
          console.log(`Added ${data.data.length} GS divisions to dropdown`);
          enableSelectSearch(gsDivisionSelect);
        }
      })
      .catch((error) => console.error("Error loading GS divisions:", error));
  }

  loadClubs(1);
}

/**
 * Display clubs in table
 */
function displayClubs(clubs) {
  currentClubsData = clubs; // Store for printing
  const tbody = document.getElementById("clubsTableBody");

  if (clubs.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="12" class="py-8 text-center text-slate-500"><span data-i18n="table.no_data">No data available</span></td></tr>';

    // Update translations for dynamically added content
    if (window.i18n && typeof window.i18n.applyTranslations === "function") {
      window.i18n.applyTranslations();
    }
    return;
  }

  tbody.innerHTML = "";

  clubs.forEach((club) => {
    const tr = document.createElement("tr");

    // Calculate next reorganization due date
    let nextReorgDate = "";
    if (club.last_reorg_date) {
      const lastDate = new Date(club.last_reorg_date);
      const lastMonth = lastDate.getMonth() + 1; // 1-12
      const lastYear = lastDate.getFullYear();

      let nextYear = lastYear;
      let nextMonth = lastMonth;
      let nextDay = lastDate.getDate();

      if (lastMonth >= 7) {
        // If reorg is in July or later, next due is 2 years later, January 1st
        nextYear = lastYear + 2;
        nextMonth = 1;
        nextDay = 1;
      } else {
        // If reorg is Jan-June, next due is 1 year later, January 1st
        nextYear = lastYear + 1;
        nextMonth = 1;
        nextDay = 1;
      }

      nextReorgDate = formatDate(
        new Date(nextYear, nextMonth - 1, nextDay).toISOString().split("T")[0],
      );
    }

    tr.innerHTML = `
            <td class="whitespace-nowrap text-slate-900 font-medium">${escapeHtml(club.reg_number)}</td>
            <td class="whitespace-nowrap text-slate-700">${formatDate(club.registration_date)}</td>
            <td class="text-slate-900 font-medium">${escapeHtml(club.name)}</td>
            <td class="text-slate-700">${escapeHtml(club.division_name || "")}</td>
            <td class="text-slate-700">${escapeHtml(club.gs_division_name || "")}</td>
            <td class="text-slate-700">${escapeHtml(club.chairman_name || "")}</td>
            <td class="text-slate-700">${escapeHtml(club.chairman_address || "")} ${club.chairman_phone ? "(" + escapeHtml(club.chairman_phone) + ")" : ""}</td>
            <td class="text-slate-700">${escapeHtml(club.secretary_name || "")}</td>
            <td class="text-slate-700">${escapeHtml(club.secretary_address || "")} ${club.secretary_phone ? "(" + escapeHtml(club.secretary_phone) + ")" : ""}</td>
            <td class="whitespace-nowrap text-slate-700">${club.last_reorg_date ? formatDate(club.last_reorg_date) : "-"}</td>
            <td class="whitespace-nowrap text-slate-700">${nextReorgDate || "-"}</td>
            <td class="whitespace-nowrap">
                <div class="flex items-center gap-3">
                    <a href="club-details.php?id=${club.id}" 
                       class="text-blue-600 hover:text-blue-800 transition" 
                       title="View Details">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                    ${
                      window.currentUserRole === "admin"
                        ? `
                    <a href="reorganize-club.php?id=${club.id}" 
                       class="text-purple-600 hover:text-purple-800 transition" 
                       title="Reorganize">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </a>
                    <button onclick="editClub(${club.id})" 
                            class="text-green-600 hover:text-green-800 transition" 
                            title="Edit">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <button onclick="deleteClub(${club.id}, '${escapeHtml(club.name).replace(/'/g, "\\'")}')" 
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
        `;
    tbody.appendChild(tr);
  });

  // Update translations for dynamically added content
  if (window.i18n && typeof window.i18n.applyTranslations === "function") {
    window.i18n.applyTranslations();
  }
}

/**
 * Update statistics
 */
function updateStats(stats) {
  const totalEl = document.getElementById("statTotalClubs");
  const galleEl = document.getElementById("statGalle");
  const mataraEl = document.getElementById("statMatara");
  const hambantotaEl = document.getElementById("statHambantota");

  if (totalEl) totalEl.textContent = stats.total || 0;
  if (galleEl) galleEl.textContent = stats.galle || 0;
  if (mataraEl) mataraEl.textContent = stats.matara || 0;
  if (hambantotaEl) hambantotaEl.textContent = stats.hambantota || 0;
}

/**
 * Reset filters
 */
function resetFilters() {
  document.getElementById("searchInput").value = "";

  // Reset native dropdowns
  const districtSelect = document.getElementById("filterDistrict");
  const divisionSelect = document.getElementById("filterDivision");
  const gsDivisionSelect = document.getElementById("filterGsDivision");

  districtSelect.value = "";
  divisionSelect.innerHTML =
    '<option value="" data-i18n="placeholder.select_district_first"></option>';
  gsDivisionSelect.innerHTML =
    '<option value="" data-i18n="placeholder.select_division_first"></option>';

  const statusSelect = document.getElementById("filterStatus");
  if (statusSelect) statusSelect.value = "";

  // Update translations if i18n is available
  if (window.i18n) {
    window.i18n.updatePageTranslations();
  }

  loadClubs(1);
}

/**
 * Format date
 */
function formatDate(dateString) {
  if (!dateString) return "-";
  const date = new Date(dateString);
  return date.toLocaleDateString("si-LK");
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

/**
 * Edit club - Redirect to registration page with club ID
 */
function editClub(clubId) {
  window.location.href = `edit-club.php?id=${clubId}`;
}

/**
 * Delete club with confirmation
 */
function deleteClub(clubId, clubName) {
  // Get translated confirmation message
  const confirmMessage = window.i18n
    ? window.i18n.t("message.confirm_delete") + "\n\n" + clubName
    : `Are you sure you want to delete this club?\n\n${clubName}`;

  if (!confirm(confirmMessage)) {
    return;
  }

  // Show loading state
  const deleteButton = event.target.closest("button");
  if (deleteButton) {
    deleteButton.disabled = true;
    deleteButton.style.opacity = "0.5";
  }

  // Send delete request
  fetch(`${apiBase}/clubs.php?id=${clubId}`, {
    method: "DELETE",
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Show success message
        const successMessage = window.i18n
          ? window.i18n.t("message.delete_success")
          : "Club deleted successfully";
        alert(successMessage);

        // Reload clubs list
        loadClubs();
        loadStatistics();
      } else {
        // Show error message
        alert(
          data.message ||
            (window.i18n
              ? window.i18n.t("message.delete_error")
              : "Failed to delete club"),
        );

        // Re-enable button
        if (deleteButton) {
          deleteButton.disabled = false;
          deleteButton.style.opacity = "1";
        }
      }
    })
    .catch((error) => {
      console.error("Error deleting club:", error);
      alert(
        window.i18n
          ? window.i18n.t("message.error_generic")
          : "An error occurred while deleting the club",
      );

      // Re-enable button
      if (deleteButton) {
        deleteButton.disabled = false;
        deleteButton.style.opacity = "1";
      }
    });
}

/**
 * Export clubs to Excel (Server-side CSV export)
 */
function exportToExcel() {
  const search = document.getElementById("searchInput").value;
  const districtId = document.getElementById("filterDistrict").value;
  const divisionId = document.getElementById("filterDivision").value;
  const gsDivisionId = document.getElementById("filterGsDivision").value;
  const language = localStorage.getItem("language");

  // Build query parameters
  const params = new URLSearchParams();
  if (search) params.append("search", search);
  if (districtId) params.append("district_id", districtId);
  if (divisionId) params.append("division_id", divisionId);
  if (gsDivisionId) params.append("gs_division_id", gsDivisionId);
  params.append("language", language);

  // Show loading state
  const btn = event.target.closest("button");
  const originalText = btn.textContent;
  btn.disabled = true;
  btn.textContent = "Exporting...";

  try {
    // Create download link and trigger download
    const downloadUrl = `${apiBase}/export-clubs-excel.php?${params.toString()}`;
    const link = document.createElement("a");
    link.href = downloadUrl;
    link.download = `clubs_export_${new Date().toISOString().split("T")[0]}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Reset button
    btn.disabled = false;
    btn.textContent = originalText;
  } catch (error) {
    console.error("Error exporting to Excel:", error);
    alert(
      window.i18n
        ? window.i18n.t("message.export_error")
        : "Failed to export data. Please try again.",
    );
    btn.disabled = false;
    btn.textContent = originalText;
  }
}

/**
 * Store clubs data for printing
 */
let currentClubsData = [];

async function loadAllClubsForPrint() {
  try {
    const data = await fetchClubs({ printAll: true });
    if (data && data.success) {
      currentClubsData = data.data || [];
      return true;
    }
  } catch (e) {
    console.error("Error loading clubs for print:", e);
  }
  return false;
}

/**
 * Populate print container with current data
 */
function populatePrintContainer() {
  const printContainer = document.getElementById("printContainer");
  if (!printContainer) return;

  // Use currentClubsData if available, otherwise fetch
  const data = currentClubsData || [];

  const search = document.getElementById("searchInput").value;
  const districtSelect = document.getElementById("filterDistrict");
  const divisionSelect = document.getElementById("filterDivision");
  const gsDivisionSelect = document.getElementById("filterGsDivision");
  const statusSelect = document.getElementById("filterStatus");

  let filterText = window.i18n
    ? window.i18n.t("filter.all_clubs")
    : "All Clubs";
  if (gsDivisionSelect.value) {
    filterText = gsDivisionSelect.options[gsDivisionSelect.selectedIndex].text;
  } else if (divisionSelect.value) {
    filterText = divisionSelect.options[divisionSelect.selectedIndex].text;
  } else if (districtSelect.value) {
    filterText = districtSelect.options[districtSelect.selectedIndex].text;
  }
  if (statusSelect && statusSelect.value) {
    filterText += ` - ${statusSelect.options[statusSelect.selectedIndex].text}`;
  }
  if (search) {
    filterText += ` - ${window.i18n ? window.i18n.t("placeholder.search") : "Search"}: ${search}`;
  }

  printContainer.innerHTML = "";
  if (data.length === 0) {
    printContainer.innerHTML =
      '<p style="text-align:center;padding:20px;">No data available</p>';
    return;
  }

  const deptName = window.i18n
    ? window.i18n.t("header.department_name")
    : "Department of Sports Southern Province";
  const reportTitle = window.i18n
    ? window.i18n.t("page.clubs_report_title")
    : "Sports Clubs Report";
  const preparedBy = window.i18n
    ? window.i18n.t("footer.prepared_by")
    : "Prepared By";
  const checkedBy = window.i18n
    ? window.i18n.t("footer.checked_by")
    : "Checked By";
  const approvedBy = window.i18n
    ? window.i18n.t("footer.approved_by")
    : "Approved By";

  let html = `
    <div class="print-header">
      <div class="dept-name">${deptName}</div>
      <h1>${reportTitle}</h1>
      <div class="report-subtitle">${filterText}</div>
    </div>
    <table>
      <thead>
        <tr>
          <th style="width:3%;">No.</th>
          <th style="width:7%;">Reg No.</th>
          <th style="width:9%;">Date</th>
          <th style="width:11%;">Club Name</th>
          <th style="width:11%;">Division</th>
          <th style="width:11%;">GS Division</th>
          <th style="width:17%;">Chairman (Name, Address & Phone)</th>
          <th style="width:17%;">Secretary (Name, Address & Phone)</th>
          <th style="width:7%;">Last Reorg</th>
          <th style="width:7%;">Next Reorg</th>
        </tr>
      </thead>
      <tbody>
  `;

  data.forEach((club, idx) => {
    html += `
      <tr>
        <td style="text-align:center;">${idx + 1}</td>
        <td>${escapeHtml(club.reg_number || "-")}</td>
        <td style="text-align:center;white-space:nowrap;">${formatDate(club.registration_date)}</td>
        <td>${escapeHtml(club.name)}</td>
        <td>${escapeHtml(club.division_name || "-")}</td>
        <td>${escapeHtml(club.gs_division_name || "-")}</td>
        <td>${escapeHtml(club.chairman_name || "-")}${club.chairman_address ? "<br>" + escapeHtml(club.chairman_address) : ""}${club.chairman_phone ? "<br>" + escapeHtml(club.chairman_phone) : ""}</td>
        <td>${escapeHtml(club.secretary_name || "-")}${club.secretary_address ? "<br>" + escapeHtml(club.secretary_address) : ""}${club.secretary_phone ? "<br>" + escapeHtml(club.secretary_phone) : ""}</td>
        <td style="text-align:center;white-space:nowrap;">${formatDate(club.last_reorg_date)}</td>
        <td style="text-align:center;white-space:nowrap;">${formatDate(club.next_reorg_due_date)}</td>
      </tr>
    `;
  });

  html += `
      </tbody>
    </table>
    <div class="print-footer">
      <div class="signatures">
        <div class="sig-block"><div class="sig-line"></div><div class="sig-label">${preparedBy}</div></div>
        <div class="sig-block"><div class="sig-line"></div><div class="sig-label">${checkedBy}</div></div>
        <div class="sig-block"><div class="sig-line"></div><div class="sig-label">${approvedBy}</div></div>
      </div>
    </div>
  `;

  printContainer.innerHTML = html;

  if (window.i18n && window.i18n.applyTranslations) {
    window.i18n.applyTranslations();
  }
}

// Note: printing is initiated via printWithDate() which loads full data first.

/* JavaScript for Print with Date */
async function printWithDate() {
  try {
    // Store original title
    const originalTitle = document.title;

    // Get current date in YYYY-MM-DD format
    const now = new Date();
    const dateStr =
      now.getFullYear() +
      "-" +
      String(now.getMonth() + 1).padStart(2, "0") +
      "-" +
      String(now.getDate()).padStart(2, "0");

    // Get filter info if available
    let filterInfo = "";
    const district = document.getElementById("filterDistrict")?.value;
    const division = document.getElementById("filterDivision")?.value;

    if (district) {
      const districtText =
        document.getElementById("filterDistrict")?.selectedOptions[0]?.text;
      filterInfo = "_" + districtText.replace(/\s+/g, "_");
    }

    if (division) {
      const divisionText =
        document.getElementById("filterDivision")?.selectedOptions[0]?.text;
      filterInfo += "_" + divisionText.replace(/\s+/g, "_");
    }

    // Update print filter info if element exists
    const printFilterEl = document.getElementById("printFilterInfo");
    if (printFilterEl) {
      printFilterEl.textContent =
        "Sports Clubs Report | " +
        dateStr +
        (filterInfo ? " | " + filterInfo.replaceAll("_", " ") : "");
    }

    // Update title (for tab / header only)
    document.title = "Sports_Clubs_Report_" + dateStr + filterInfo;

    // Ensure print table has all rows (not just current page)
    const loaded = await loadAllClubsForPrint();
    if (!loaded) {
      console.error("Failed to load clubs for print");
      alert(
        "Too many clubs to print at once. Please apply filters (by district or division) to reduce the data and try again.",
      );
      document.title = originalTitle;
      return;
    }

    // Populate the print container
    populatePrintContainer();

    // Wait a moment for content to render, then print
    setTimeout(() => {
      window.print();

      // Restore original title after print
      setTimeout(() => {
        document.title = originalTitle;
      }, 500);
    }, 300);
  } catch (error) {
    console.error("Print error:", error);
    alert("Error preparing print. Please check the console for details.");
  }
}

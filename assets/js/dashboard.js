/**
 * Load dashboard statistics
 */
function loadStatistics() {
  fetch("/sports-v2/api/statistics.php")
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
            card.className = "stat-card rounded-lg shadow-md p-6";
            card.style.borderLeftColor = color.border;

            card.innerHTML = `
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-semibold text-gray-600">${escapeHtml(
                    district.district_name,
                  )}</p>
                  <p class="text-3xl font-bold text-gray-800 mt-2">${
                    district.count
                  }</p>
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
  loadClubs();
  loadStatistics();

  // Live search with debounce
  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    // Search as user types (with 500ms debounce)
    searchInput.addEventListener(
      "input",
      debounce(function () {
        loadClubs();
      }, 500),
    );

    // Also support Enter key for immediate search
    searchInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        clearTimeout(searchTimeout); // Cancel debounce
        loadClubs();
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
  const gnDivisionSelect = document.getElementById("filterGnDivision");

  // Add change event listeners
  districtSelect.addEventListener("change", function () {
    handleDistrictChange(this.value);
  });

  divisionSelect.addEventListener("change", function () {
    handleDivisionChange(this.value);
  });

  gnDivisionSelect.addEventListener("change", function () {
    loadClubs();
  });

  // Load all districts
  fetch("/sports-v2/api/locations.php?type=district")
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
  const gnDivisionSelect = document.getElementById("filterGnDivision");

  // Clear division and GN division
  divisionSelect.innerHTML =
    '<option value="" data-i18n="placeholder.select_district_first"></option>';
  gnDivisionSelect.innerHTML =
    '<option value="" data-i18n="placeholder.select_division_first"></option>';

  // Update translations if i18n is available
  if (window.i18n) {
    window.i18n.updatePageTranslations();
  }

  if (districtId) {
    // Load divisions
    const url = `/sports-v2/api/locations.php?type=division&parent_id=${districtId}`;
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

  loadClubs();
}

/**
 * Handle division change
 */
function handleDivisionChange(divisionId) {
  console.log("handleDivisionChange called with:", divisionId);

  const gnDivisionSelect = document.getElementById("filterGnDivision");

  // Clear GN division
  gnDivisionSelect.innerHTML =
    '<option value="" data-i18n="placeholder.select_division_first"></option>';

  // Update translations if i18n is available
  if (window.i18n) {
    window.i18n.updatePageTranslations();
  }

  if (divisionId) {
    // Load GN divisions
    const url = `/sports-v2/api/locations.php?type=gn_division&parent_id=${divisionId}`;
    console.log("Fetching GN divisions from:", url);

    fetch(url)
      .then((response) => {
        console.log("GN Division response status:", response.status);
        return response.json();
      })
      .then((data) => {
        console.log("GN Division data received:", data);
        if (data.success && data.data) {
          data.data.forEach((gnDivision) => {
            const option = document.createElement("option");
            option.value = gnDivision.id;
            option.textContent = gnDivision.name;
            option.setAttribute("data-search", gnDivision.name.toLowerCase());
            gnDivisionSelect.appendChild(option);
          });
          console.log(`Added ${data.data.length} GN divisions to dropdown`);
          enableSelectSearch(gnDivisionSelect);
        }
      })
      .catch((error) => console.error("Error loading GN divisions:", error));
  }

  loadClubs();
}

/**
 * Load clubs based on filters
 */
function loadClubs() {
  const search = document.getElementById("searchInput").value;
  const districtId = document.getElementById("filterDistrict").value;
  const divisionId = document.getElementById("filterDivision").value;
  const gnDivisionId = document.getElementById("filterGnDivision").value;

  // Build query parameters
  const params = new URLSearchParams();
  if (search) params.append("search", search);
  if (districtId) params.append("district_id", districtId);
  if (divisionId) params.append("division_id", divisionId);
  if (gnDivisionId) params.append("gn_division_id", gnDivisionId);

  // Show loading state
  const tbody = document.getElementById("clubsTableBody");
  tbody.innerHTML =
    '<tr><td colspan="12" class="px-6 py-4 text-center text-gray-500"><span data-i18n="message.loading">Loading...</span></td></tr>';

  // Fetch clubs
  fetch(`/sports-v2/api/clubs-list.php?${params.toString()}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayClubs(data.data);
        updateStats(data.stats || {});
      } else {
        tbody.innerHTML =
          '<tr><td colspan="12" class="px-6 py-4 text-center text-red-500">' +
          (data.message || window.i18n.t("message.error_loading_clubs")) +
          "</td></tr>";
      }
    })
    .catch((error) => {
      console.error("Error loading clubs:", error);
      tbody.innerHTML =
        '<tr><td colspan="12" class="px-6 py-4 text-center text-red-500">' +
        window.i18n.t("message.error_loading_clubs") +
        "</td></tr>";
    });
}

/**
 * Display clubs in table
 */
function displayClubs(clubs) {
  currentClubsData = clubs; // Store for printing
  const tbody = document.getElementById("clubsTableBody");

  if (clubs.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="12" class="px-6 py-4 text-center text-gray-500"><span data-i18n="table.no_data">No data available</span></td></tr>';

    // Update translations for dynamically added content
    if (window.i18n && typeof window.i18n.updateContent === "function") {
      window.i18n.updateContent();
    }
    return;
  }

  tbody.innerHTML = "";

  clubs.forEach((club) => {
    const tr = document.createElement("tr");
    tr.className = "hover:bg-gray-50";

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
        // If reorg is Jan-June, next due is 1 year later, same month/day
        nextYear = lastYear + 1;
      }

      nextReorgDate = formatDate(
        new Date(nextYear, nextMonth - 1, nextDay).toISOString().split("T")[0],
      );
    }

    tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(club.reg_number)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${formatDate(club.registration_date)}</td>
            <td class="px-6 py-4 text-sm text-gray-900">${escapeHtml(club.name)}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml(club.division_name || "")}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml(club.gn_division_name || "")}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml(club.chairman_name || "")}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml(club.chairman_address || "")}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml(club.secretary_name || "")}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml(club.secretary_address || "")}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${club.last_reorg_date ? formatDate(club.last_reorg_date) : "-"}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${nextReorgDate || "-"}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex items-center gap-3">
                    <a href="club-details.php?id=${club.id}" 
                       class="text-blue-600 hover:text-blue-800 transition" 
                       title="View Details">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
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
                </div>
            </td>
        `;
    tbody.appendChild(tr);
  });

  // Update translations for dynamically added content
  if (window.i18n && typeof window.i18n.updateContent === "function") {
    window.i18n.updateContent();
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
  const gnDivisionSelect = document.getElementById("filterGnDivision");

  districtSelect.value = "";
  divisionSelect.innerHTML =
    '<option value="" data-i18n="placeholder.select_district_first"></option>';
  gnDivisionSelect.innerHTML =
    '<option value="" data-i18n="placeholder.select_division_first"></option>';

  // Update translations if i18n is available
  if (window.i18n) {
    window.i18n.updatePageTranslations();
  }

  loadClubs();
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
  fetch(`/sports-v2/api/clubs.php?id=${clubId}`, {
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
  const gnDivisionId = document.getElementById("filterGnDivision").value;
  const language = localStorage.getItem("language") || "si";

  // Build query parameters
  const params = new URLSearchParams();
  if (search) params.append("search", search);
  if (districtId) params.append("district_id", districtId);
  if (divisionId) params.append("division_id", divisionId);
  if (gnDivisionId) params.append("gn_division_id", gnDivisionId);
  params.append("language", language);

  // Show loading state
  const btn = event.target.closest("button");
  const originalText = btn.textContent;
  btn.disabled = true;
  btn.textContent = "Exporting...";

  try {
    // Create download link and trigger download
    const downloadUrl = `/sports-v2/api/export-clubs-excel.php?${params.toString()}`;
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

/**
 * Populate print container with current data
 */
function populatePrintContainer() {
  const printTableBody = document.getElementById("printTableBody");
  const printFilterInfo = document.getElementById("printFilterInfo");

  if (!printTableBody) return;

  // Clear existing content
  printTableBody.innerHTML = "";

  // Get filter info
  const search = document.getElementById("searchInput").value;
  const districtSelect = document.getElementById("filterDistrict");
  const divisionSelect = document.getElementById("filterDivision");
  const gnDivisionSelect = document.getElementById("filterGnDivision");

  let filterText = window.i18n
    ? window.i18n.t("filter.all_clubs")
    : "All Clubs";

  if (gnDivisionSelect.value) {
    filterText =
      gnDivisionSelect.options[gnDivisionSelect.selectedIndex].text;
  } else if (divisionSelect.value) {
    filterText = divisionSelect.options[divisionSelect.selectedIndex].text;
  } else if (districtSelect.value) {
    filterText = districtSelect.options[districtSelect.selectedIndex].text;
  }

  if (search) {
    filterText += ` - ${window.i18n ? window.i18n.t("placeholder.search") : "Search"}: ${search}`;
  }

  printFilterInfo.textContent = filterText;

  // Populate table with current clubs data
  if (currentClubsData.length === 0) {
    printTableBody.innerHTML =
      '<tr><td colspan="12" style="text-align: center; padding: 20px;">No data available</td></tr>';
    return;
  }

  currentClubsData.forEach((club, index) => {
    const tr = document.createElement("tr");

    const statusText =
      club.reorg_status === "active"
        ? window.i18n
          ? window.i18n.t("status.active")
          : "Active"
        : window.i18n
          ? window.i18n.t("status.expired")
          : "Expired";

    tr.innerHTML = `
      <td style="text-align: center;">${index + 1}</td>
      <td>${escapeHtml(club.reg_number || "-")}</td>
      <td style="text-align: center;">${formatDate(club.registration_date)}</td>
      <td>${escapeHtml(club.name)}</td>
      <td>${escapeHtml(club.district_name || "-")}</td>
      <td>${escapeHtml(club.division_name || "-")}</td>
      <td>${escapeHtml(club.gn_division_name || "-")}</td>
      <td>${escapeHtml(club.chairman_name || "-")}</td>
      <td>${escapeHtml(club.chairman_address || "-")}</td>
      <td>${escapeHtml(club.secretary_name || "-")}</td>
      <td>${escapeHtml(club.secretary_address || "-")}</td>
      <td style="text-align: center;">${formatDate(club.last_reorg_date)}</td>
      <td style="text-align: center;">${formatDate(club.next_reorg_due_date)}</td>
    `;

    printTableBody.appendChild(tr);
  });

  // Apply translations to print container
  if (window.i18n && window.i18n.applyTranslations) {
    window.i18n.applyTranslations();
  }
}

// Listen for print event to populate print container
window.addEventListener("beforeprint", function () {
  populatePrintContainer();
});

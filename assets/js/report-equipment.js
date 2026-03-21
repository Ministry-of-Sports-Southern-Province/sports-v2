document.addEventListener("DOMContentLoaded", function () {
  loadDistricts();
  loadEquipmentTypes();
  loadReportYears();

  // Real-time report generation (reset to page 1 when filters change)
  document.getElementById("equipment").addEventListener("change", function () {
    generateReport(1);
  });
  document.getElementById("district").addEventListener("change", function () {
    loadDivisions();
    generateReport(1);
  });
  document.getElementById("division")?.addEventListener("change", function () {
    loadGSDivisions();
    generateReport(1);
  });
  document
    .getElementById("gsDivision")
    ?.addEventListener("change", function () {
      generateReport(1);
    });
  document.getElementById("year")?.addEventListener("change", function () {
    generateReport(1);
  });
});

let currentPage = 1;
let rowsPerPage = 10;
let totalPages = 1;
let totalRows = 0;
let currentViewMode = "aggregated"; // "aggregated" or "yearwise"

function buildReportQuery({
  page = 1,
  limit = rowsPerPage,
  printAll = false,
} = {}) {
  const equipment = document.getElementById("equipment").value;
  const district = document.getElementById("district").value;
  const division = document.getElementById("division")?.value || "";
  const gsDivision = document.getElementById("gsDivision")?.value || "";
  const year = document.getElementById("year")?.value || "";

  const params = new URLSearchParams();
  params.append("type", "equipment");
  params.append("equipment", equipment);
  params.append("district", district);
  params.append("view_mode", currentViewMode);
  if (division) params.append("division", division);
  if (gsDivision) params.append("gs_division", gsDivision);
  if (year) params.append("year", year);

  if (printAll) {
    params.append("print_all", "1");
  } else {
    params.append("page", String(page));
    params.append("limit", String(limit));
  }

  return `../api/reports.php?${params.toString()}`;
}

function loadDistricts() {
  fetch("../api/locations.php?type=district")
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        const select = document.getElementById("district");
        data.data.forEach((d) => {
          const opt = document.createElement("option");
          opt.value = d.name;
          opt.textContent = d.name;
          select.appendChild(opt);
        });
      }
    });
}

function loadDivisions() {
  const select = document.getElementById("division");

  // Clear existing options except "All"
  while (select.children.length > 1) {
    select.removeChild(select.lastChild);
  }

  // Store division data for later use in GN division loading
  window.divisionsData = {};

  fetch("../api/locations.php?type=division&search=")
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        data.data.forEach((d) => {
          const opt = document.createElement("option");
          opt.value = d.name;
          opt.textContent = d.name;
          opt.dataset.id = d.id;
          select.appendChild(opt);
          window.divisionsData[d.name] = d.id;
        });
      }
    })
    .catch((err) => console.error("Error loading divisions:", err));
}

function loadGSDivisions() {
  const select = document.getElementById("gsDivision");
  const divisionSelect = document.getElementById("division");
  const selectedDivision = divisionSelect.value;

  // Clear existing options except "All"
  while (select.children.length > 1) {
    select.removeChild(select.lastChild);
  }

  if (!selectedDivision) return;

  // Get the division ID from stored data
  const divisionId = window.divisionsData?.[selectedDivision];
  if (!divisionId) return;

  fetch(`../api/locations.php?type=gs_division&parent_id=${divisionId}&search=`)
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        data.data.forEach((g) => {
          const opt = document.createElement("option");
          opt.value = g.name;
          opt.textContent = g.name;
          select.appendChild(opt);
        });
      }
    })
    .catch((err) => console.error("Error loading GN divisions:", err));
}

function loadEquipmentTypes() {
  fetch("../api/equipment-types.php")
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        const select = document.getElementById("equipment");
        data.data.forEach((e) => {
          const opt = document.createElement("option");
          opt.value = e.name;
          opt.textContent = e.name;
          select.appendChild(opt);
        });
        generateReport();
      }
    });
}

/**
 * Load available years from equipment records for filtering
 */
function loadReportYears() {
  fetch("../api/reports.php?type=equipment&get_years=1")
    .then((res) => res.json())
    .then((data) => {
      if (data.success && data.years) {
        const select = document.getElementById("year");
        // Sort years in descending order
        const years = data.years.sort((a, b) => parseInt(b) - parseInt(a));
        years.forEach((year) => {
          const opt = document.createElement("option");
          opt.value = year;
          opt.textContent = year;
          select.appendChild(opt);
        });
      }
    })
    .catch((err) => console.error("Error loading years:", err));
}

/**
 * Set view mode for equipment report (aggregated or yearwise)
 */
function setViewMode(mode) {
  currentViewMode = mode;

  // Update button styling
  document.getElementById("viewModeAggregated").classList.remove("active-mode");
  document.getElementById("viewModeAggregated").style.backgroundColor =
    "#d1d5db";
  document.getElementById("viewModeAggregated").style.color = "#374151";

  document.getElementById("viewModeYearwise").classList.remove("active-mode");
  document.getElementById("viewModeYearwise").style.backgroundColor = "#d1d5db";
  document.getElementById("viewModeYearwise").style.color = "#374151";

  if (mode === "aggregated") {
    document.getElementById("viewModeAggregated").classList.add("active-mode");
    document.getElementById("viewModeAggregated").style.backgroundColor =
      "#2563eb";
    document.getElementById("viewModeAggregated").style.color = "white";
  } else {
    document.getElementById("viewModeYearwise").classList.add("active-mode");
    document.getElementById("viewModeYearwise").style.backgroundColor =
      "#2563eb";
    document.getElementById("viewModeYearwise").style.color = "white";
  }

  // Regenerate report with new view mode
  generateReport(1);
}

function generateReport() {
  generateReportPage(1);
}

function generateReportPage(page = 1) {
  currentPage = page;
  const equipment = document.getElementById("equipment").value;
  const district = document.getElementById("district").value;
  const division = document.getElementById("division")?.value || "";
  const gsDivision = document.getElementById("gsDivision")?.value || "";

  fetch(buildReportQuery({ page: currentPage, limit: rowsPerPage }))
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        displayReport(data.data, equipment, district, division, gsDivision);
        renderPagination(data.pagination);
      }
    });
}

// Print function with date
function printReportWithDate() {
  const originalTitle = document.title;
  const now = new Date();
  const dateStr =
    now.getFullYear() +
    "-" +
    String(now.getMonth() + 1).padStart(2, "0") +
    "-" +
    String(now.getDate()).padStart(2, "0");

  const equipment = document.getElementById("equipment")?.value;
  const district = document.getElementById("district")?.value;
  const division = document.getElementById("division")?.value;

  let filterInfo = "";
  if (equipment) {
    filterInfo = "_" + equipment.replace(/\s+/g, "_");
  }
  if (district) {
    filterInfo += "_" + district.replace(/\s+/g, "_");
  }
  if (division) {
    filterInfo += "_" + division.replace(/\s+/g, "_");
  }

  document.title = "Equipment_Report_" + dateStr + filterInfo;

  const equipmentVal = document.getElementById("equipment")?.value;
  const districtVal = document.getElementById("district")?.value;
  const divisionVal = document.getElementById("division")?.value || "";
  const gsDivisionVal = document.getElementById("gsDivision")?.value || "";

  fetch(buildReportQuery({ printAll: true }))
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        displayReport(
          data.data,
          equipmentVal,
          districtVal,
          divisionVal,
          gsDivisionVal,
        );
        window.print();
        setTimeout(() => {
          document.title = originalTitle;
          generateReportPage(currentPage);
        }, 750);
      } else {
        window.print();
        setTimeout(() => {
          document.title = originalTitle;
        }, 750);
      }
    })
    .catch(() => {
      window.print();
      setTimeout(() => {
        document.title = originalTitle;
      }, 750);
    });
}

function renderPagination(pagination) {
  const container = document.getElementById("reportPagination");
  const infoEl = document.getElementById("reportPaginationInfo");
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
  prevBtn.onclick = () => generateReportPage(currentPage - 1);
  container.appendChild(prevBtn);

  const windowSize = 3;
  const start = Math.max(1, currentPage - windowSize);
  const end = Math.min(totalPages, currentPage + windowSize);

  if (start > 1) {
    const firstBtn = document.createElement("button");
    firstBtn.textContent = "1";
    firstBtn.className = "px-3 py-1 border rounded bg-white hover:bg-gray-50";
    firstBtn.onclick = () => generateReportPage(1);
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
    btn.textContent = String(i);
    btn.className =
      "px-3 py-1 border rounded " +
      (i === currentPage
        ? "bg-blue-600 text-white"
        : "bg-white hover:bg-gray-50");
    btn.onclick = () => generateReportPage(i);
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
    lastBtn.onclick = () => generateReportPage(totalPages);
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
  nextBtn.onclick = () => generateReportPage(currentPage + 1);
  container.appendChild(nextBtn);

  if (infoEl) {
    const startRow = totalRows === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
    const endRow = Math.min(currentPage * rowsPerPage, totalRows);
    infoEl.textContent = `Showing ${startRow}–${endRow} of ${totalRows} (Page ${currentPage} of ${totalPages})`;
  }
}

function displayReport(
  data,
  equipment,
  district,
  division = "",
  gsDivision = "",
) {
  const output = document.getElementById("reportOutput");
  const equipmentText = equipment || "All Equipment";
  const districtText = district || "All Districts";
  const divisionText = division ? ` | Division: ${division}` : "";
  const gsDivisionText = gsDivision ? ` | GS Division: ${gsDivision}` : "";

  // Calculate total
  const totalQuantity = data.reduce(
    (sum, row) => sum + parseInt(row.quantity || 0),
    0,
  );

  // Sort data by district, division, gs_division for proper grouping
  const sortedData = [...data].sort((a, b) => {
    if (a.district !== b.district)
      return (a.district || "").localeCompare(b.district || "");
    if (a.division !== b.division)
      return (a.division || "").localeCompare(b.division || "");
    if (a.gs_division !== b.gs_division)
      return (a.gs_division || "").localeCompare(b.gs_division || "");
    if (currentViewMode === "yearwise" && a.year !== b.year) {
      return parseInt(b.year || 0) - parseInt(a.year || 0); // Newer years first
    }
    return 0;
  });

  // Assign colors to groups - cycling through color palette
  const colors = [
    "#dbeafe", // light blue
    "#fef3c7", // light yellow
    "#d1fae5", // light green
    "#fce7f3", // light pink
    "#e0e7ff", // light indigo
    "#fed7aa", // light orange
    "#f3e8ff", // light purple
    "#fecaca", // light red
  ];

  // Build table header based on view mode
  let tableHeader = `
    <tr>
      <th>#</th>
      <th>Registration No</th>
      <th>Club Name</th>
      <th>District</th>
      <th>Division</th>
      <th>GS Division</th>
      <th>Equipment</th>
      ${currentViewMode === "yearwise" ? "<th>Year</th>" : ""}
      <th class="text-right">Quantity</th>
    </tr>
  `;

  let tableRows = "";
  let currentGsDivisionKey = "";
  let colorIndex = 0;
  let currentColor = "";

  sortedData.forEach((row, i) => {
    const gsDivisionKey = `${row.district}|${row.division}|${row.gs_division}`;

    // Change color when GN Division changes
    if (gsDivisionKey !== currentGsDivisionKey) {
      currentGsDivisionKey = gsDivisionKey;
      currentColor = colors[colorIndex % colors.length];
      colorIndex++;
    }

    tableRows += `
      <tr style="background-color: ${currentColor} !important;">
        <td class="font-medium text-slate-900">${i + 1}</td>
        <td class="text-slate-800">${row.reg_number}</td>
        <td class="text-slate-800 font-medium">${row.name}</td>
        <td class="text-slate-800">${row.district || "-"}</td>
        <td class="text-slate-800">${row.division || "-"}</td>
        <td class="text-slate-800">${row.gs_division || "-"}</td>
        <td class="text-slate-800">${row.equipment}</td>
        ${currentViewMode === "yearwise" ? `<td class="text-slate-800 font-medium">${row.year || "-"}</td>` : ""}
        <td class="text-right font-medium text-slate-900">${row.quantity}</td>
      </tr>`;
  });

  output.innerHTML = `
        <style>
            /* Print Styles - Compact with Same-Color Grouping */
            @media print {
                @page { 
                    size: A4 landscape; 
                    margin: 5mm; 
                }
                
                body {
                    margin: 0;
                    padding: 0;
                    background: white;
                }
                
                .no-print {
                    display: none !important;
                }
                
                .print-header,
                .print-footer {
                    display: block !important;
                }
                
                /* Header Compact */
                .print-header { 
                    text-align: center; 
                    margin-bottom: 12px; 
                    border-bottom: 2px solid #1e3a8a; 
                    padding-bottom: 6px; 
                }
                .print-header .dept-name { 
                    font-size: 9pt;
                    font-weight: bold; 
                    color: #4b5563; 
                    text-transform: uppercase; 
                    margin-bottom: 3px; 
                }
                .print-header h1 { 
                    font-size: 16pt;
                    font-weight: 900; 
                    color: #1e3a8a; 
                    margin: 3px 0; 
                    line-height: 1;
                }
                .print-header .text-sm {
                    font-size: 9pt;
                    margin-top: 5px;
                    color: #000;
                }
                
                /* Table Compact Styling - text wraps to avoid column overflow */
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    font-size: 7pt; 
                    margin-top: 8px; 
                    line-height: 1.2;
                    table-layout: fixed;
                }
                thead { display: table-header-group; }
                tfoot { display: table-footer-group; }
                table th { 
                    background-color: #1e3a8a !important; 
                    color: white !important; 
                    font-weight: bold; 
                    font-size: 7pt;
                    padding: 3px 4px; 
                    border: 1px solid #ccc; 
                    text-align: left; 
                    line-height: 1.1;
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                    word-break: break-word;
                    -webkit-print-color-adjust: exact; 
                    print-color-adjust: exact;
                }
                table td { 
                    padding: 3px 4px; 
                    border: 1px solid #ccc; 
                    font-size: 7pt; 
                    color: #333; 
                    line-height: 1.2; 
                    vertical-align: top;
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                    word-break: break-word;
                    min-width: 0;
                }
                
                /* Ensure inline background colors print */
                table tbody tr {
                    -webkit-print-color-adjust: exact; 
                    print-color-adjust: exact;
                }
                
                /* Footer - Properly Aligned Left and Right */
                .print-footer { 
                    margin-top: 15px; 
                    page-break-inside: avoid;
                }
                .signatures { 
                    display: flex; 
                    justify-content: space-between;
                    align-items: flex-end;
                    margin-top: 20px; 
                    padding: 0 50px;
                }
                .sig-block { 
                    width: 180px; 
                    text-align: center; 
                }
                .sig-line { 
                    border-bottom: 1px dotted #000; 
                    margin-bottom: 4px; 
                    height: 15px; 
                }
                .sig-label { 
                    font-size: 8pt; 
                    font-weight: bold; 
                    text-transform: uppercase; 
                    color: black; 
                }
                
                /* Summary section */
                .mt-6 {
                    margin-top: 10px;
                    font-size: 8pt;
                    font-weight: bold;
                }
            }
        </style>
        
        <div class="print-header" style="display: none;">
            <div class="dept-name" data-i18n="header.department_name">Department of Sports Southern Province</div>
            <h1 data-i18n="report.type_equipment">Equipment Report</h1>
            <div class="text-sm">Equipment: ${equipmentText} | District: ${districtText}${divisionText}${gsDivisionText}</div>
        </div>

        <div class="text-center mb-6 no-print">
            <h2 class="text-2xl font-bold">Department of Sports Southern Province</h2>
            <h3 class="text-xl mt-2">Equipment Report</h3>
            <p class="text-sm text-gray-600 mt-2">Equipment: ${equipmentText} | District: ${districtText}${divisionText}${gsDivisionText}</p>
            <p class="text-sm text-gray-600">Generated: ${new Date().toLocaleDateString("en-US")}</p>
        </div>
        
        <table class="report-table min-w-full border-collapse">
            <thead>
                ${tableHeader}
            </thead>
            <tbody>
                ${tableRows}
            </tbody>
        </table>
        
        <div class="mt-6 text-sm text-gray-600">
            <p>Total Records: ${data.length} | Total Quantity: ${totalQuantity}</p>
        </div>

        <div class="print-footer" style="display: none;">
            <div class="signatures">
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-label" data-i18n="footer.prepared_by">Prepared By</div>
                </div>
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-label" data-i18n="footer.approved_by">Approved By</div>
                </div>
            </div>
        </div>
    `;

  if (window.i18n && window.i18n.applyTranslations) {
    window.i18n.applyTranslations();
  }
}

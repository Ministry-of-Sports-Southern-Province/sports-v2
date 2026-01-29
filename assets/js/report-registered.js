document.addEventListener("DOMContentLoaded", function () {
  loadDistricts();

  // Real-time report generation
  document
    .getElementById("district")
    .addEventListener("change", () => generateReport(1));
  document
    .getElementById("dateRange")
    .addEventListener("change", () => generateReport(1));

  // Generate initial report
  generateReport(1);
});

let currentPage = 1;
let rowsPerPage = 10;
let totalPages = 1;
let totalRows = 0;

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

function buildReportQuery({
  page = 1,
  limit = rowsPerPage,
  printAll = false,
} = {}) {
  const district = document.getElementById("district").value;
  const dateRange = document.getElementById("dateRange").value;

  const params = new URLSearchParams();
  params.append("type", "registered");
  params.append("district", district);
  params.append("date_range", dateRange);
  if (printAll) {
    params.append("print_all", "1");
  } else {
    params.append("page", String(page));
    params.append("limit", String(limit));
  }
  return `../api/reports.php?${params.toString()}`;
}

function generateReport(page = 1) {
  currentPage = page;
  const district = document.getElementById("district").value;
  const dateRange = document.getElementById("dateRange").value;

  fetch(buildReportQuery({ page: currentPage, limit: rowsPerPage }))
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        displayReport(data.data, district, dateRange);
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

  const district = document.getElementById("district")?.value;
  const dateRange = document.getElementById("dateRange")?.value;

  let filterInfo = "";
  if (district) {
    filterInfo = "_" + district.replace(/\s+/g, "_");
  }
  if (dateRange) {
    const rangeText =
      dateRange === "year" ? "Year" : dateRange === "month" ? "Month" : "All";
    filterInfo += "_" + rangeText;
  }

  document.title = "Registered_Clubs_Report_" + dateStr + filterInfo;

  // Load full dataset for print, then print, then restore paginated view
  const districtVal = document.getElementById("district").value;
  const dateRangeVal = document.getElementById("dateRange").value;
  fetch(buildReportQuery({ printAll: true }))
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        displayReport(data.data, districtVal, dateRangeVal);
        window.print();
        // Restore current page view after print
        setTimeout(() => {
          document.title = originalTitle;
          generateReport(currentPage);
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
  prevBtn.onclick = () => generateReport(currentPage - 1);
  container.appendChild(prevBtn);

  const windowSize = 3;
  const start = Math.max(1, currentPage - windowSize);
  const end = Math.min(totalPages, currentPage + windowSize);

  if (start > 1) {
    const firstBtn = document.createElement("button");
    firstBtn.textContent = "1";
    firstBtn.className = "px-3 py-1 border rounded bg-white hover:bg-gray-50";
    firstBtn.onclick = () => generateReport(1);
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
    btn.onclick = () => generateReport(i);
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
    lastBtn.onclick = () => generateReport(totalPages);
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
  nextBtn.onclick = () => generateReport(currentPage + 1);
  container.appendChild(nextBtn);

  if (infoEl) {
    const startRow = totalRows === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
    const endRow = Math.min(currentPage * rowsPerPage, totalRows);
    infoEl.textContent = `Showing ${startRow}–${endRow} of ${totalRows} (Page ${currentPage} of ${totalPages})`;
  }
}

function displayReport(data, district, dateRange) {
  const output = document.getElementById("reportOutput");
  const districtText = district || "සියලු දිස්ත්රික්ක";
  const rangeText =
    dateRange === "year"
      ? "මෙම වර්ෂය"
      : dateRange === "month"
        ? "මෙම මාසය"
        : "සියලු කාලය";

  output.innerHTML = `
        <style>
            /* Print Styles for Compact Table */
            @media print {
                @page { 
                    size: A4 portrait; 
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
                table tbody tr:nth-child(even) { 
                    background-color: #f9fafb !important; 
                    -webkit-print-color-adjust: exact; 
                    print-color-adjust: exact;
                }
                
                /* Footer Compact */
                .print-footer { 
                    margin-top: 15px; 
                    page-break-inside: avoid;
                }
                .signatures { 
                    display: flex; 
                    justify-content: space-around; 
                    margin-top: 20px; 
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
            <h1 data-i18n="report.type_registered">ලියාපදිංචි සමාජ වාර්තාව</h1>
            <div class="text-sm">දිස්ත්රික්කය: ${districtText} | කාල පරාසය: ${rangeText}</div>
        </div>

        <div class="text-center mb-6 no-print">
            <h2 class="text-2xl font-bold">දකුණු පළාත් ක්‍රීඩා දෙපාර්තමේන්තුව</h2>
            <h3 class="text-xl mt-2">ලියාපදිංචි සමාජ වාර්තාව</h3>
            <p class="text-sm text-gray-600 mt-2">දිස්ත්රික්කය: ${districtText} | කාල පරාසය: ${rangeText}</p>
            <p class="text-sm text-gray-600">උත්පාදන දිනය: ${new Date().toLocaleDateString("si-LK")}</p>
        </div>
        <table class="report-table min-w-full border-collapse">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ලියාපදිංචි අංකය</th>
                    <th>සමාජ නාමය</th>
                    <th>දිස්ත්රික්කය</th>
                    <th>සභාපති</th>
                    <th>දුරකථනය</th>
                    <th>ලියාපදිංචි දිනය</th>
                </tr>
            </thead>
            <tbody>
                ${data
                  .map(
                    (row, i) => `
                    <tr>
                        <td class="font-medium text-slate-900">${i + 1}</td>
                        <td class="text-slate-800">${row.reg_number}</td>
                        <td class="text-slate-800 font-medium">${row.name}</td>
                        <td class="text-slate-800">${row.district || "-"}</td>
                        <td class="text-slate-800">${row.chairman}</td>
                        <td class="text-slate-800">${row.chairman_phone}</td>
                        <td class="text-slate-800">${row.registration_date}</td>
                    </tr>
                `,
                  )
                  .join("")}
            </tbody>
        </table>
        <div class="mt-6 text-sm text-gray-600">
            <p>මුළු වාර්තා ගණන: ${data.length}</p>
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

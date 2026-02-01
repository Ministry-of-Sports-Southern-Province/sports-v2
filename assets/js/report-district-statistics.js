document.addEventListener("DOMContentLoaded", function () {
  const output = document.getElementById("reportOutput");
  output.innerHTML =
    '<div class="text-center py-8 text-gray-600">පූරණය වෙමින්...</div>';

  Promise.all([loadDistricts(), loadYears()])
    .then(() => {
      // After both districts and years are loaded, generate initial report
      console.log("Districts and years loaded, generating initial report");
      setTimeout(() => generateReport(1), 300);
    })
    .catch((err) => {
      console.error("Error loading data:", err);
      output.innerHTML =
        '<div class="text-center py-8 text-red-600"><p>ප්‍රారම්භක දත්ත පූරණයේ දෝෂයක්</p></div>';
    });

  // Real-time report generation
  const districtSelect = document.getElementById("district");
  const yearSelect = document.getElementById("year");

  if (districtSelect) {
    districtSelect.addEventListener("change", () => generateReport(1));
  }
  if (yearSelect) {
    yearSelect.addEventListener("change", () => generateReport(1));
  }
});

let currentPage = 1;
let rowsPerPage = 10;
let totalPages = 1;
let totalRows = 0;

function loadDistricts() {
  return fetch("../api/locations.php?type=district")
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
      return data;
    })
    .catch((err) => {
      console.error("Error loading districts:", err);
      return null;
    });
}

function loadYears() {
  return fetch("../api/reports.php?type=district_statistics&action=get_years")
    .then((res) => res.json())
    .then((data) => {
      if (data.success && data.data) {
        const select = document.getElementById("year");
        const currentYear = new Date().getFullYear();
        let hasCurrentYear = false;

        data.data.forEach((year) => {
          const opt = document.createElement("option");
          opt.value = year;
          opt.textContent = year;
          if (year == currentYear) {
            opt.selected = true;
            hasCurrentYear = true;
          }
          select.appendChild(opt);
        });

        // If no current year found, select first available
        if (!hasCurrentYear && data.data.length > 0) {
          select.value = data.data[0];
        }
      }
      return data;
    })
    .catch((err) => {
      console.error("Error loading years:", err);
      return null;
    });
}

function buildReportQuery({
  page = 1,
  limit = rowsPerPage,
  printAll = false,
} = {}) {
  const district = document.getElementById("district").value;
  const year = document.getElementById("year").value;

  const params = new URLSearchParams();
  params.append("type", "district_statistics");
  if (district) {
    params.append("district", district);
  }
  if (year) {
    params.append("year", year);
  }
  if (printAll) {
    params.append("print_all", "1");
  } else {
    params.append("page", String(page));
    params.append("limit", String(limit));
  }

  const query = `../api/reports.php?${params.toString()}`;
  console.log("Report Query:", query);
  return query;
}

function generateReport(page = 1) {
  currentPage = page;
  const district = document.getElementById("district").value;
  const year = document.getElementById("year").value;
  const output = document.getElementById("reportOutput");

  // Show loading state
  output.innerHTML =
    '<div class="text-center py-8 text-gray-600">පූරණය වෙමින්...</div>';

  fetch(buildReportQuery({ page: currentPage, limit: rowsPerPage }))
    .then((res) => res.json())
    .then((data) => {
      if (data.success && data.data) {
        displayReport(data.data, district, year);
        renderPagination(data.pagination);
      } else {
        output.innerHTML = `<div class="text-center py-8 text-gray-500">
          <p>දත්ත නොමැත</p>
        </div>`;
      }
    })
    .catch((err) => {
      console.error("Error generating report:", err);
      output.innerHTML = `<div class="text-center py-8 text-red-600">
        <p>වාර්තාව පූරණය කිරීමට දෙවැරක් ඇති විය</p>
      </div>`;
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
  const year = document.getElementById("year")?.value;

  let filterInfo = "";
  if (district) {
    filterInfo = "_" + district.replace(/\s+/g, "_");
  }
  if (year) {
    filterInfo += "_" + year;
  }

  document.title = "District_Statistics_Report_" + dateStr + filterInfo;

  // Load full dataset for print, then print, then restore paginated view
  const districtVal = document.getElementById("district").value;
  const yearVal = document.getElementById("year").value;
  fetch(buildReportQuery({ printAll: true }))
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        displayReport(data.data, districtVal, yearVal);
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

function displayReport(data, district, year) {
  const output = document.getElementById("reportOutput");

  if (!data || data.length === 0) {
    output.innerHTML = `<div class="text-center py-12 text-gray-500">
      <p class="text-lg">❌ තෙරුවන් දත්ත නොමැත</p>
      <p class="text-sm mt-2">කරුණාකර වෙනත් ෆිල්ටර් තෝරා උත්සාහ කරන්න</p>
    </div>`;
    return;
  }

  const districtText = district || "සියලු දිස්ත්රික්ක";
  const yearText = year || new Date().getFullYear().toString();
  const reportDate = new Date();
  const formattedDate = reportDate.toLocaleDateString("si-LK", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
  });

  let tableHTML = `
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-blue-900">
                    <th class="border border-gray-300 px-4 py-2 text-white text-left">#</th>
                    <th class="border border-gray-300 px-4 py-2 text-white text-left">කොට්ඨාසය</th>
                    <th class="border border-gray-300 px-4 py-2 text-white text-center">මුළු ලියාපදිංචි සමාජ</th>
                    <th class="border border-gray-300 px-4 py-2 text-white text-center">${yearText} ලියාපදිංචි</th>
                    <th class="border border-gray-300 px-4 py-2 text-white text-center">${yearText} ප්‍රතිසංවිධාන</th>
                </tr>
            </thead>
            <tbody>
  `;

  let totalClubs = 0;
  let totalYearRegistered = 0;
  let totalYearReorganized = 0;

  data.forEach((row, index) => {
    const bgClass = index % 2 === 0 ? "bg-white" : "bg-gray-50";
    tableHTML += `
                <tr class="${bgClass}">
                    <td class="border border-gray-300 px-4 py-2 text-center font-medium">${index + 1}</td>
                    <td class="border border-gray-300 px-4 py-2 font-medium">${row.division_name || "-"}</td>
                    <td class="border border-gray-300 px-4 py-2 text-center">${row.total_clubs || 0}</td>
                    <td class="border border-gray-300 px-4 py-2 text-center">${row.year_registered || 0}</td>
                    <td class="border border-gray-300 px-4 py-2 text-center">${row.year_reorganized || 0}</td>
                </tr>
    `;
    totalClubs += row.total_clubs || 0;
    totalYearRegistered += row.year_registered || 0;
    totalYearReorganized += row.year_reorganized || 0;
  });

  tableHTML += `
                <tr class="bg-blue-100 font-bold">
                    <td class="border border-gray-300 px-4 py-2"></td>
                    <td class="border border-gray-300 px-4 py-2">එකතුව</td>
                    <td class="border border-gray-300 px-4 py-2 text-center">${totalClubs}</td>
                    <td class="border border-gray-300 px-4 py-2 text-center">${totalYearRegistered}</td>
                    <td class="border border-gray-300 px-4 py-2 text-center">${totalYearReorganized}</td>
                </tr>
            </tbody>
        </table>
  `;

  output.innerHTML = `
        <div class="print-section">
            <!-- Screen View Header -->
            <div class="text-center mb-6 no-print">
                <h2 class="text-2xl font-bold text-blue-900">දකුණු පළාත් ක්‍රීඩා දෙපාර්තමේන්තුව</h2>
                <h3 class="text-xl mt-2 font-semibold text-blue-800">දිස්ත්රික් ක්‍රීඩා සමාජ සංඛ්‍යා වාර්තාව</h3>
                <div class="border-b-2 border-blue-900 my-3"></div>
                <p class="text-sm text-gray-700 mt-2"><strong>දිස්ත්රික්කය:</strong> ${districtText}</p>
                <p class="text-sm text-gray-700"><strong>වර්ෂය:</strong> ${yearText}</p>
                <p class="text-xs text-gray-500 mt-2">උත්පාදන දිනය: ${formattedDate}</p>
            </div>

            <!-- Print Header (Hidden by default) -->
            <div class="print-header hidden" style="display: none;">
                <div class="text-center border-b-2 border-blue-900 pb-3 mb-3">
                    <div class="text-xs font-bold text-gray-700 uppercase">දකුණු පළාත් ක්‍රීඩා දෙපාර්තමේන්තුව</div>
                    <h1 class="text-lg font-black text-blue-900 my-1">දිස්ත්රික් ක්‍රීඩා සමාජ සංඛ්‍යා වාර්තාව</h1>
                    <div class="text-xs text-gray-700">දිස්ත්රික්කය: ${districtText} | වර්ෂය: ${yearText}</div>
                    <div class="text-xs text-gray-600">උත්පාදන දිනය: ${formattedDate}</div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="table-section overflow-x-auto">
                ${tableHTML}
            </div>

            <!-- Summary Statistics -->
            <div class="mt-6 p-4 bg-gray-50 rounded no-print">
                <h4 class="font-bold text-blue-900 mb-2">සාරාංශය</h4>
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">මුළු දිස්ත්‍රික්කයන්:</span>
                        <div class="text-2xl font-bold text-blue-900">${Math.max(1, data.length)}</div>
                    </div>
                    <div>
                        <span class="text-gray-600">සර්ව ලියාපදිංචි:</span>
                        <div class="text-2xl font-bold text-green-700">${totalClubs}</div>
                    </div>
                    <div>
                        <span class="text-gray-600">${yearText} ගිණුම්:</span>
                        <div class="text-2xl font-bold text-purple-700">${totalYearRegistered + totalYearReorganized}</div>
                    </div>
                </div>
            </div>

            <!-- Print Footer (Hidden by default) -->
            <div class="print-footer hidden" style="display: none;">
                <div class="signatures">
                    <div class="sig-block">
                        <div class="sig-line"></div>
                        <div class="sig-label" data-i18n="footer.prepared_by">සකස් කළේ</div>
                    </div>
                    <div class="sig-block">
                        <div class="sig-line"></div>
                        <div class="sig-label" data-i18n="footer.approved_by">අනුමත කළේ</div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            @media print {
                @page {
                    size: A4 portrait;
                    margin: 10mm;
                }

                body {
                    margin: 0;
                    padding: 0;
                    background: white;
                    font-size: 9pt;
                }

                .no-print {
                    display: none !important;
                }

                .print-header,
                .print-footer {
                    display: block !important;
                }

                .hidden {
                    display: block !important;
                }

                .print-section {
                    box-sizing: border-box;
                    position: relative;
                }

                .print-header {
                    margin-bottom: 12px;
                }

                .print-header .text-xs {
                    font-size: 8pt;
                }

                .print-header h1 {
                    font-size: 14pt;
                    margin: 2px 0;
                }

                .table-section {
                    overflow: visible !important;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 8pt;
                    margin-top: 5px;
                }

                table th {
                    background-color: #1e3a8a !important;
                    color: white !important;
                    padding: 5px 3px !important;
                    border: 1px solid #000 !important;
                    font-weight: bold;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                table td {
                    padding: 4px 3px !important;
                    border: 1px solid #ccc !important;
                    color: #000 !important;
                }

                table tbody tr:nth-child(even) {
                    background-color: #f9fafb !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                table tbody tr:last-child {
                    background-color: #dbeafe !important;
                    font-weight: bold;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .print-footer {
                    margin-top: 20px;
                    page-break-inside: avoid;
                }

                .print-footer .text-xs {
                    font-size: 8pt;
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
            }

            /* Screen View Styles */
            table {
                border-collapse: collapse;
            }

            table th {
                background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
                color: white;
                font-weight: bold;
                text-align: center;
                padding: 10px 8px;
                border: 1px solid #ccc;
            }

            table td {
                padding: 8px;
                border: 1px solid #e5e7eb;
            }

            .table-section {
                background: white;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                overflow-x: auto;
            }
        </style>
    `;

  if (window.i18n && window.i18n.applyTranslations) {
    window.i18n.applyTranslations();
  }
}

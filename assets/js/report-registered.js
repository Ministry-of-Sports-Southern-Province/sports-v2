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

  fetch(buildReportQuery({ printAll: true }))
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        populatePrintContainer(data.data);
        window.print();
        setTimeout(() => {
          document.title = originalTitle;
          generateReport(currentPage);
        }, 750);
      }
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
        <div class="text-center mb-6 no-print">
            <h2 class="text-2xl font-bold">දකුණු පළාත් ක්රීඩා දෙපාර්තමේන්තුව</h2>
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
    `;

  if (window.i18n && window.i18n.applyTranslations) {
    window.i18n.applyTranslations();
  }
}

function populatePrintContainer(data) {
  const printContainer = document.getElementById("printContainer");
  if (!printContainer) return;

  const district = document.getElementById("district").value;
  const dateRange = document.getElementById("dateRange").value;
  const districtText =
    district ||
    (window.i18n ? window.i18n.t("filter.all_districts") : "All Districts");
  const rangeText =
    dateRange === "year"
      ? window.i18n
        ? window.i18n.t("filter.year")
        : "This Year"
      : dateRange === "month"
        ? window.i18n
          ? window.i18n.t("filter.month")
          : "This Month"
        : window.i18n
          ? window.i18n.t("filter.alltime")
          : "All Time";
  const filterText = `${districtText} | ${rangeText}`;

  printContainer.innerHTML = "";
  if (data.length === 0) {
    printContainer.innerHTML =
      '<div class="print-page"><p style="text-align: center; padding: 20px;">No data available</p><div class="page-number-footer">Page 1</div></div>';
    return;
  }

  const firstPageRows = 32;
  const otherPageRows = 38;
  const pages = [];
  let dataIndex = 0;

  while (dataIndex < data.length) {
    const isFirstPage = pages.length === 0;
    const maxRows = isFirstPage ? firstPageRows : otherPageRows;
    const remainingRows = data.length - dataIndex;
    let rowsThisPage = Math.min(maxRows, remainingRows);
    if (
      remainingRows - rowsThisPage < 10 &&
      !isFirstPage &&
      remainingRows > rowsThisPage
    ) {
      rowsThisPage = remainingRows;
    }
    pages.push(data.slice(dataIndex, dataIndex + rowsThisPage));
    dataIndex += rowsThisPage;
  }

  const totalPages = pages.length;
  pages.forEach((pageData, pageNum) => {
    const pageDiv = document.createElement("div");
    pageDiv.className = "print-page";
    let pageHTML = "";

    if (pageNum === 0) {
      const deptName = window.i18n
        ? window.i18n.t("header.department_name")
        : "Department of Sports Southern Province";
      const reportTitle = window.i18n
        ? window.i18n.t("report.type_registered")
        : "Registered Clubs Report";
      pageHTML += `
        <div class="print-header">
          <div class="dept-name">${deptName}</div>
          <h1>${reportTitle}</h1>
          <div class="report-subtitle">${filterText}</div>
        </div>
      `;
    }

    pageHTML += `
      <table>
        <thead>
          <tr>
            <th style="width: 4%;">#</th>
            <th style="width: 12%;">Reg No.</th>
            <th style="width: 28%;">Club Name</th>
            <th style="width: 12%;">District</th>
            <th style="width: 18%;">Chairman</th>
            <th style="width: 13%;">Phone</th>
            <th style="width: 13%;">Reg Date</th>
          </tr>
        </thead>
        <tbody>
    `;

    pageData.forEach((row, idx) => {
      const globalIdx =
        pages.slice(0, pageNum).reduce((sum, p) => sum + p.length, 0) + idx + 1;
      pageHTML += `
        <tr>
          <td style="text-align: center;">${globalIdx}</td>
          <td>${row.reg_number || "-"}</td>
          <td>${row.name || "-"}</td>
          <td>${row.district || "-"}</td>
          <td>${row.chairman || "-"}</td>
          <td>${row.chairman_phone || "-"}</td>
          <td style="text-align: center; white-space: nowrap;">${row.registration_date || "-"}</td>
        </tr>
      `;
    });

    pageHTML += `</tbody></table>`;

    if (pageNum === totalPages - 1) {
      const preparedBy = window.i18n
        ? window.i18n.t("footer.prepared_by")
        : "Prepared By";
      const approvedBy = window.i18n
        ? window.i18n.t("footer.approved_by")
        : "Approved By";
      pageHTML += `
        <div class="print-footer">
          <div class="signatures">
            <div class="sig-block"><div class="sig-line"></div><div class="sig-label">${preparedBy}</div></div>
            <div class="sig-block"><div class="sig-line"></div><div class="sig-label">${approvedBy}</div></div>
          </div>
        </div>
      `;
    }

    pageHTML += `<div class="page-number-footer">Page ${pageNum + 1} of ${totalPages}</div>`;
    pageDiv.innerHTML = pageHTML;
    printContainer.appendChild(pageDiv);
  });
}

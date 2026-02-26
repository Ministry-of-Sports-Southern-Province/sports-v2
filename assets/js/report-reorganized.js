document.addEventListener("DOMContentLoaded", function () {
  loadDistricts();
  populateYears();

  // Real-time report generation
  document
    .getElementById("year")
    .addEventListener("change", () => generateReport(1));
  document
    .getElementById("district")
    .addEventListener("change", () => generateReport(1));

  // Register callback for language changes to regenerate report
  if (window.i18n && typeof window.i18n.onLanguageChange === "function") {
    window.i18n.onLanguageChange(() => {
      generateReport(currentPage);
    });
  }
});

let currentPage = 1;
let rowsPerPage = 10;
let totalPages = 1;
let totalRows = 0;

function populateYears() {
  const select = document.getElementById("year");
  const currentYear = new Date().getFullYear();
  for (let year = currentYear; year >= currentYear - 10; year--) {
    const opt = document.createElement("option");
    opt.value = year;
    opt.textContent = year;
    select.appendChild(opt);
  }
  generateReport(1);
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

function buildReportQuery({
  page = 1,
  limit = rowsPerPage,
  printAll = false,
} = {}) {
  const year = document.getElementById("year").value;
  const district = document.getElementById("district").value;

  const params = new URLSearchParams();
  params.append("type", "reorganized");
  params.append("year", year);
  params.append("district", district);
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
  const year = document.getElementById("year").value;
  const district = document.getElementById("district").value;

  fetch(buildReportQuery({ page: currentPage, limit: rowsPerPage }))
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        displayReport(data.data, year, district);
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

  const yearVal = document.getElementById("year")?.value;
  const districtVal = document.getElementById("district")?.value;
  let filterInfo = "";
  if (yearVal) filterInfo += "_" + String(yearVal);
  if (districtVal) filterInfo += "_" + districtVal.replace(/\s+/g, "_");

  document.title = "Reorganized_Clubs_Report_" + dateStr + filterInfo;

  const year = document.getElementById("year").value;
  const district = document.getElementById("district").value;

  fetch(buildReportQuery({ printAll: true }))
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        populatePrintContainer(data.data, year, district);
        window.print();
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

function displayReport(data, year, district) {
  const output = document.getElementById("reportOutput");
  const districtText =
    district ||
    (window.i18n ? window.i18n.t("filter.all_districts") : "All Districts");

  output.innerHTML = `
        <div class="print-header" style="display: none;">
            <div class="dept-name" data-i18n="header.department_name">Department of Sports Southern Province</div>
            <h1 data-i18n="report.type_reorganized">ප්රතිසංවිධාන සමාජ වාර්තාව</h1>
            <div class="text-sm">වර්ෂය: ${year} | දිස්ත්රික්කය: ${districtText}</div>
        </div>

        <div class="text-center mb-6 no-print">
            <h2 class="text-2xl font-bold">${window.i18n ? window.i18n.t("header.department_name") : "Department of Sports Southern Province"}</h2>
            <h3 class="text-xl mt-2">${window.i18n ? window.i18n.t("report.type_reorganized") : "Reorganized Clubs Report"} - ${year}</h3>
            <p class="text-sm text-gray-600 mt-2">${window.i18n ? window.i18n.t("table.district") : "District"}: ${districtText}</p>
            <p class="text-sm text-gray-600">${window.i18n ? window.i18n.t("report.generated_date") : "Generated Date"}: ${new Date().toLocaleDateString()}</p>
        </div>
        <table class="report-table min-w-full border-collapse">
            <thead>
                <tr>
                    <th>#</th>
                    <th>${window.i18n ? window.i18n.t("table.reg_number") : "Reg No"}</th>
                    <th>${window.i18n ? window.i18n.t("table.club_name") : "Club Name"}</th>
                    <th>${window.i18n ? window.i18n.t("table.district") : "District"}</th>
                    <th>${window.i18n ? window.i18n.t("table.chairman") : "Chairman"}</th>
                    <th>${window.i18n ? window.i18n.t("table.phone") : "Phone"}</th>
                    <th>${window.i18n ? window.i18n.t("table.reorg_date") : "Reorganization Date"}</th>
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
                        <td class="text-slate-800">${row.reorg_date}</td>
                    </tr>
                `,
                  )
                  .join("")}
            </tbody>
        </table>
        <div class="mt-6 text-sm text-gray-600">
            <p>${window.i18n ? window.i18n.t("report.total_records") : "Total Records"}: ${data.length}</p>
        </div>

        <div class="print-footer" style="display: none;">
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label" data-i18n="footer.created_by">Created By</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label" data-i18n="footer.approved_by">Approved By</div>
            </div>
        </div>
    `;

  if (window.i18n && window.i18n.applyTranslations) {
    window.i18n.applyTranslations();
  }
}

function populatePrintContainer(data, year, district) {
  const printContainer = document.getElementById("printContainer");
  if (!printContainer) return;

  const districtText =
    district ||
    (window.i18n ? window.i18n.t("filter.all_districts") : "All Districts");
  const filterText = `${districtText} | ${year}`;

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
      remainingRows - rowsThisPage < 8 &&
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
        ? window.i18n.t("report.type_reorganized")
        : "Reorganized Clubs Report";
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
            <th style="width: 12%;">${window.i18n ? window.i18n.t("table.reg_number") : "Reg No."}</th>
            <th style="width: 28%;">${window.i18n ? window.i18n.t("table.club_name") : "Club Name"}</th>
            <th style="width: 12%;">${window.i18n ? window.i18n.t("table.district") : "District"}</th>
            <th style="width: 18%;">${window.i18n ? window.i18n.t("table.chairman") : "Chairman"}</th>
            <th style="width: 13%;">${window.i18n ? window.i18n.t("table.phone") : "Phone"}</th>
            <th style="width: 13%;">${window.i18n ? window.i18n.t("table.reorg_date") : "Reorg Date"}</th>
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
          <td style="text-align: center; white-space: nowrap;">${row.reorg_date || "-"}</td>
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
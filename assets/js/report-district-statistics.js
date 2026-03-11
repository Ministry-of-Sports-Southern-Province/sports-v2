document.addEventListener("DOMContentLoaded", function () {
  const output = document.getElementById("reportOutput");
  output.innerHTML =
    '<div class="text-center py-8 text-gray-600">පූරණය වෙමින්...</div>';

  initDateDefaults();

  Promise.all([loadDistricts()])
    .then(() => {
      console.log("Districts loaded, generating initial report");
      setTimeout(() => generateReport(1), 300);
    })
    .catch((err) => {
      console.error("Error loading data:", err);
      output.innerHTML =
        '<div class="text-center py-8 text-red-600"><p>ප්‍රారම්භක දත්ත පූරණයේ දෝෂයක්</p></div>';
    });

  // Real-time report generation on any filter change
  const districtSelect = document.getElementById("district");
  if (districtSelect) {
    districtSelect.addEventListener("change", () => generateReport(1));
  }
  ["reg_date_from", "reg_date_to", "reorg_date_from", "reorg_date_to"].forEach(
    (id) => {
      const el = document.getElementById(id);
      if (el) el.addEventListener("change", () => generateReport(1));
    },
  );

  // Register callback for language changes to regenerate report
  if (window.i18n && typeof window.i18n.onLanguageChange === "function") {
    window.i18n.onLanguageChange(() => {
      if (currentDisplayedData) {
        displayReport(
          currentDisplayedData.data,
          currentDisplayedData.district,
          currentDisplayedData.regFrom,
          currentDisplayedData.regTo,
          currentDisplayedData.reorgFrom,
          currentDisplayedData.reorgTo,
        );
      }
    });
  }
});

let currentPage = 1;
let rowsPerPage = 10;
let totalPages = 1;
let totalRows = 0;
let grandTotalClubs = 0;
let grandTotalYearRegistered = 0;
let grandTotalYearReorganized = 0;
let currentDisplayedData = null;

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

function initDateDefaults() {
  const year = new Date().getFullYear();
  const from = year + "-01-01";
  const to = year + "-12-31";
  ["reg_date_from", "reorg_date_from"].forEach((id) => {
    const el = document.getElementById(id);
    if (el && !el.value) el.value = from;
  });
  ["reg_date_to", "reorg_date_to"].forEach((id) => {
    const el = document.getElementById(id);
    if (el && !el.value) el.value = to;
  });
}

function buildReportQuery({
  page = 1,
  limit = rowsPerPage,
  printAll = false,
} = {}) {
  const district = document.getElementById("district").value;
  const regDateFrom = document.getElementById("reg_date_from")?.value || "";
  const regDateTo = document.getElementById("reg_date_to")?.value || "";
  const reorgDateFrom = document.getElementById("reorg_date_from")?.value || "";
  const reorgDateTo = document.getElementById("reorg_date_to")?.value || "";

  const params = new URLSearchParams();
  params.append("type", "district_statistics");
  if (district) params.append("district", district);
  if (regDateFrom) params.append("reg_date_from", regDateFrom);
  if (regDateTo) params.append("reg_date_to", regDateTo);
  if (reorgDateFrom) params.append("reorg_date_from", reorgDateFrom);
  if (reorgDateTo) params.append("reorg_date_to", reorgDateTo);
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
  const regDateFrom = document.getElementById("reg_date_from")?.value || "";
  const regDateTo = document.getElementById("reg_date_to")?.value || "";
  const reorgDateFrom = document.getElementById("reorg_date_from")?.value || "";
  const reorgDateTo = document.getElementById("reorg_date_to")?.value || "";
  const output = document.getElementById("reportOutput");

  output.innerHTML =
    '<div class="text-center py-8 text-gray-600">පූරණය වෙමින්...</div>';

  fetch(buildReportQuery({ page: currentPage, limit: rowsPerPage }))
    .then((res) => res.json())
    .then((data) => {
      if (data.success && data.data) {
        if (data.pagination && data.pagination.totals) {
          grandTotalClubs = data.pagination.totals.total_clubs || 0;
          grandTotalYearRegistered =
            data.pagination.totals.total_registered || 0;
          grandTotalYearReorganized =
            data.pagination.totals.total_reorganized || 0;
        }
        if (data.pagination) {
          totalRows = data.pagination.total || 0;
        }
        displayReport(
          data.data,
          district,
          regDateFrom,
          regDateTo,
          reorgDateFrom,
          reorgDateTo,
        );
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

// Reset filters to current-year defaults
function resetFilters() {
  const districtSelect = document.getElementById("district");
  if (districtSelect) districtSelect.value = "";
  // Force re-init regardless of existing values
  const year = new Date().getFullYear();
  const from = year + "-01-01";
  const to = year + "-12-31";
  ["reg_date_from", "reorg_date_from"].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.value = from;
  });
  ["reg_date_to", "reorg_date_to"].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.value = to;
  });
  currentPage = 1;
  generateReport(1);
}

// Print: load all rows then trigger browser print dialog
function printReportWithDate() {
  const originalTitle = document.title;
  const now = new Date();
  const dateStr =
    now.getFullYear() +
    "-" +
    String(now.getMonth() + 1).padStart(2, "0") +
    "-" +
    String(now.getDate()).padStart(2, "0");

  const district = document.getElementById("district")?.value || "";
  document.title =
    "District_Statistics_Report_" +
    dateStr +
    (district ? "_" + district.replace(/\s+/g, "_") : "");

  const districtVal = document.getElementById("district").value;
  const regDateFromVal = document.getElementById("reg_date_from")?.value || "";
  const regDateToVal = document.getElementById("reg_date_to")?.value || "";
  const reorgDateFromVal =
    document.getElementById("reorg_date_from")?.value || "";
  const reorgDateToVal = document.getElementById("reorg_date_to")?.value || "";

  fetch(buildReportQuery({ printAll: true }))
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        if (data.pagination && data.pagination.totals) {
          grandTotalClubs = data.pagination.totals.total_clubs || 0;
          grandTotalYearRegistered =
            data.pagination.totals.total_registered || 0;
          grandTotalYearReorganized =
            data.pagination.totals.total_reorganized || 0;
        }
        const originalPage = currentPage;
        currentPage = 1;
        displayReport(
          data.data,
          districtVal,
          regDateFromVal,
          regDateToVal,
          reorgDateFromVal,
          reorgDateToVal,
        );
        window.print();
        setTimeout(() => {
          document.title = originalTitle;
          currentPage = originalPage;
          generateReport(originalPage);
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

function displayReport(data, district, regFrom, regTo, reorgFrom, reorgTo) {
  const output = document.getElementById("reportOutput");

  // Store current data for language switching
  currentDisplayedData = { data, district, regFrom, regTo, reorgFrom, reorgTo };

  if (!data || data.length === 0) {
    const noDataMsg =
      window.i18n?.t("message.no_results") || "No results found";
    const tryOtherMsg =
      window.i18n?.t("placeholder.select") || "Select another filter";
    output.innerHTML = `<div class="text-center py-12 text-gray-500">
      <p class="text-lg">❌ ${noDataMsg}</p>
      <p class="text-sm mt-2">${tryOtherMsg}</p>
    </div>`;
    return;
  }

  // Translations
  const allDistrictsText =
    window.i18n?.t("filter.all_districts") || "All Districts";
  const divisionHeader = window.i18n?.t("table.division") || "Division";
  const totalClubsHeader = window.i18n?.t("stats.total_clubs") || "Total Clubs";
  const regHeader = window.i18n?.t("report.date_range_reg") || "Registered";
  const reorgHeader =
    window.i18n?.t("report.date_range_reorg") || "Reorganized";
  const totalLabel = window.i18n?.t("table.total") || "Total";
  const deptName =
    window.i18n?.t("header.department_name") || "Department of Sports";
  const reportTitle =
    window.i18n?.t("report.type_district_statistics") ||
    "District Statistics Report";
  const districtLabel = window.i18n?.t("table.district") || "District";
  const generatedLabel =
    window.i18n?.t("report.generated_date") || "Generated Date";
  const reportIdLabel = window.i18n?.t("report.report_id") || "Report ID";
  const preparedByLabel = window.i18n?.t("footer.prepared_by") || "Prepared By";
  const approvedByLabel = window.i18n?.t("footer.approved_by") || "Approved By";
  const regRangeLabel =
    window.i18n?.t("report.date_range_reg") || "Registration Date Range";
  const reorgRangeLabel =
    window.i18n?.t("report.date_range_reorg") || "Reorganization Date Range";
  const reportNoteLabel =
    window.i18n?.t("footer.report_note") ||
    "This report was generated by the Department of Sports Southern Province";
  const chartByDivLabel =
    window.i18n?.t("stats.distribution_by_district") ||
    "Distribution by Division";
  const chartSummaryLabel = window.i18n?.t("stats.overview") || "Overview";

  const districtText = district || allDistrictsText;
  const reportDate = new Date();
  const reportDateStr = reportDate.toLocaleDateString(undefined, {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
  });
  const reportId =
    "DSR-" +
    reportDate.getFullYear() +
    String(reportDate.getMonth() + 1).padStart(2, "0") +
    String(reportDate.getDate()).padStart(2, "0") +
    String(reportDate.getHours()).padStart(2, "0") +
    String(reportDate.getMinutes()).padStart(2, "0") +
    String(reportDate.getSeconds()).padStart(2, "0");

  const fmtDate = (d) => (d ? d.replace(/-/g, "/") : "-");
  const regRangeText = `${fmtDate(regFrom)} → ${fmtDate(regTo)}`;
  const reorgRangeText = `${fmtDate(reorgFrom)} → ${fmtDate(reorgTo)}`;

  // ── Build data table ─────────────────────────────────────────
  let tableHTML = `
    <table class="rpt-table">
      <thead>
        <tr>
          <th class="th-idx">#</th>
          <th class="th-div">${divisionHeader}</th>
          <th class="th-num">${totalClubsHeader}</th>
          <th class="th-num">${regHeader}<br><span class="th-sub">${regRangeText}</span></th>
          <th class="th-num">${reorgHeader}<br><span class="th-sub">${reorgRangeText}</span></th>
        </tr>
      </thead>
      <tbody>`;

  data.forEach((row, index) => {
    const rowClass = index % 2 === 0 ? "tr-even" : "tr-odd";
    tableHTML += `
        <tr class="${rowClass}">
          <td class="td-idx">${(currentPage - 1) * rowsPerPage + index + 1}</td>
          <td class="td-div">${row.division_name || "-"}</td>
          <td class="td-num">${row.total_clubs || 0}</td>
          <td class="td-num">${row.year_registered || 0}</td>
          <td class="td-num">${row.year_reorganized || 0}</td>
        </tr>`;
  });

  // Grand totals row — always visible (screen + print)
  tableHTML += `
        <tr class="tr-totals">
          <td class="td-idx" colspan="2"><strong>${totalLabel}</strong></td>
          <td class="td-num"><strong>${grandTotalClubs}</strong></td>
          <td class="td-num"><strong>${grandTotalYearRegistered}</strong></td>
          <td class="td-num"><strong>${grandTotalYearReorganized}</strong></td>
        </tr>
      </tbody>
    </table>`;

  output.innerHTML = `
    <div class="print-section">
      <!-- Screen header (hidden in print) -->
      <div class="rpt-screen-header no-print">
        <div class="rpt-sh-top">
          <div>
            <div class="rpt-dept">${deptName}</div>
            <div class="rpt-title">${reportTitle}</div>
          </div>
          <div class="rpt-id-badge">${reportIdLabel}: ${reportId}</div>
        </div>
        <div class="rpt-meta-grid">
          <div><span class="rpt-meta-lbl">${districtLabel}</span><span class="rpt-meta-val">${districtText}</span></div>
          <div><span class="rpt-meta-lbl">${regRangeLabel}</span><span class="rpt-meta-val">${regRangeText}</span></div>
          <div><span class="rpt-meta-lbl">${reorgRangeLabel}</span><span class="rpt-meta-val">${reorgRangeText}</span></div>
          <div><span class="rpt-meta-lbl">${generatedLabel}</span><span class="rpt-meta-val">${reportDateStr}</span></div>
        </div>
      </div>

      <!-- KPI summary cards -->
      <div class="rpt-kpi-row">
        <div class="rpt-kpi rpt-kpi-blue">
          <div class="rpt-kpi-num">${totalRows}</div>
          <div class="rpt-kpi-lbl">${divisionHeader}</div>
        </div>
        <div class="rpt-kpi rpt-kpi-green">
          <div class="rpt-kpi-num">${grandTotalClubs}</div>
          <div class="rpt-kpi-lbl">${totalClubsHeader}</div>
        </div>
        <div class="rpt-kpi rpt-kpi-amber">
          <div class="rpt-kpi-num">${grandTotalYearRegistered}</div>
          <div class="rpt-kpi-lbl">${regHeader}</div>
        </div>
        <div class="rpt-kpi rpt-kpi-purple">
          <div class="rpt-kpi-num">${grandTotalYearReorganized}</div>
          <div class="rpt-kpi-lbl">${reorgHeader}</div>
        </div>
      </div>

      <!-- Charts row (screen only) -->
      <div class="rpt-charts-row no-print">
        <div class="rpt-chart-card">
          <div class="rpt-chart-hdr">${chartByDivLabel}</div>
          <div style="position:relative;height:220px;"><canvas id="rptBarChart"></canvas></div>
        </div>
        <div class="rpt-chart-card">
          <div class="rpt-chart-hdr">${chartSummaryLabel}</div>
          <div style="position:relative;height:220px;"><canvas id="rptSummaryChart"></canvas></div>
        </div>
      </div>

      <!-- Print header (shown in print only) -->
      <div class="print-header" style="display:none;">
        <div class="ph-dept">${deptName}</div>
        <div class="ph-title">${reportTitle}</div>
        <div class="ph-rule"></div>
        <table class="ph-meta">
          <tr>
            <td class="ph-lbl">${districtLabel}:</td>
            <td class="ph-val">${districtText}</td>
            <td class="ph-lbl">${reportIdLabel}:</td>
            <td class="ph-val">${reportId}</td>
          </tr>
          <tr>
            <td class="ph-lbl">${regRangeLabel}:</td>
            <td class="ph-val">${regRangeText}</td>
            <td class="ph-lbl">${reorgRangeLabel}:</td>
            <td class="ph-val">${reorgRangeText}</td>
          </tr>
          <tr>
            <td class="ph-lbl">${generatedLabel}:</td>
            <td class="ph-val" colspan="3">${reportDateStr}</td>
          </tr>
        </table>
      </div>

      <!-- Table -->
      <div class="table-section">
        ${tableHTML}
      </div>

      <!-- Print footer (shown in print only) -->
      <div class="print-footer" style="display:none;">
        <div class="pf-note">* ${reportNoteLabel}</div>
        <div class="pf-sigs">
          <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">${preparedByLabel}</div>
          </div>
          <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">${approvedByLabel}</div>
          </div>
        </div>
      </div>
    </div>
        <style>
            /* ── Report styles (scoped) ── */
            .rpt-screen-header {
              background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
              color: #fff;
              border-radius: 8px;
              padding: 18px 22px;
              margin-bottom: 16px;
            }
            .rpt-sh-top {
              display: flex;
              justify-content: space-between;
              align-items: flex-start;
              margin-bottom: 14px;
            }
            .rpt-dept {
              font-size: 11px;
              text-transform: uppercase;
              letter-spacing: 0.8px;
              opacity: 0.8;
              margin-bottom: 4px;
            }
            .rpt-title { font-size: 20px; font-weight: 800; }
            .rpt-id-badge {
              font-size: 11px;
              background: rgba(255,255,255,0.15);
              padding: 4px 12px;
              border-radius: 20px;
              white-space: nowrap;
              opacity: 0.85;
            }
            .rpt-meta-grid {
              display: grid;
              grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
              gap: 10px;
              font-size: 12px;
            }
            .rpt-meta-lbl {
              display: block;
              font-size: 10px;
              opacity: 0.7;
              text-transform: uppercase;
              letter-spacing: 0.4px;
              margin-bottom: 2px;
            }
            .rpt-meta-val { display: block; font-weight: 600; }

            /* KPI cards */
            .rpt-kpi-row {
              display: grid;
              grid-template-columns: repeat(4, 1fr);
              gap: 12px;
              margin-bottom: 20px;
            }
            .rpt-kpi {
              background: #fff;
              border-radius: 8px;
              padding: 16px;
              border-top: 4px solid;
              box-shadow: 0 1px 4px rgba(0,0,0,0.07);
            }
            .rpt-kpi-blue   { border-color: #3b82f6; }
            .rpt-kpi-green  { border-color: #10b981; }
            .rpt-kpi-amber  { border-color: #f59e0b; }
            .rpt-kpi-purple { border-color: #8b5cf6; }
            .rpt-kpi-num { font-size: 28px; font-weight: 800; line-height: 1; }
            .rpt-kpi-blue   .rpt-kpi-num { color: #3b82f6; }
            .rpt-kpi-green  .rpt-kpi-num { color: #10b981; }
            .rpt-kpi-amber  .rpt-kpi-num { color: #f59e0b; }
            .rpt-kpi-purple .rpt-kpi-num { color: #8b5cf6; }
            .rpt-kpi-lbl { font-size: 11px; color: #6b7280; margin-top: 4px; font-weight: 500; }

            /* Charts row */
            .rpt-charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
            .rpt-chart-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); padding: 14px 16px; }
            .rpt-chart-hdr { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 10px; }
            @media (max-width: 640px) { .rpt-charts-row { grid-template-columns: 1fr; } }

            /* Table */
            .table-section { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow-x: auto; }
            .rpt-table { width: 100%; border-collapse: collapse; }
            .th-idx { background:#1e3a8a; color:#fff; padding:10px 12px; border:1px solid #1e3a8a; font-size:13px; font-weight:600; width:48px; text-align:center; }
            .th-div { background:#1e3a8a; color:#fff; padding:10px 12px; border:1px solid #1e3a8a; font-size:13px; font-weight:600; }
            .th-num { background:#1e3a8a; color:#fff; padding:10px 12px; border:1px solid #1e3a8a; font-size:13px; font-weight:600; text-align:center; }
            .th-sub { font-size:10px; font-weight:400; opacity:0.8; display:block; margin-top:2px; }
            .tr-even { background:#ffffff; }
            .tr-odd  { background:#f8fafc; }
            .tr-totals { background:#dbeafe; }
            .td-idx { border:1px solid #e2e8f0; padding:10px 12px; text-align:center; color:#64748b; font-size:13px; }
            .td-div { border:1px solid #e2e8f0; padding:10px 12px; font-weight:500; color:#111827; font-size:13px; }
            .td-num { border:1px solid #e2e8f0; padding:10px 12px; text-align:center; font-size:13px; color:#111827; }
            .tr-totals .td-idx,
            .tr-totals .td-div,
            .tr-totals .td-num { font-weight:700; color:#1e3a8a; }

            /* Print footer */
            .pf-note { }
            .pf-sigs { }

            @media (max-width: 640px) {
              .rpt-kpi-row { grid-template-columns: repeat(2, 1fr); }
            }

            /* ── Print styles ── */
            @media print {
                @page { size: A4 portrait; margin: 10mm; }
                body { background: white; font-size: 9pt; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                .no-print { display: none !important; }
                .rpt-screen-header { display: none !important; }
                .print-header { display: block !important; margin-bottom: 14px; }
                .ph-dept { font-size: 9pt; font-weight: bold; text-transform: uppercase; color: #374151; letter-spacing: 0.5px; text-align: center; margin-bottom: 2px; }
                .ph-title { font-size: 18pt; font-weight: 900; color: #1e3a8a; text-align: center; margin: 3px 0 7px; }
                .ph-rule { border-bottom: 2px solid #1e3a8a; margin-bottom: 8px; }
                .ph-meta { width: 100%; font-size: 8pt; border-collapse: collapse; margin-bottom: 10px; }
                .ph-lbl { color: #6b7280; padding: 2px 6px 2px 0; white-space: nowrap; font-weight: 600; width: 130px; }
                .ph-val { color: #111827; padding: 2px 16px 2px 2px; font-weight: 500; }

                /* KPI strip in print */
                .rpt-kpi-row { display: grid !important; grid-template-columns: repeat(4, 1fr); gap: 5px; margin-bottom: 10px; }
                .rpt-kpi { border-radius: 4px; padding: 6px 8px; border-top-width: 3px; box-shadow: none; background: #fff !important; border-top-style: solid; }
                .rpt-kpi-blue   { border-top-color: #3b82f6 !important; }
                .rpt-kpi-green  { border-top-color: #10b981 !important; }
                .rpt-kpi-amber  { border-top-color: #f59e0b !important; }
                .rpt-kpi-purple { border-top-color: #8b5cf6 !important; }
                .rpt-kpi-num { font-size: 14pt !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                .rpt-kpi-blue   .rpt-kpi-num { color: #3b82f6 !important; }
                .rpt-kpi-green  .rpt-kpi-num { color: #10b981 !important; }
                .rpt-kpi-amber  .rpt-kpi-num { color: #f59e0b !important; }
                .rpt-kpi-purple .rpt-kpi-num { color: #8b5cf6 !important; }
                .rpt-kpi-lbl { font-size: 7pt !important; }

                .table-section { overflow: visible !important; box-shadow: none; }
                .rpt-table { font-size: 8pt; margin-top: 6px; }
                .th-idx, .th-div, .th-num {
                    background-color: #1e3a8a !important;
                    color: white !important;
                    padding: 5px 4px !important;
                    font-size: 8pt;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
                .th-sub { font-size: 6.5pt; }
                .td-idx, .td-div, .td-num { padding: 4px !important; font-size: 8pt; }
                .tr-even { background: #ffffff !important; }
                .tr-odd  { background: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                .tr-totals { background: #dbeafe !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

                .print-footer { display: block !important; margin-top: 24px; page-break-inside: avoid; }
                .pf-note { font-size: 7pt; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 6px; margin-bottom: 18px; }
                .pf-sigs { display: flex; justify-content: space-around; }
                .sig-block { text-align: center; width: 180px; }
                .sig-line { border-bottom: 1px dotted #1e3a8a; margin-bottom: 6px; height: 30px; }
                .sig-label { font-size: 8pt; font-weight: bold; text-transform: uppercase; color: #374151; }
            }
        </style>
    `;

  if (window.i18n && window.i18n.applyTranslations) {
    window.i18n.applyTranslations();
  }

  // Build charts after DOM is ready
  buildReportCharts(data, totalClubsHeader, regHeader, reorgHeader);
}

/* ================================
   REPORT CHARTS
================================ */
let _rptBarChart = null;
let _rptSummaryChart = null;

function buildReportCharts(data, totalClubsLabel, regLabel, reorgLabel) {
  if (typeof Chart === "undefined") return;

  const labels = data.map((r) => r.division_name || "-");
  const totals = data.map((r) => Number(r.total_clubs) || 0);
  const regs = data.map((r) => Number(r.year_registered) || 0);
  const reorgs = data.map((r) => Number(r.year_reorganized) || 0);

  const fontFamily = "Noto Sans Sinhala, Noto Sans Tamil, Roboto, sans-serif";
  const tooltipCfg = {
    backgroundColor: "rgba(0,0,0,0.8)",
    padding: 8,
    titleFont: { family: fontFamily, size: 11 },
    bodyFont: { family: fontFamily, size: 10 },
  };

  // ── Grouped bar: per-division breakdown ──────────────────────
  if (_rptBarChart) {
    _rptBarChart.destroy();
    _rptBarChart = null;
  }
  const barCtx = document.getElementById("rptBarChart");
  if (barCtx) {
    _rptBarChart = new Chart(barCtx, {
      type: "bar",
      data: {
        labels,
        datasets: [
          {
            label: totalClubsLabel,
            data: totals,
            backgroundColor: "#3b82f6",
            borderRadius: 3,
          },
          {
            label: regLabel,
            data: regs,
            backgroundColor: "#10b981",
            borderRadius: 3,
          },
          {
            label: reorgLabel,
            data: reorgs,
            backgroundColor: "#8b5cf6",
            borderRadius: 3,
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
              font: { family: fontFamily, size: 10 },
              padding: 8,
              boxWidth: 10,
            },
          },
          tooltip: tooltipCfg,
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1, font: { size: 9 } },
            grid: { color: "rgba(0,0,0,0.05)" },
          },
          x: {
            ticks: { font: { family: fontFamily, size: 9 }, maxRotation: 30 },
            grid: { display: false },
          },
        },
      },
    });
  }

  // ── Doughnut summary: grand totals ───────────────────────────
  if (_rptSummaryChart) {
    _rptSummaryChart.destroy();
    _rptSummaryChart = null;
  }
  const summaryCtx = document.getElementById("rptSummaryChart");
  if (summaryCtx) {
    const total = grandTotalClubs || 0;
    _rptSummaryChart = new Chart(summaryCtx, {
      type: "doughnut",
      data: {
        labels: [totalClubsLabel, regLabel, reorgLabel],
        datasets: [
          {
            data: [
              grandTotalClubs,
              grandTotalYearRegistered,
              grandTotalYearReorganized,
            ],
            backgroundColor: ["#3b82f6", "#10b981", "#8b5cf6"],
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
          legend: {
            position: "bottom",
            labels: {
              font: { family: fontFamily, size: 10 },
              padding: 8,
              boxWidth: 10,
            },
          },
          tooltip: {
            ...tooltipCfg,
            callbacks: {
              label(ctx) {
                const val = ctx.parsed || 0;
                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                return `${ctx.label}: ${val} (${pct}%)`;
              },
            },
          },
        },
      },
      plugins: [
        {
          id: "centerTotal",
          beforeDraw(chart) {
            const { width, height, ctx: c } = chart;
            c.restore();
            const fs = (height / 100).toFixed(2);
            c.font = `bold ${fs}em sans-serif`;
            c.textBaseline = "middle";
            c.fillStyle = "#374151";
            const txt = total.toString();
            c.fillText(
              txt,
              Math.round((width - c.measureText(txt).width) / 2),
              height / 2 - 8,
            );
            c.font = `normal ${fs * 0.4}em sans-serif`;
            c.fillStyle = "#6b7280";
            const sub = window.i18n?.t("stats.total_label") || "Total";
            c.fillText(
              sub,
              Math.round((width - c.measureText(sub).width) / 2),
              height / 2 + 14,
            );
            c.save();
          },
        },
      ],
    });
  }
}

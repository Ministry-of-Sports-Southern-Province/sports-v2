let districtChart = null;
let statusChart = null;
let registrationChart = null;
let realtimeFallbackTimer = null;
let currentRegistrationFilter = "alltime";

document.addEventListener("DOMContentLoaded", function () {
  loadSummaryData();
  startRealtimeSummary();
  setupFilterButtons();
});

function setupFilterButtons() {
  document.querySelectorAll(".filter-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      currentRegistrationFilter = this.getAttribute("data-filter");
      loadRegistrationData(currentRegistrationFilter);
      updateFilterButtonStyles();
    });
  });
  // Set initial active button
  updateFilterButtonStyles();
}

function loadSummaryData() {
  return fetch(`../api/summary.php?filter=${currentRegistrationFilter}`)
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        updateStats(data.data);
        if (!districtChart) createDistrictChart(data.data.byDistrict);
        else updateDistrictChart(data.data.byDistrict);

        if (!statusChart) createStatusChart(data.data.byStatus);
        else updateStatusChart(data.data.byStatus);

        if (!registrationChart)
          createRegistrationChart(data.data.registrationTrend);
        else updateRegistrationChart(data.data.registrationTrend);
      }
      return data;
    })
    .catch((err) => {
      console.error(err);
      throw err;
    });
}

function startRealtimeSummary() {
  // Prefer Server-Sent Events (simple, works over standard PHP/Apache)
  if (window.EventSource) {
    try {
      const sse = new EventSource("../api/summary-stream.php");

      sse.addEventListener("message", (evt) => {
        try {
          const payload = JSON.parse(evt.data);
          if (payload && payload.data) {
            applyRealtimePayload(payload.data);
          }
        } catch (e) {
          console.error("Invalid SSE payload", e);
        }
      });

      sse.addEventListener("error", (err) => {
        console.warn("SSE connection error, falling back to polling", err);
        sse.close();
        startPollingFallback();
      });

      // Clear any previous fallback
      clearPollingFallback();
      return;
    } catch (e) {
      console.warn("SSE not available, fallback to polling", e);
    }
  }

  // Fallback for browsers without EventSource
  startPollingFallback();
}

function applyRealtimePayload(data) {
  updateStats(data);

  if (data.byDistrict) {
    if (!districtChart) createDistrictChart(data.byDistrict);
    else updateDistrictChart(data.byDistrict);
  }

  if (data.byStatus) {
    if (!statusChart) createStatusChart(data.byStatus);
    else updateStatusChart(data.byStatus);
  }

  if (data.registrationTrend) {
    if (!registrationChart) createRegistrationChart(data.registrationTrend);
    else updateRegistrationChart(data.registrationTrend);
  }
}

function startPollingFallback() {
  // Poll every 10 seconds when SSE is not available
  if (realtimeFallbackTimer) return;
  realtimeFallbackTimer = setInterval(() => {
    loadSummaryData().catch(() => {});
  }, 10000);
}

function clearPollingFallback() {
  if (realtimeFallbackTimer) {
    clearInterval(realtimeFallbackTimer);
    realtimeFallbackTimer = null;
  }
}

function updateFilterButtonStyles() {
  document.querySelectorAll(".filter-btn").forEach((btn) => {
    const filter = btn.getAttribute("data-filter");
    if (filter === currentRegistrationFilter) {
      btn.classList.remove("bg-gray-200", "hover:bg-gray-300");
      btn.classList.add("bg-blue-500", "text-white", "hover:bg-blue-600");
    } else {
      btn.classList.remove("bg-blue-500", "text-white", "hover:bg-blue-600");
      btn.classList.add("bg-gray-200", "hover:bg-gray-300");
      btn.classList.remove("text-white");
    }
  });
}

function loadRegistrationData(filter) {
  return fetch(`../api/summary.php?filter=${filter}`)
    .then((res) => res.json())
    .then((data) => {
      if (data.success && data.data.registrationTrend) {
        if (!registrationChart) {
          createRegistrationChart(data.data.registrationTrend);
        } else {
          updateRegistrationChart(data.data.registrationTrend);
        }
      }
      return data;
    })
    .catch((err) => {
      console.error(err);
      throw err;
    });
}

function updateStats(data) {
  document.getElementById("totalClubs").textContent = data.total || 0;
  document.getElementById("activeClubs").textContent = data.active || 0;
  document.getElementById("expiredClubs").textContent = data.expired || 0;
  document.getElementById("totalReorgs").textContent = data.totalReorgs || 0;
}

function createDistrictChart(data) {
  const ctx = document.getElementById("districtChart").getContext("2d");
  districtChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: data.map((d) => d.district),
      datasets: [
        {
          label: window.i18n ? window.i18n.t("chart.clubs") : "Clubs",
          data: data.map((d) => d.count),
          backgroundColor: "#3b82f6",
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
      },
    },
  });
}

function updateDistrictChart(data) {
  districtChart.data.labels = data.map((d) => d.district);
  districtChart.data.datasets[0].data = data.map((d) => d.count);
  districtChart.update();
}

function createStatusChart(data) {
  const ctx = document.getElementById("statusChart").getContext("2d");
  statusChart = new Chart(ctx, {
    type: "pie",
    data: {
      labels: [
        window.i18n ? window.i18n.t("chart.active") : "Active",
        window.i18n ? window.i18n.t("chart.expired") : "Expired",
      ],
      datasets: [
        {
          data: [data.active, data.expired],
          backgroundColor: ["#10b981", "#f59e0b"],
        },
      ],
    },
    options: {
      responsive: true,
    },
  });
}

function updateStatusChart(data) {
  statusChart.data.datasets[0].data = [data.active, data.expired];
  statusChart.update();
}

function createRegistrationChart(data) {
  const ctx = document.getElementById("registrationChart").getContext("2d");
  registrationChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: data.map((d) => d.month),
      datasets: [
        {
          label: window.i18n
            ? window.i18n.t("chart.registrations")
            : "Registrations",
          data: data.map((d) => d.count),
          borderColor: "#3b82f6",
          backgroundColor: "rgba(59, 130, 246, 0.1)",
          tension: 0.4,
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
      },
    },
  });
}

function updateRegistrationChart(data) {
  registrationChart.data.labels = data.map((d) => d.month);
  registrationChart.data.datasets[0].data = data.map((d) => d.count);
  registrationChart.update();
}

// ═══════════════════════════════════════════════════════════════════════════════
//  STATISTICAL REPORT PRINT MODAL
// ═══════════════════════════════════════════════════════════════════════════════

// ── State ─────────────────────────────────────────────────────────────────────
var prCachedData        = null;        // cached API response
var prActiveTab         = 'combined';  // active tab id
var prReportLang        = null;        // language active in the modal
var prModalTranslations = null;        // translations object for modal language

// ── Chart instances: combined tab ────────────────────────────────────────────
var prYearlyRegChart    = null;
var prYearlyReorgChart  = null;
var prMonthlyRegChart   = null;
var prMonthlyReorgChart = null;
var prComparisonChart   = null;

// ── Chart instances: registrations-only tab ───────────────────────────────────
var prRegOnlyYearlyChart  = null;
var prRegOnlyMonthlyChart = null;

// ── Chart instances: reorganizations-only tab ─────────────────────────────────
var prReorgOnlyYearlyChart  = null;
var prReorgOnlyMonthlyChart = null;

// ── i18n helper — prefers modal translations, falls back to page i18n ─────────
function t(key, fallback) {
  if (prModalTranslations && prModalTranslations[key]) return prModalTranslations[key];
  return (window.i18n && window.i18n.t) ? window.i18n.t(key) : (fallback || key);
}

// ── Month labels in the current modal language ────────────────────────────────
function getMonthLabels() {
  var keys = ['month.jan','month.feb','month.mar','month.apr','month.may','month.jun',
              'month.jul','month.aug','month.sep','month.oct','month.nov','month.dec'];
  var fb   = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  return keys.map(function(k, i) { return t(k, fb[i]); });
}

// ── Wire up buttons once the DOM is ready ────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  var openBtn  = document.getElementById('openStatReportBtn');
  var closeBtn = document.getElementById('closePrintModalBtn');
  var printBtn = document.getElementById('printReportBtn');
  if (openBtn)  openBtn.addEventListener('click',  openStatReportModal);
  if (closeBtn) closeBtn.addEventListener('click', closeStatReportModal);
  if (printBtn) printBtn.addEventListener('click', function () { window.print(); });
});

// ── Open modal ────────────────────────────────────────────────────────────────
function openStatReportModal() {
  var modal   = document.getElementById('statReportModal');
  var loading = document.getElementById('statReportLoading');
  var body    = document.getElementById('statReportBody');
  if (!modal) return;

  // Reset UI to loading state
  prActiveTab = 'combined';
  modal.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
  if (loading) loading.classList.remove('hidden');
  if (body)    body.classList.add('hidden');

  destroyAllPrintCharts();

  // Initialise modal language from page language
  prReportLang        = (window.i18n && window.i18n.currentLanguage) || 'si';
  prModalTranslations = (window.i18n && window.i18n.translations)    || null;
  updatePrLangButtons(prReportLang);
  updatePrTabButtons('combined');
  stampPrDates();

  // Fetch data (or reuse cached)
  if (prCachedData) {
    applyPrData(prCachedData, loading, body);
  } else {
    fetch('../api/summary-print.php')
      .then(function(res) { return res.json(); })
      .then(function(resp) {
        if (!resp.success) throw new Error(resp.error || 'API error');
        prCachedData = resp.data;
        applyPrData(prCachedData, loading, body);
      })
      .catch(function(err) {
        console.error('Statistical report error:', err);
        if (loading) loading.innerHTML = '<p style="color:#ef4444;font-size:14px;">' +
          t('message.error', 'An error occurred') + '</p>';
      });
  }
}

// ── Apply loaded data to the modal ───────────────────────────────────────────
function applyPrData(data, loading, body) {
  updateMonthlyHeadings(data.currentYear);
  renderPrintStats(data.stats);
  renderCombinedCharts(data);    // combined tab always rendered first (it's visible)
  renderAllDistrictTables(data.districtBreakdown);
  applyPrTranslations();
  if (loading) loading.classList.add('hidden');
  if (body)    body.classList.remove('hidden');
}

// ── Close modal ───────────────────────────────────────────────────────────────
function closeStatReportModal() {
  var modal = document.getElementById('statReportModal');
  if (modal) modal.classList.add('hidden');
  document.body.style.overflow = '';
  destroyAllPrintCharts();
  prCachedData = null;
}

// ── Destroy ALL chart instances ───────────────────────────────────────────────
function destroyAllPrintCharts() {
  [prYearlyRegChart, prYearlyReorgChart, prMonthlyRegChart, prMonthlyReorgChart,
   prComparisonChart, prRegOnlyYearlyChart, prRegOnlyMonthlyChart,
   prReorgOnlyYearlyChart, prReorgOnlyMonthlyChart]
    .forEach(function(c) { if (c) c.destroy(); });
  prYearlyRegChart = prYearlyReorgChart = prMonthlyRegChart =
    prMonthlyReorgChart = prComparisonChart = null;
  prRegOnlyYearlyChart = prRegOnlyMonthlyChart = null;
  prReorgOnlyYearlyChart = prReorgOnlyMonthlyChart = null;
}
function destroyPrintCharts() { destroyAllPrintCharts(); } // backward compat alias

// ── Fill stat cards (all three tabs) ─────────────────────────────────────────
function renderPrintStats(stats) {
  function set(id, val) { var el = document.getElementById(id); if (el) el.textContent = val; }
  // Combined
  set('prTotalClubs',   stats.total);
  set('prActiveClubs',  stats.active);
  set('prExpiredClubs', stats.expired);
  set('prTotalReorgs',  stats.totalReorgs);
  // Registrations tab
  set('prRegTotalClubs',   stats.total);
  set('prRegActiveClubs',  stats.active);
  set('prRegExpiredClubs', stats.expired);
  // Reorganizations tab
  set('prReorgTotalReorgs', stats.totalReorgs);
}

// ── Common Chart.js options ───────────────────────────────────────────────────
function prCommonOpts(legendVisible) {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: legendVisible !== false,
                labels: { boxWidth: 12, font: { size: 11 } } }
    },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
  };
}

// ── Combined tab charts ───────────────────────────────────────────────────────
function renderCombinedCharts(data) {
  var years  = data.yearlyRegistrations.map(function(d){return d.year.toString();});
  var months = getMonthLabels();

  // 1. Yearly Registrations
  var c1 = document.getElementById('printYearlyRegChart');
  if (c1) {
    if (prYearlyRegChart) prYearlyRegChart.destroy();
    prYearlyRegChart = new Chart(c1.getContext('2d'), { type:'line', data:{
      labels: years,
      datasets:[{ label:t('report.yearly_registrations','Yearly Registrations'),
        data:data.yearlyRegistrations.map(function(d){return d.count;}),
        borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,0.1)',
        tension:0.35, fill:true, pointRadius:5, pointHoverRadius:7 }]
    }, options: prCommonOpts() });
  }

  // 2. Yearly Reorganizations
  var c2 = document.getElementById('printYearlyReorgChart');
  if (c2) {
    if (prYearlyReorgChart) prYearlyReorgChart.destroy();
    prYearlyReorgChart = new Chart(c2.getContext('2d'), { type:'line', data:{
      labels: years,
      datasets:[{ label:t('report.yearly_reorgs','Yearly Reorganizations'),
        data:data.yearlyReorgs.map(function(d){return d.count;}),
        borderColor:'#7c3aed', backgroundColor:'rgba(124,58,237,0.1)',
        tension:0.35, fill:true, pointRadius:5, pointHoverRadius:7 }]
    }, options: prCommonOpts() });
  }

  // 3. Monthly Registrations
  var c3 = document.getElementById('printMonthlyRegChart');
  if (c3) {
    if (prMonthlyRegChart) prMonthlyRegChart.destroy();
    prMonthlyRegChart = new Chart(c3.getContext('2d'), { type:'bar', data:{
      labels: months,
      datasets:[{ label:t('report.monthly_registrations','Monthly Registrations'),
        data:data.monthlyRegistrations.map(function(d){return d.count;}),
        backgroundColor:'rgba(59,130,246,0.7)', borderColor:'#3b82f6',
        borderWidth:1, borderRadius:3 }]
    }, options: prCommonOpts() });
  }

  // 4. Monthly Reorganizations
  var c4 = document.getElementById('printMonthlyReorgChart');
  if (c4) {
    if (prMonthlyReorgChart) prMonthlyReorgChart.destroy();
    prMonthlyReorgChart = new Chart(c4.getContext('2d'), { type:'bar', data:{
      labels: months,
      datasets:[{ label:t('report.monthly_reorgs','Monthly Reorganizations'),
        data:data.monthlyReorgs.map(function(d){return d.count;}),
        backgroundColor:'rgba(124,58,237,0.7)', borderColor:'#7c3aed',
        borderWidth:1, borderRadius:3 }]
    }, options: prCommonOpts() });
  }

  // 5. Comparison grouped bar
  var c5 = document.getElementById('printComparisonChart');
  if (c5) {
    if (prComparisonChart) prComparisonChart.destroy();
    prComparisonChart = new Chart(c5.getContext('2d'), { type:'bar', data:{
      labels: data.comparisonData.map(function(d){return d.year.toString();}),
      datasets:[
        { label:t('report.yearly_registrations','Registrations'),
          data:data.comparisonData.map(function(d){return d.registrations;}),
          backgroundColor:'rgba(59,130,246,0.75)', borderColor:'#3b82f6',
          borderWidth:1, borderRadius:3 },
        { label:t('report.yearly_reorgs','Reorganizations'),
          data:data.comparisonData.map(function(d){return d.reorgs;}),
          backgroundColor:'rgba(124,58,237,0.75)', borderColor:'#7c3aed',
          borderWidth:1, borderRadius:3 }
      ]
    }, options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{display:true,labels:{boxWidth:12,font:{size:11}}},
                tooltip:{mode:'index',intersect:false} },
      scales:{ x:{stacked:false}, y:{beginAtZero:true,ticks:{precision:0}} }
    }});
  }
}

// ── Registrations-only tab charts ─────────────────────────────────────────────
function renderRegOnlyCharts(data) {
  var years  = data.yearlyRegistrations.map(function(d){return d.year.toString();});
  var months = getMonthLabels();

  var cy = document.getElementById('prRegOnlyYearlyChart');
  if (cy) {
    if (prRegOnlyYearlyChart) prRegOnlyYearlyChart.destroy();
    prRegOnlyYearlyChart = new Chart(cy.getContext('2d'), { type:'line', data:{
      labels: years,
      datasets:[{ label:t('report.yearly_registrations','Yearly Registrations'),
        data:data.yearlyRegistrations.map(function(d){return d.count;}),
        borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,0.1)',
        tension:0.35, fill:true, pointRadius:5, pointHoverRadius:7 }]
    }, options: prCommonOpts() });
  }

  var cm = document.getElementById('prRegOnlyMonthlyChart');
  if (cm) {
    if (prRegOnlyMonthlyChart) prRegOnlyMonthlyChart.destroy();
    prRegOnlyMonthlyChart = new Chart(cm.getContext('2d'), { type:'bar', data:{
      labels: months,
      datasets:[{ label:t('report.monthly_registrations','Monthly Registrations'),
        data:data.monthlyRegistrations.map(function(d){return d.count;}),
        backgroundColor:'rgba(59,130,246,0.7)', borderColor:'#3b82f6',
        borderWidth:1, borderRadius:3 }]
    }, options: prCommonOpts() });
  }
}

// ── Reorganizations-only tab charts ───────────────────────────────────────────
function renderReorgOnlyCharts(data) {
  var years  = data.yearlyReorgs.map(function(d){return d.year.toString();});
  var months = getMonthLabels();

  var cy = document.getElementById('prReorgOnlyYearlyChart');
  if (cy) {
    if (prReorgOnlyYearlyChart) prReorgOnlyYearlyChart.destroy();
    prReorgOnlyYearlyChart = new Chart(cy.getContext('2d'), { type:'line', data:{
      labels: years,
      datasets:[{ label:t('report.yearly_reorgs','Yearly Reorganizations'),
        data:data.yearlyReorgs.map(function(d){return d.count;}),
        borderColor:'#7c3aed', backgroundColor:'rgba(124,58,237,0.1)',
        tension:0.35, fill:true, pointRadius:5, pointHoverRadius:7 }]
    }, options: prCommonOpts() });
  }

  var cm = document.getElementById('prReorgOnlyMonthlyChart');
  if (cm) {
    if (prReorgOnlyMonthlyChart) prReorgOnlyMonthlyChart.destroy();
    prReorgOnlyMonthlyChart = new Chart(cm.getContext('2d'), { type:'bar', data:{
      labels: months,
      datasets:[{ label:t('report.monthly_reorgs','Monthly Reorganizations'),
        data:data.monthlyReorgs.map(function(d){return d.count;}),
        backgroundColor:'rgba(124,58,237,0.7)', borderColor:'#7c3aed',
        borderWidth:1, borderRadius:3 }]
    }, options: prCommonOpts() });
  }
}

// ── District tables (all three tabs at once) ──────────────────────────────────
function renderAllDistrictTables(districts) {
  var noData = '<tr><td colspan="3" class="text-center px-4 py-2 text-gray-400">' +
               t('table.no_data','No data available') + '</td></tr>';

  // Combined
  var tb = document.getElementById('printDistrictTableBody');
  if (tb) {
    if (!districts || !districts.length) { tb.innerHTML = noData; }
    else {
      var tr = 0, trr = 0, html = '';
      districts.forEach(function(d) {
        tr += d.registrations; trr += d.reorgs;
        html += '<tr><td class="px-4 py-2 border border-gray-200">' + escapeHTMLPrint(d.district) + '</td>' +
                '<td class="text-right px-4 py-2 border border-gray-200">' + d.registrations + '</td>' +
                '<td class="text-right px-4 py-2 border border-gray-200">' + d.reorgs + '</td></tr>';
      });
      tb.innerHTML = html;
      var e1 = document.getElementById('prDistrictTotalReg');   if (e1) e1.textContent = tr;
      var e2 = document.getElementById('prDistrictTotalReorg'); if (e2) e2.textContent = trr;
    }
  }

  // Registrations-only
  var tbR = document.getElementById('prRegDistrictTableBody');
  if (tbR) {
    if (!districts || !districts.length) { tbR.innerHTML = noData; }
    else {
      var tr2 = 0, html2 = '';
      districts.forEach(function(d) {
        tr2 += d.registrations;
        html2 += '<tr><td class="px-4 py-2 border border-gray-200">' + escapeHTMLPrint(d.district) + '</td>' +
                 '<td class="text-right px-4 py-2 border border-gray-200">' + d.registrations + '</td></tr>';
      });
      tbR.innerHTML = html2;
      var e3 = document.getElementById('prRegDistrictTotal'); if (e3) e3.textContent = tr2;
    }
  }

  // Reorganizations-only
  var tbRO = document.getElementById('prReorgDistrictTableBody');
  if (tbRO) {
    if (!districts || !districts.length) { tbRO.innerHTML = noData; }
    else {
      var trr2 = 0, html3 = '';
      districts.forEach(function(d) {
        trr2 += d.reorgs;
        html3 += '<tr><td class="px-4 py-2 border border-gray-200">' + escapeHTMLPrint(d.district) + '</td>' +
                 '<td class="text-right px-4 py-2 border border-gray-200">' + d.reorgs + '</td></tr>';
      });
      tbRO.innerHTML = html3;
      var e4 = document.getElementById('prReorgDistrictTotal'); if (e4) e4.textContent = trr2;
    }
  }
}

// ── Tab switching ─────────────────────────────────────────────────────────────
function switchPrTab(tab) {
  prActiveTab = tab;
  ['Combined','Registrations','Reorganizations'].forEach(function(name) {
    var el = document.getElementById('prTab' + name);
    if (el) el.classList.add('hidden');
  });
  var cap = tab.charAt(0).toUpperCase() + tab.slice(1);
  var active = document.getElementById('prTab' + cap);
  if (active) active.classList.remove('hidden');
  updatePrTabButtons(tab);

  // Render charts for the newly visible tab (canvases are visible now)
  if (prCachedData) {
    if (tab === 'combined')        renderCombinedCharts(prCachedData);
    else if (tab === 'registrations')   renderRegOnlyCharts(prCachedData);
    else if (tab === 'reorganizations') renderReorgOnlyCharts(prCachedData);
  }
}

// ── Language switching inside the modal ───────────────────────────────────────
function switchPrLang(lang) {
  prReportLang = lang;
  updatePrLangButtons(lang);
  var basePath = (window.i18n && window.i18n.getBasePath) ? window.i18n.getBasePath() : '';
  fetch(basePath + '/assets/lang/' + lang + '.json')
    .then(function(r) { return r.json(); })
    .then(function(translations) {
      prModalTranslations = translations;
      applyPrTranslations();
      // Re-draw active tab charts with new month labels
      if (prCachedData) {
        if (prActiveTab === 'combined')        renderCombinedCharts(prCachedData);
        else if (prActiveTab === 'registrations')   renderRegOnlyCharts(prCachedData);
        else if (prActiveTab === 'reorganizations') renderReorgOnlyCharts(prCachedData);
      }
    })
    .catch(function(err) { console.error('Modal lang switch failed:', err); });
}

// ── Translate all data-i18n elements inside the modal ────────────────────────
function applyPrTranslations() {
  if (!prModalTranslations) return;
  var tr = prModalTranslations;
  document.querySelectorAll('#statReportModal [data-i18n]').forEach(function(el) {
    var key = el.getAttribute('data-i18n');
    if (tr[key]) el.textContent = tr[key];
  });
  // Restore monthly headings (they have year appended dynamically)
  if (prCachedData) updateMonthlyHeadings(prCachedData.currentYear);
  // Restore dates (generated-date text may be translated)
  stampPrDates();
}

// ── Stamp generated date in all tab headers ───────────────────────────────────
function stampPrDates() {
  var now    = new Date();
  var ds     = now.getFullYear() + '-' +
               String(now.getMonth() + 1).padStart(2, '0') + '-' +
               String(now.getDate()).padStart(2, '0');
  var label  = t('report.generated_date', 'Generated');
  ['prDateCombined','prDateRegistrations','prDateReorganizations'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.textContent = label + ': ' + ds;
  });
}

// ── Update monthly headings with year ─────────────────────────────────────────
function updateMonthlyHeadings(year) {
  ['statReportMonthlyHeading','prRegMonthlyHeading','prReorgMonthlyHeading'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.textContent = t('report.monthly_trends','Monthly Trends') + ' (' + year + ')';
  });
}

// ── Button styles ─────────────────────────────────────────────────────────────
function updatePrLangButtons(lang) {
  document.querySelectorAll('.pr-lang-btn').forEach(function(btn) {
    btn.classList.toggle('pr-active', btn.getAttribute('data-lang') === lang);
  });
}
function updatePrTabButtons(tab) {
  document.querySelectorAll('.pr-tab-btn').forEach(function(btn) {
    btn.classList.toggle('pr-active', btn.getAttribute('data-tab') === tab);
  });
}

// ── Minimal HTML-escape ───────────────────────────────────────────────────────
function escapeHTMLPrint(str) {
  return String(str)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

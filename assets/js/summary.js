let districtChart = null;
let statusChart = null;
let registrationChart = null;
let realtimeFallbackTimer = null;

document.addEventListener("DOMContentLoaded", function () {
  loadSummaryData();
  startRealtimeSummary();
});

function loadSummaryData() {
  return fetch("../api/summary.php")
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

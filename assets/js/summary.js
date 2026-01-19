document.addEventListener("DOMContentLoaded", function () {
  loadSummaryData();
});

function loadSummaryData() {
  fetch("../api/summary.php")
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        updateStats(data.data);
        createDistrictChart(data.data.byDistrict);
        createStatusChart(data.data.byStatus);
        createRegistrationChart(data.data.registrationTrend);
      }
    })
    .catch((err) => console.error(err));
}

function updateStats(data) {
  document.getElementById("totalClubs").textContent = data.total || 0;
  document.getElementById("activeClubs").textContent = data.active || 0;
  document.getElementById("expiredClubs").textContent = data.expired || 0;
  document.getElementById("totalReorgs").textContent = data.totalReorgs || 0;
}

function createDistrictChart(data) {
  const ctx = document.getElementById("districtChart").getContext("2d");
  new Chart(ctx, {
    type: "bar",
    data: {
      labels: data.map((d) => d.district),
      datasets: [
        {
          label: window.i18n ? window.i18n.t("chart.clubs") : "Clubs",
          data: data.map((d) => d.count),
          backgroundColor: ["#3b82f6", "#10b981", "#f59e0b"],
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

function createStatusChart(data) {
  const ctx = document.getElementById("statusChart").getContext("2d");
  new Chart(ctx, {
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

function createRegistrationChart(data) {
  const ctx = document.getElementById("registrationChart").getContext("2d");
  new Chart(ctx, {
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

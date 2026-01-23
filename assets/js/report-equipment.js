document.addEventListener("DOMContentLoaded", function () {
  loadDistricts();
  loadEquipmentTypes();

  // Real-time report generation
  document
    .getElementById("equipment")
    .addEventListener("change", generateReport);
  document.getElementById("district").addEventListener("change", function () {
    loadDivisions();
    generateReport();
  });
  document.getElementById("division")?.addEventListener("change", function () {
    loadGNDivisions();
    generateReport();
  });
  document
    .getElementById("gnDivision")
    ?.addEventListener("change", generateReport);
});

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

function loadGNDivisions() {
  const select = document.getElementById("gnDivision");
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

  fetch(`../api/locations.php?type=gn_division&parent_id=${divisionId}&search=`)
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

function generateReport() {
  const equipment = document.getElementById("equipment").value;
  const district = document.getElementById("district").value;
  const division = document.getElementById("division")?.value || "";
  const gnDivision = document.getElementById("gnDivision")?.value || "";

  let url = `../api/reports.php?type=equipment&equipment=${equipment}&district=${district}`;
  if (division) url += `&division=${encodeURIComponent(division)}`;
  if (gnDivision) url += `&gn_division=${encodeURIComponent(gnDivision)}`;

  fetch(url)
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        displayReport(data.data, equipment, district, division, gnDivision);
      }
    });
}

function displayReport(
  data,
  equipment,
  district,
  division = "",
  gnDivision = "",
) {
  const output = document.getElementById("reportOutput");
  const equipmentText = equipment || "All Equipment";
  const districtText = district || "All Districts";
  const divisionText = division ? ` | Division: ${division}` : "";
  const gnDivisionText = gnDivision ? ` | GN Division: ${gnDivision}` : "";

  output.innerHTML = `
        <div class="print-header" style="display: none;">
            <div class="dept-name" data-i18n="header.department_name">Southern Province Sports Department</div>
            <h1 data-i18n="report.type_equipment">Equipment Report</h1>
            <div class="text-sm">Equipment: ${equipmentText} | District: ${districtText}${divisionText}${gnDivisionText}</div>
        </div>

        <div class="text-center mb-6 no-print">
            <h2 class="text-2xl font-bold">Southern Province Sports Department</h2>
            <h3 class="text-xl mt-2">Equipment Report</h3>
            <p class="text-sm text-gray-600 mt-2">Equipment: ${equipmentText} | District: ${districtText}${divisionText}${gnDivisionText}</p>
            <p class="text-sm text-gray-600">Generated: ${new Date().toLocaleDateString('si-LK')}</p>
        </div>
        <table class="min-w-full border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2">#</th>
                    <th class="border border-gray-300 px-4 py-2">Registration No</th>
                    <th class="border border-gray-300 px-4 py-2">Club Name</th>
                    <th class="border border-gray-300 px-4 py-2">District</th>
                    <th class="border border-gray-300 px-4 py-2">Division</th>
                    <th class="border border-gray-300 px-4 py-2">GN Division</th>
                    <th class="border border-gray-300 px-4 py-2">Equipment</th>
                    <th class="border border-gray-300 px-4 py-2">Quantity</th>
                </tr>
            </thead>
            <tbody>
                ${data
                  .map(
                    (row, i) => `
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">${i + 1}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.reg_number}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.name}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.district || "-"}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.division || "-"}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.gn_division || "-"}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.equipment}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.quantity}</td>
                    </tr>
                `,
                  )
                  .join("")}
            </tbody>
        </table>
        <div class="mt-6 text-sm text-gray-600">
            <p>Total Records: ${data.length}</p>
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

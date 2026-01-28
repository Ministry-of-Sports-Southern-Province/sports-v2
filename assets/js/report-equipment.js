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

// Print function with date
function printReportWithDate() {
  const originalTitle = document.title;
  const now = new Date();
  const dateStr = now.getFullYear() + '-' + 
                String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                String(now.getDate()).padStart(2, '0');
  
  const equipment = document.getElementById("equipment")?.value;
  const district = document.getElementById("district")?.value;
  const division = document.getElementById("division")?.value;
  
  let filterInfo = '';
  if (equipment) {
    filterInfo = '_' + equipment.replace(/\s+/g, '_');
  }
  if (district) {
    filterInfo += '_' + district.replace(/\s+/g, '_');
  }
  if (division) {
    filterInfo += '_' + division.replace(/\s+/g, '_');
  }
  
  document.title = 'Equipment_Report_' + dateStr + filterInfo;
  window.print();
  
  setTimeout(() => {
    document.title = originalTitle;
  }, 1000);
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

  // Calculate total
  const totalQuantity = data.reduce((sum, row) => sum + parseInt(row.quantity || 0), 0);

  // Sort data by district, division, gn_division for proper grouping
  const sortedData = [...data].sort((a, b) => {
    if (a.district !== b.district) return (a.district || '').localeCompare(b.district || '');
    if (a.division !== b.division) return (a.division || '').localeCompare(b.division || '');
    if (a.gn_division !== b.gn_division) return (a.gn_division || '').localeCompare(b.gn_division || '');
    return 0;
  });

  // Assign colors to groups - cycling through color palette
  const colors = [
    '#dbeafe',  // light blue
    '#fef3c7',  // light yellow
    '#d1fae5',  // light green
    '#fce7f3',  // light pink
    '#e0e7ff',  // light indigo
    '#fed7aa',  // light orange
    '#f3e8ff',  // light purple
    '#fecaca',  // light red
  ];

  let tableRows = '';
  let currentGnDivisionKey = '';
  let colorIndex = 0;
  let currentColor = '';

  sortedData.forEach((row, i) => {
    const gnDivisionKey = `${row.district}|${row.division}|${row.gn_division}`;
    
    // Change color when GN Division changes
    if (gnDivisionKey !== currentGnDivisionKey) {
      currentGnDivisionKey = gnDivisionKey;
      currentColor = colors[colorIndex % colors.length];
      colorIndex++;
    }
    
    tableRows += `
      <tr style="background-color: ${currentColor} !important;">
        <td class="border border-gray-300 px-4 py-2">${i + 1}</td>
        <td class="border border-gray-300 px-4 py-2">${row.reg_number}</td>
        <td class="border border-gray-300 px-4 py-2">${row.name}</td>
        <td class="border border-gray-300 px-4 py-2">${row.district || "-"}</td>
        <td class="border border-gray-300 px-4 py-2">${row.division || "-"}</td>
        <td class="border border-gray-300 px-4 py-2">${row.gn_division || "-"}</td>
        <td class="border border-gray-300 px-4 py-2">${row.equipment}</td>
        <td class="border border-gray-300 px-4 py-2" style="text-align: right;">${row.quantity}</td>
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
                
                /* Table Compact Styling */
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    font-size: 7pt; 
                    margin-top: 8px; 
                    line-height: 1.2;
                }
                table th { 
                    background-color: #1e3a8a !important; 
                    color: white !important; 
                    font-weight: bold; 
                    font-size: 7pt;
                    padding: 3px; 
                    border: 1px solid #ccc; 
                    text-align: left; 
                    line-height: 1.1;
                    -webkit-print-color-adjust: exact; 
                    print-color-adjust: exact;
                }
                table td { 
                    padding: 2px 3px; 
                    border: 1px solid #ccc; 
                    font-size: 7pt; 
                    color: #333; 
                    line-height: 1.2; 
                    vertical-align: top; 
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
            <div class="dept-name" data-i18n="header.department_name">Southern Province Sports Department</div>
            <h1 data-i18n="report.type_equipment">Equipment Report</h1>
            <div class="text-sm">Equipment: ${equipmentText} | District: ${districtText}${divisionText}${gnDivisionText}</div>
        </div>

        <div class="text-center mb-6 no-print">
            <h2 class="text-2xl font-bold">Southern Province Sports Department</h2>
            <h3 class="text-xl mt-2">Equipment Report</h3>
            <p class="text-sm text-gray-600 mt-2">Equipment: ${equipmentText} | District: ${districtText}${divisionText}${gnDivisionText}</p>
            <p class="text-sm text-gray-600">Generated: ${new Date().toLocaleDateString('en-US')}</p>
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
                    <th class="border border-gray-300 px-4 py-2" style="text-align: right;">Quantity</th>
                </tr>
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
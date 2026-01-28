document.addEventListener('DOMContentLoaded', function() {
    loadDistricts();
    
    // Real-time report generation
    document.getElementById('district').addEventListener('change', generateReport);
    document.getElementById('dateRange').addEventListener('change', generateReport);
    
    // Generate initial report
    generateReport();
});

function loadDistricts() {
    fetch('../api/locations.php?type=district')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('district');
                data.data.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.name;
                    opt.textContent = d.name;
                    select.appendChild(opt);
                });
            }
        });
}

function generateReport() {
    const district = document.getElementById('district').value;
    const dateRange = document.getElementById('dateRange').value;

    fetch(`../api/reports.php?type=registered&district=${district}&date_range=${dateRange}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayReport(data.data, district, dateRange);
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
    
    const district = document.getElementById('district')?.value;
    const dateRange = document.getElementById('dateRange')?.value;
    
    let filterInfo = '';
    if (district) {
        filterInfo = '_' + district.replace(/\s+/g, '_');
    }
    if (dateRange) {
        const rangeText = dateRange === 'year' ? 'Year' : dateRange === 'month' ? 'Month' : 'All';
        filterInfo += '_' + rangeText;
    }
    
    document.title = 'Registered_Clubs_Report_' + dateStr + filterInfo;
    window.print();
    
    setTimeout(() => {
        document.title = originalTitle;
    }, 1000);
}

function displayReport(data, district, dateRange) {
    const output = document.getElementById('reportOutput');
    const districtText = district || 'සියලු දිස්ත්රික්ක';
    const rangeText = dateRange === 'year' ? 'මෙම වර්ෂය' : dateRange === 'month' ? 'මෙම මාසය' : 'සියලු කාලය';
    
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
            <div class="dept-name" data-i18n="header.department_name">Southern Province Sports Department</div>
            <h1 data-i18n="report.type_registered">ලියාපදිංචි සමාජ වාර්තාව</h1>
            <div class="text-sm">දිස්ත්රික්කය: ${districtText} | කාල පරාසය: ${rangeText}</div>
        </div>

        <div class="text-center mb-6 no-print">
            <h2 class="text-2xl font-bold">දකුණු පළාත් ක්රීඩා අමාත්යාංශය</h2>
            <h3 class="text-xl mt-2">ලියාපදිංචි සමාජ වාර්තාව</h3>
            <p class="text-sm text-gray-600 mt-2">දිස්ත්රික්කය: ${districtText} | කාල පරාසය: ${rangeText}</p>
            <p class="text-sm text-gray-600">උත්පාදන දිනය: ${new Date().toLocaleDateString('si-LK')}</p>
        </div>
        <table class="min-w-full border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2">#</th>
                    <th class="border border-gray-300 px-4 py-2">ලියාපදිංචි අංකය</th>
                    <th class="border border-gray-300 px-4 py-2">සමාජ නාමය</th>
                    <th class="border border-gray-300 px-4 py-2">දිස්ත්රික්කය</th>
                    <th class="border border-gray-300 px-4 py-2">සභාපති</th>
                    <th class="border border-gray-300 px-4 py-2">දුරකථනය</th>
                    <th class="border border-gray-300 px-4 py-2">ලියාපදිංචි දිනය</th>
                </tr>
            </thead>
            <tbody>
                ${data.map((row, i) => `
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">${i + 1}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.reg_number}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.name}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.district || '-'}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.chairman}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.chairman_phone}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.registration_date}</td>
                    </tr>
                `).join('')}
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
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

function displayReport(data, district, dateRange) {
    const output = document.getElementById('reportOutput');
    const districtText = district || 'සියලු දිස්ත්රික්ක';
    const rangeText = dateRange === 'year' ? 'මෙම වර්ෂය' : dateRange === 'month' ? 'මෙම මාසය' : 'සියලු කාලය';
    
    output.innerHTML = `
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

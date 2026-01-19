document.addEventListener('DOMContentLoaded', function() {
    loadDistricts();
    populateYears();
    
    // Real-time report generation
    document.getElementById('year').addEventListener('change', generateReport);
    document.getElementById('district').addEventListener('change', generateReport);
});

function populateYears() {
    const select = document.getElementById('year');
    const currentYear = new Date().getFullYear();
    for (let year = currentYear; year >= currentYear - 10; year--) {
        const opt = document.createElement('option');
        opt.value = year;
        opt.textContent = year;
        select.appendChild(opt);
    }
    generateReport();
}

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
    const year = document.getElementById('year').value;
    const district = document.getElementById('district').value;

    fetch(`../api/reports.php?type=reorganized&year=${year}&district=${district}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayReport(data.data, year, district);
            }
        });
}

function displayReport(data, year, district) {
    const output = document.getElementById('reportOutput');
    const districtText = district || 'සියලු දිස්ත්රික්ක';
    
    output.innerHTML = `
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold">දකුණු පළාත් ක්රීඩා අමාත්යාංශය</h2>
            <h3 class="text-xl mt-2">ප්රතිසංවිධාන සමාජ වාර්තාව - ${year}</h3>
            <p class="text-sm text-gray-600 mt-2">දිස්ත්රික්කය: ${districtText}</p>
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
                    <th class="border border-gray-300 px-4 py-2">ප්රතිසංවිධාන දිනය</th>
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
                        <td class="border border-gray-300 px-4 py-2">${row.reorg_date}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
        <div class="mt-6 text-sm text-gray-600">
            <p>මුළු වාර්තා ගණන: ${data.length}</p>
        </div>
    `;
}

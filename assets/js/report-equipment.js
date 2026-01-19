document.addEventListener('DOMContentLoaded', function() {
    loadDistricts();
    loadEquipmentTypes();
    
    // Real-time report generation
    document.getElementById('equipment').addEventListener('change', generateReport);
    document.getElementById('district').addEventListener('change', generateReport);
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

function loadEquipmentTypes() {
    fetch('../api/equipment-types.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('equipment');
                data.data.forEach(e => {
                    const opt = document.createElement('option');
                    opt.value = e.name;
                    opt.textContent = e.name;
                    select.appendChild(opt);
                });
                generateReport();
            }
        });
}

function generateReport() {
    const equipment = document.getElementById('equipment').value;
    const district = document.getElementById('district').value;

    fetch(`../api/reports.php?type=equipment&equipment=${equipment}&district=${district}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayReport(data.data, equipment, district);
            }
        });
}

function displayReport(data, equipment, district) {
    const output = document.getElementById('reportOutput');
    const equipmentText = equipment || 'සියලු උපකරණ';
    const districtText = district || 'සියලු දිස්ත්රික්ක';
    
    output.innerHTML = `
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold">දකුණු පළාත් ක්රීඩා අමාත්යාංශය</h2>
            <h3 class="text-xl mt-2">උපකරණ ලැයිස්තුව වාර්තාව</h3>
            <p class="text-sm text-gray-600 mt-2">උපකරණය: ${equipmentText} | දිස්ත්රික්කය: ${districtText}</p>
            <p class="text-sm text-gray-600">උත්පාදන දිනය: ${new Date().toLocaleDateString('si-LK')}</p>
        </div>
        <table class="min-w-full border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2">#</th>
                    <th class="border border-gray-300 px-4 py-2">ලියාපදිංචි අංකය</th>
                    <th class="border border-gray-300 px-4 py-2">සමාජ නාමය</th>
                    <th class="border border-gray-300 px-4 py-2">දිස්ත්රික්කය</th>
                    <th class="border border-gray-300 px-4 py-2">උපකරණය</th>
                    <th class="border border-gray-300 px-4 py-2">ප්රමාණය</th>
                </tr>
            </thead>
            <tbody>
                ${data.map((row, i) => `
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">${i + 1}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.reg_number}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.name}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.district || '-'}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.equipment}</td>
                        <td class="border border-gray-300 px-4 py-2">${row.quantity}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
        <div class="mt-6 text-sm text-gray-600">
            <p>මුළු වාර්තා ගණන: ${data.length}</p>
        </div>
    `;
}

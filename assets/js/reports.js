document.addEventListener('DOMContentLoaded', function() {
    loadDistricts();
    loadEquipmentTypes();
    populateYears();
});

function populateYears() {
    const select = document.getElementById('reorgYear');
    const currentYear = new Date().getFullYear();
    for (let year = currentYear; year >= currentYear - 10; year--) {
        const opt = document.createElement('option');
        opt.value = year;
        opt.textContent = year;
        select.appendChild(opt);
    }
}

function loadDistricts() {
    fetch('../api/locations.php?type=districts')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                [document.getElementById('reorgDistrict'), document.getElementById('regDistrict')].forEach(select => {
                    data.data.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.name;
                        opt.textContent = d.name;
                        select.appendChild(opt);
                    });
                });
            }
        });
}

function loadEquipmentTypes() {
    fetch('../api/equipment-types.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('equipmentType');
                data.data.forEach(e => {
                    const opt = document.createElement('option');
                    opt.value = e.name;
                    opt.textContent = e.name;
                    select.appendChild(opt);
                });
            }
        });
}

function updateReportOptions() {
    const type = document.getElementById('reportType').value;
    document.getElementById('reorganizedOptions').classList.toggle('hidden', type !== 'reorganized');
    document.getElementById('registeredOptions').classList.toggle('hidden', type !== 'registered');
    document.getElementById('equipmentOptions').classList.toggle('hidden', type !== 'equipment');
}

function generateReport() {
    const type = document.getElementById('reportType').value;
    const columns = Array.from(document.querySelectorAll('input[type="checkbox"]:checked')).map(cb => cb.value);

    let params = { type, columns: columns.join(',') };

    if (type === 'reorganized') {
        params.year = document.getElementById('reorgYear').value;
        params.district = document.getElementById('reorgDistrict').value;
    } else if (type === 'registered') {
        params.district = document.getElementById('regDistrict').value;
        params.date_range = document.getElementById('dateRange').value;
    } else if (type === 'equipment') {
        params.equipment = document.getElementById('equipmentType').value;
    }

    const queryString = new URLSearchParams(params).toString();
    fetch(`../api/reports.php?${queryString}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayReport(data.data, columns, type);
            }
        })
        .catch(err => console.error(err));
}

function displayReport(data, columns, type) {
    const output = document.getElementById('reportOutput');
    
    const title = type === 'reorganized' ? 'ප්රතිසංවිධාන සමාජ වාර්තාව' : 
                  type === 'registered' ? 'ලියාපදිංචි සමාජ වාර්තාව' : 
                  'උපකරණ වාර්තාව';

    const headers = {
        reg_number: 'ලියාපදිංචි අංකය',
        name: 'සමාජ නාමය',
        district: 'දිස්ත්රික්කය',
        division: 'කොට්ඨාසය',
        chairman: 'සභාපති',
        chairman_phone: 'දුරකථනය',
        secretary: 'ලේකම්',
        registration_date: 'ලියාපදිංචි දිනය',
        reorg_date: 'ප්රතිසංවිධාන දිනය',
        equipment: 'උපකරණ',
        quantity: 'ප්රමාණය'
    };

    output.innerHTML = `
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold">දකුණු පළාත් ක්රීඩා අමාත්යාංශය</h2>
            <h3 class="text-xl mt-2">${title}</h3>
            <p class="text-sm text-gray-600 mt-2">උත්පාදන දිනය: ${new Date().toLocaleDateString('si-LK')}</p>
        </div>
        <table class="min-w-full border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2">#</th>
                    ${columns.map(col => `<th class="border border-gray-300 px-4 py-2">${headers[col] || col}</th>`).join('')}
                </tr>
            </thead>
            <tbody>
                ${data.map((row, i) => `
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">${i + 1}</td>
                        ${columns.map(col => `<td class="border border-gray-300 px-4 py-2">${row[col] || '-'}</td>`).join('')}
                    </tr>
                `).join('')}
            </tbody>
        </table>
        <div class="mt-6 text-sm text-gray-600">
            <p>මුළු වාර්තා ගණන: ${data.length}</p>
        </div>
    `;
}

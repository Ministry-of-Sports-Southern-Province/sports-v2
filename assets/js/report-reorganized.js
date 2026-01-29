document.addEventListener('DOMContentLoaded', function() {
    loadDistricts();
    populateYears();
    
    // Real-time report generation
    document.getElementById('year').addEventListener('change', () => generateReport(1));
    document.getElementById('district').addEventListener('change', () => generateReport(1));
});

let currentPage = 1;
let rowsPerPage = 10;
let totalPages = 1;
let totalRows = 0;

function populateYears() {
    const select = document.getElementById('year');
    const currentYear = new Date().getFullYear();
    for (let year = currentYear; year >= currentYear - 10; year--) {
        const opt = document.createElement('option');
        opt.value = year;
        opt.textContent = year;
        select.appendChild(opt);
    }
    generateReport(1);
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

function buildReportQuery({ page = 1, limit = rowsPerPage, printAll = false } = {}) {
    const year = document.getElementById('year').value;
    const district = document.getElementById('district').value;

    const params = new URLSearchParams();
    params.append('type', 'reorganized');
    params.append('year', year);
    params.append('district', district);
    if (printAll) {
        params.append('print_all', '1');
    } else {
        params.append('page', String(page));
        params.append('limit', String(limit));
    }
    return `../api/reports.php?${params.toString()}`;
}

function generateReport(page = 1) {
    currentPage = page;
    const year = document.getElementById('year').value;
    const district = document.getElementById('district').value;

    fetch(buildReportQuery({ page: currentPage, limit: rowsPerPage }))
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayReport(data.data, year, district);
                renderPagination(data.pagination);
            }
        });
}

function printReportWithDate() {
    const originalTitle = document.title;
    const now = new Date();
    const dateStr = now.getFullYear() + '-' +
        String(now.getMonth() + 1).padStart(2, '0') + '-' +
        String(now.getDate()).padStart(2, '0');

    const yearVal = document.getElementById('year')?.value;
    const districtVal = document.getElementById('district')?.value;
    let filterInfo = '';
    if (yearVal) filterInfo += '_' + String(yearVal);
    if (districtVal) filterInfo += '_' + districtVal.replace(/\s+/g, '_');

    document.title = 'Reorganized_Clubs_Report_' + dateStr + filterInfo;

    const year = document.getElementById('year').value;
    const district = document.getElementById('district').value;

    fetch(buildReportQuery({ printAll: true }))
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayReport(data.data, year, district);
                window.print();
                setTimeout(() => {
                    document.title = originalTitle;
                    generateReport(currentPage);
                }, 750);
            } else {
                window.print();
                setTimeout(() => {
                    document.title = originalTitle;
                }, 750);
            }
        })
        .catch(() => {
            window.print();
            setTimeout(() => {
                document.title = originalTitle;
            }, 750);
        });
}

function renderPagination(pagination) {
    const container = document.getElementById('reportPagination');
    const infoEl = document.getElementById('reportPaginationInfo');
    if (!container) return;

    if (!pagination) {
        container.innerHTML = '';
        if (infoEl) infoEl.textContent = '';
        return;
    }

    totalPages = pagination.total_pages || 1;
    totalRows = pagination.total || 0;
    container.innerHTML = '';

    const prevBtn = document.createElement('button');
    prevBtn.textContent = 'Prev';
    prevBtn.disabled = currentPage === 1;
    prevBtn.className =
        'px-3 py-1 border rounded ' +
        (prevBtn.disabled ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-50');
    prevBtn.onclick = () => generateReport(currentPage - 1);
    container.appendChild(prevBtn);

    const windowSize = 3;
    const start = Math.max(1, currentPage - windowSize);
    const end = Math.min(totalPages, currentPage + windowSize);

    if (start > 1) {
        const firstBtn = document.createElement('button');
        firstBtn.textContent = '1';
        firstBtn.className = 'px-3 py-1 border rounded bg-white hover:bg-gray-50';
        firstBtn.onclick = () => generateReport(1);
        container.appendChild(firstBtn);
        if (start > 2) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.className = 'px-2 text-gray-500';
            container.appendChild(dots);
        }
    }

    for (let i = start; i <= end; i++) {
        const btn = document.createElement('button');
        btn.textContent = String(i);
        btn.className =
            'px-3 py-1 border rounded ' + (i === currentPage ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50');
        btn.onclick = () => generateReport(i);
        container.appendChild(btn);
    }

    if (end < totalPages) {
        if (end < totalPages - 1) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.className = 'px-2 text-gray-500';
            container.appendChild(dots);
        }
        const lastBtn = document.createElement('button');
        lastBtn.textContent = String(totalPages);
        lastBtn.className = 'px-3 py-1 border rounded bg-white hover:bg-gray-50';
        lastBtn.onclick = () => generateReport(totalPages);
        container.appendChild(lastBtn);
    }

    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Next';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.className =
        'px-3 py-1 border rounded ' +
        (nextBtn.disabled ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-50');
    nextBtn.onclick = () => generateReport(currentPage + 1);
    container.appendChild(nextBtn);

    if (infoEl) {
        const startRow = totalRows === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
        const endRow = Math.min(currentPage * rowsPerPage, totalRows);
        infoEl.textContent = `Showing ${startRow}–${endRow} of ${totalRows} (Page ${currentPage} of ${totalPages})`;
    }
}

function displayReport(data, year, district) {
    const output = document.getElementById('reportOutput');
    const districtText = district || 'සියලු දිස්ත්රික්ක';
    
    output.innerHTML = `
        <div class="print-header" style="display: none;">
            <div class="dept-name" data-i18n="header.department_name">Southern Province Sports Department</div>
            <h1 data-i18n="report.type_reorganized">ප්රතිසංවිධාන සමාජ වාර්තාව</h1>
            <div class="text-sm">වර්ෂය: ${year} | දිස්ත්රික්කය: ${districtText}</div>
        </div>

        <div class="text-center mb-6 no-print">
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

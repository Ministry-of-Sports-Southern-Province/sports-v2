let allClubs = [];

document.addEventListener('DOMContentLoaded', function() {
    loadDistricts();
    loadClubs();
    
    // Real-time search
    document.getElementById('searchInput').addEventListener('input', filterClubs);
    document.getElementById('districtFilter').addEventListener('change', filterClubs);
    document.getElementById('statusFilter').addEventListener('change', filterClubs);
});

function loadDistricts() {
    fetch('../api/locations.php?type=district')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('districtFilter');
                data.data.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.name;
                    opt.textContent = d.name;
                    select.appendChild(opt);
                });
            }
        });
}

function loadClubs() {
    fetch('../api/clubs-list.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                allClubs = data.data;
                displayClubs(allClubs);
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('clubsTable').innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-red-600">දෝෂයකි</td></tr>';
        });
}

function displayClubs(clubs) {
    const tbody = document.getElementById('clubsTable');
    if (clubs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center">දත්ත නොමැත</td></tr>';
        return;
    }

    tbody.innerHTML = clubs.map((club, i) => {
        const status = club.reorg_status || 'expired';
        const statusClass = status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
        
        return `
            <tr>
                <td class="px-6 py-4">${i + 1}</td>
                <td class="px-6 py-4">${club.reg_number}</td>
                <td class="px-6 py-4">${club.name}</td>
                <td class="px-6 py-4">${club.district_name || '-'}</td>
                <td class="px-6 py-4">${club.last_reorg_date || 'N/A'}</td>
                <td class="px-6 py-4"><span class="px-2 py-1 rounded text-sm ${statusClass}" data-i18n="status.${status}">${status === 'active' ? 'සක්රීය' : 'කල් ඉකුත්'}</span></td>
                <td class="px-6 py-4">
                    <button onclick="viewHistory(${club.id})" class="text-blue-600 hover:text-blue-800 mr-2" data-i18n="button.view_history">ඉතිහාසය</button>
                    <button onclick="renewClub(${club.id})" class="text-green-600 hover:text-green-800 mr-2" data-i18n="button.renew">නවීකරණය</button>
                    <button onclick="deleteReorg(${club.id})" class="text-red-600 hover:text-red-800" data-i18n="button.delete">මකන්න</button>
                </td>
            </tr>
        `;
    }).join('');
    if (window.i18n && window.i18n.applyTranslations) {
        window.i18n.applyTranslations();
    }
}

function filterClubs() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const district = document.getElementById('districtFilter').value;
    const status = document.getElementById('statusFilter').value;

    const filtered = allClubs.filter(club => {
        const matchSearch = !search || club.name.toLowerCase().includes(search) || club.reg_number.toLowerCase().includes(search);
        const matchDistrict = !district || club.district_name === district;
        const matchStatus = !status || club.reorg_status === status;
        return matchSearch && matchDistrict && matchStatus;
    });

    displayClubs(filtered);
}

function viewHistory(clubId) {
    window.location.href = `club-details.php?id=${clubId}`;
}

function renewClub(clubId) {
    if (!confirm('මෙම සමාජය ප්රතිසංවිධාන කිරීමට අවශ්‍යද?')) return;

    fetch('../api/reorganizations.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ club_id: clubId, reorg_date: new Date().toISOString().split('T')[0] })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('සමාජය සාර්තකව ප්රතිසංවිධාන කරන ලදී');
            loadClubs();
        } else {
            alert(data.message || 'දොෂයකි');
        }
    })
    .catch(err => {
        console.error(err);
        alert('දොෂයකි');
    });
}

function deleteReorg(clubId) {
    if (!confirm('අවසාන ප්රතිසංවිධාන දිනය ඉවත් කිරීමට අවශ්‍යද?')) return;

    fetch('../api/reorganizations.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ club_id: clubId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('ප්රතිසංවිධාන දිනය ඉවත් කරන ලදී');
            loadClubs();
        } else {
            alert(data.message || 'දොෂයකි');
        }
    })
    .catch(err => {
        console.error(err);
        alert('දොෂයකි');
    });
}

/**
 * Club Details JavaScript
 */

document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const clubId = urlParams.get("id");

  if (!clubId) {
    showError("Club ID not provided");
    return;
  }

  loadClubDetails(clubId);
});

function loadClubDetails(clubId) {
  fetch(`../api/club-details.php?id=${clubId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayClubDetails(data.data);
      } else {
        showError(data.message || "Failed to load club details");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showError("An error occurred while loading club details");
    });
}

function displayClubDetails(club) {
  const container = document.getElementById("clubDetails");

  container.innerHTML = `
    <div class="print-header" style="display: none;">
      <h1>දකුණු පළාත් ක්රීඩා අමාත්යාංශය</h1>
      <div class="subtitle">Southern Province Sports Department</div>
      <div class="subtitle" style="font-weight: bold; margin-top: 3mm;">ක්රීඩා සමාජ ලියාපදිංචි ප්රමාණ පත්රය</div>
    </div>
    
    <style>
      @media print {
        .print-header { display: block !important; }
      }
    </style>

    <div class="detail-card">
      <h2 data-i18n="form.club_information">සමාජ තොරතුරු</h2>
      <div class="info-row">
        <div><label data-i18n="form.reg_number">ලියාපදිංචි අංකය</label><p>${escapeHtml(
          club.reg_number
        )}</p></div>
        <div><label data-i18n="form.registration_date">ලියාපදිංචි දිනය</label><p>${formatDate(
          club.registration_date
        )}</p></div>
      </div>
      <div class="info-row">
        <div class="full-width"><label data-i18n="form.club_name">සමාජයේ නම</label><p>${escapeHtml(
          club.name
        )}</p></div>
      </div>
    </div>

    <div class="detail-card">
      <h2 data-i18n="form.location_information">ස්ථාන තොරතුරු</h2>
      <div class="info-row">
        <div><label data-i18n="form.district">දිස්ත්රික්කය</label><p>${escapeHtml(
          club.district_name || "-"
        )}</p></div>
        <div><label data-i18n="form.division">ප්රාදේශීය ලේකම් කොට්ඨාසය</label><p>${escapeHtml(
          club.division_name || "-"
        )}</p></div>
      </div>
      <div class="info-row">
        <div class="full-width"><label data-i18n="form.gn_division">ග්රාම නිලධාරී කොට්ඨාසය</label><p>${escapeHtml(
          club.gn_division_name || "-"
        )}</p></div>
      </div>
    </div>

    <div class="detail-card">
      <h2 data-i18n="form.chairman_information">සභාපති තොරතුරු</h2>
      <div class="info-row">
        <div><label data-i18n="form.chairman_name">සභාපතිගේ නම</label><p>${escapeHtml(
          club.chairman_name
        )}</p></div>
        <div><label data-i18n="form.chairman_phone">සභාපතිගේ දුරකථන අංකය</label><p>${escapeHtml(
          club.chairman_phone
        )}</p></div>
      </div>
      <div class="info-row">
        <div class="full-width"><label data-i18n="form.chairman_address">සභාපතිගේ ලිපිනය</label><p>${escapeHtml(
          club.chairman_address
        )}</p></div>
      </div>
    </div>

    <div class="detail-card">
      <h2 data-i18n="form.secretary_information">ලේකම් තොරතුරු</h2>
      <div class="info-row">
        <div><label data-i18n="form.secretary_name">ලේකම්ගේ නම</label><p>${escapeHtml(
          club.secretary_name
        )}</p></div>
        <div><label data-i18n="form.secretary_phone">ලේකම්ගේ දුරකථන අංකය</label><p>${escapeHtml(
          club.secretary_phone
        )}</p></div>
      </div>
      <div class="info-row">
        <div class="full-width"><label data-i18n="form.secretary_address">ලේකම්ගේ ලිපිනය</label><p>${escapeHtml(
          club.secretary_address
        )}</p></div>
      </div>
    </div>

    ${
      club.equipment && club.equipment.length > 0
        ? `
    <div class="detail-card">
      <h2 data-i18n="form.equipment_information">ක්රීඩා උපකරණ</h2>
      <table>
        <thead>
          <tr>
            <th data-i18n="table.equipment">උපකරණ</th>
            <th data-i18n="table.quantity">ප්රමාණය</th>
          </tr>
        </thead>
        <tbody>
          ${club.equipment
            .map(
              (eq) => `
            <tr>
              <td>${escapeHtml(eq.name)}</td>
              <td>${eq.quantity}</td>
            </tr>
          `
            )
            .join("")}
        </tbody>
      </table>
    </div>
    `
        : ""
    }
    
    <div class="detail-card">
      <h2 data-i18n="form.reorganization_information">ප්රතිසංවිධාන තොරතුරු</h2>
      <div class="info-row">
        <div><label data-i18n="form.last_reorg_date">අවසාන ප්රතිසංවිධාන දිනය</label><p>${club.last_reorg_date ? formatDate(club.last_reorg_date) : 'N/A'}</p></div>
        <div><label data-i18n="form.reorg_due_date">මීළඟ ප්රතිසංවිධාන දිනය</label><p>${club.reorg_due_date ? formatDate(club.reorg_due_date) : 'N/A'}</p></div>
      </div>
      <div class="info-row">
        <div><label data-i18n="form.reorg_status">තත්ත්වය</label><p><span class="px-2 py-1 rounded text-sm ${club.reorg_status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}" data-i18n="status.${club.reorg_status}">${club.reorg_status === 'active' ? 'සක්රීය' : 'කල් ඉකුත්'}</span></p></div>
      </div>
      <div class="no-print mt-4">
        <button onclick="openReorgModal(${club.id})" class="px-4 py-2 ${club.reorg_status === 'expired' ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'} text-white rounded transition">
          <span data-i18n="button.add_reorg">ප්රතිසංවිධාන කරන්න</span>
        </button>
        ${club.last_reorg_date ? `<button onclick="deleteReorg(${club.id})" class="ml-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded transition"><span data-i18n="button.delete_reorg">අවසාන දිනය ඉවත් කරන්න</span></button>` : ''}
        <button onclick="viewReorgHistory(${club.id})" class="ml-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded transition">
          <span data-i18n="button.view_history">ඉතිහාසය බලන්න</span>
        </button>
      </div>
    </div>
    
    <div class="print-footer" style="display: none;">
      <p>මෙම ප්රමාණ පත්රය දකුණු පළාත් ක්රීඩා අමාත්යාංශය විසින් නිකුත් කරන ලදී</p>
    </div>
    
    <div id="reorgModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4" data-i18n="modal.add_reorg_title">ප්රතිසංවිධාන දිනය එකතු කරන්න</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-2">
            <input type="radio" name="dateOption" value="auto" checked onchange="toggleDateInput()"> 
            <span data-i18n="form.date_option_auto">වත්මන දිනය භාවිත කරන්න</span>
          </label>
          <label class="block text-sm font-medium mb-2">
            <input type="radio" name="dateOption" value="manual" onchange="toggleDateInput()"> 
            <span data-i18n="form.date_option_manual">පසුගිය දිනය ඇතුළත් කරන්න</span>
          </label>
        </div>
        <div class="mb-4" id="dateInputContainer" style="display:none;">
          <label class="block text-sm font-medium mb-2" data-i18n="form.reorg_date">ප්රතිසංවිධාන දිනය</label>
          <input type="date" id="reorgDate" class="w-full px-3 py-2 border rounded">
        </div>
        <div class="flex justify-end gap-2">
          <button onclick="closeReorgModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" data-i18n="button.cancel">අවලංගු කරන්න</button>
          <button onclick="saveReorg()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" data-i18n="button.save">සුරකින්න</button>
        </div>
      </div>
    </div>
    
    <div id="historyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
        <h3 class="text-xl font-bold mb-4" data-i18n="modal.reorg_history_title">ප්රතිසංවිධාන ඉතිහාසය</h3>
        <div id="historyContent" class="mb-4"></div>
        <div class="flex justify-end">
          <button onclick="closeHistoryModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" data-i18n="button.close">වසන්න</button>
        </div>
      </div>
    </div>
  `;

  setTimeout(() => {
    if (window.i18n && window.i18n.applyTranslations) {
      window.i18n.applyTranslations();
    }
  }, 100);
}

let currentClubId = null;

function toggleDateInput() {
  const dateOption = document.querySelector('input[name="dateOption"]:checked').value;
  const container = document.getElementById('dateInputContainer');
  const dateInput = document.getElementById('reorgDate');
  
  if (dateOption === 'manual') {
    container.style.display = 'block';
    dateInput.value = '';
  } else {
    container.style.display = 'none';
    dateInput.value = new Date().toISOString().split('T')[0];
  }
}

function openReorgModal(clubId) {
  currentClubId = clubId;
  document.getElementById('reorgModal').classList.remove('hidden');
  document.querySelector('input[name="dateOption"][value="auto"]').checked = true;
  toggleDateInput();
}

function closeReorgModal() {
  document.getElementById('reorgModal').classList.add('hidden');
  currentClubId = null;
}

function saveReorg() {
  const dateOption = document.querySelector('input[name="dateOption"]:checked').value;
  let reorgDate;
  
  if (dateOption === 'auto') {
    reorgDate = new Date().toISOString().split('T')[0];
  } else {
    reorgDate = document.getElementById('reorgDate').value;
    if (!reorgDate) {
      alert('කරුණාකර දිනය ඇතුල් කරන්න');
      return;
    }
  }

  fetch('../api/reorganizations.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ club_id: currentClubId, reorg_date: reorgDate })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('ප්රතිසංවිධාන දිනය සාර්ථකව එකතු කරන ලදී');
      closeReorgModal();
      loadClubDetails(currentClubId);
    } else {
      alert(data.message || 'දෝෂයකි');
    }
  })
  .catch(err => {
    console.error(err);
    alert('දෝෂයකි');
  });
}

function deleteReorg(clubId) {
  if (!confirm('අවසාන ප්රතිසංවිධාන දිනය ඉවත් කිරීමට අවශ්යද?')) return;

  fetch('../api/reorganizations.php', {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ club_id: clubId })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('ප්රතිසංවිධාන දිනය ඉවත් කරන ලදී');
      loadClubDetails(clubId);
    } else {
      alert(data.message || 'දෝෂයකි');
    }
  })
  .catch(err => {
    console.error(err);
    alert('දෝෂයකි');
  });
}

function viewReorgHistory(clubId) {
  fetch(`../api/reorganizations.php?club_id=${clubId}`)
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      const historyContent = document.getElementById('historyContent');
      if (data.data.length === 0) {
        historyContent.innerHTML = '<p class="text-center text-gray-500" data-i18n="message.no_reorg_history">ප්රතිසංවිධාන ඉතිහාසයක් නොමැත</p>';
      } else {
        historyContent.innerHTML = `
          <table class="w-full">
            <thead>
              <tr class="bg-gray-100">
                <th class="p-2 text-left">#</th>
                <th class="p-2 text-left" data-i18n="form.reorg_date">දිනය</th>
              </tr>
            </thead>
            <tbody>
              ${data.data.map((r, i) => `
                <tr class="border-t">
                  <td class="p-2">${i + 1}</td>
                  <td class="p-2">${formatDate(r.reorg_date)}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        `;
      }
      document.getElementById('historyModal').classList.remove('hidden');
      if (window.i18n && window.i18n.applyTranslations) {
        window.i18n.applyTranslations();
      }
    } else {
      alert(data.message || 'දෝෂයකි');
    }
  })
  .catch(err => {
    console.error(err);
    alert('දෝෂයකි');
  });
}

function closeHistoryModal() {
  document.getElementById('historyModal').classList.add('hidden');
}

function showError(message) {
  const container = document.getElementById("clubDetails");
  container.innerHTML = `
    <div class="detail-card">
      <div class="text-center text-red-600">
        <p>${escapeHtml(message)}</p>
        <a href="dashboard.php" class="mt-4 inline-block text-blue-600 hover:text-blue-800" data-i18n="button.back">← ආපසු</a>
      </div>
    </div>
  `;
}

function formatDate(dateString) {
  if (!dateString) return "-";
  const date = new Date(dateString);
  return date.toLocaleDateString("si-LK");
}

function escapeHtml(text) {
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  };
  return text ? String(text).replace(/[&<>\"']/g, (m) => map[m]) : "";
}

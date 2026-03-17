/**
 * clubs-public.js
 * Drives the public (no-login) club directory page.
 * Features: stats bar, skeleton loading, search highlight, mobile cards,
 *           numbered pagination, copy reg#, reset filters.
 */

(function () {
  "use strict";

  function getBasePath() {
    const s = document.querySelector('script[src*="i18n.js"]');
    if (s) return s.getAttribute("src").replace(/assets\/js\/i18n\.js$/, "");
    return "../";
  }

  const base = getBasePath();
  const API_CLUBS = base + "api/public-clubs.php";
  const API_LOC = base + "api/public-locations.php";

  // ── State ────────────────────────────────────────────────────────────────
  let currentPage = 1;
  let totalPages = 1;
  let debounceTimer = null;

  // ── DOM refs ─────────────────────────────────────────────────────────────
  const searchInput = document.getElementById("pub-search");
  const districtSel = document.getElementById("pub-district");
  const divisionSel = document.getElementById("pub-division");
  const statusSel = document.getElementById("pub-status");
  const resetBtn = document.getElementById("pub-reset");
  const tbody = document.getElementById("pub-clubs-body");
  const cardsDiv = document.getElementById("pub-clubs-cards");
  const resultInfo = document.getElementById("pub-result-info");
  const pagination = document.getElementById("pub-pagination");
  const prevBtn = document.getElementById("pub-prev-btn");
  const nextBtn = document.getElementById("pub-next-btn");
  const pageNumbers = document.getElementById("pub-page-numbers");
  const modal = document.getElementById("pub-modal");
  const modalBackdrop = document.getElementById("pub-modal-backdrop");
  const modalClose = document.getElementById("pub-modal-close");
  const copyRegBtn = document.getElementById("md-copy-reg");

  // ── i18n helper ──────────────────────────────────────────────────────────
  function t(key, fallback) {
    if (window.i18n && typeof window.i18n.t === "function") {
      const val = window.i18n.t(key);
      if (val && val !== key) return val;
    }
    return fallback || key;
  }

  // ── Boot ─────────────────────────────────────────────────────────────────
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

  async function init() {
    applyPlaceholders();
    await Promise.all([loadDistricts(), loadStats()]);
    await fetchClubs();

    // Search debounce
    searchInput.addEventListener("input", () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        currentPage = 1;
        fetchClubs();
      }, 350);
    });

    // District cascade
    districtSel.addEventListener("change", async () => {
      const distId = districtSel.value;
      divisionSel.innerHTML = `<option value="">${t("public.all_divisions", "All Divisions")}</option>`;
      divisionSel.disabled = !distId;
      if (distId) await loadDivisions(distId);
      currentPage = 1;
      fetchClubs();
    });

    divisionSel.addEventListener("change", () => {
      currentPage = 1;
      fetchClubs();
    });
    statusSel.addEventListener("change", () => {
      currentPage = 1;
      fetchClubs();
    });

    // Reset
    resetBtn.addEventListener("click", () => {
      searchInput.value = "";
      districtSel.value = "";
      divisionSel.innerHTML = `<option value="">${t("public.all_divisions", "All Divisions")}</option>`;
      divisionSel.disabled = true;
      statusSel.value = "";
      currentPage = 1;
      fetchClubs();
    });

    // Pagination
    prevBtn.addEventListener("click", () => {
      if (currentPage > 1) {
        currentPage--;
        fetchClubs();
      }
    });
    nextBtn.addEventListener("click", () => {
      if (currentPage < totalPages) {
        currentPage++;
        fetchClubs();
      }
    });

    // Modal
    modalClose.addEventListener("click", closeModal);
    modalBackdrop.addEventListener("click", closeModal);
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") closeModal();
    });

    // Copy reg#
    copyRegBtn.addEventListener("click", () => {
      const reg = document.getElementById("md-reg").textContent;
      if (!reg || reg === "—") return;
      navigator.clipboard
        .writeText(reg)
        .then(() => {
          const orig = copyRegBtn.innerHTML;
          copyRegBtn.innerHTML = `<svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>`;
          setTimeout(() => {
            copyRegBtn.innerHTML = orig;
          }, 2000);
        })
        .catch(() => {});
    });

    // Language change
    document.addEventListener("languageChanged", () => {
      applyPlaceholders();
      document.querySelectorAll("[data-i18n]").forEach((el) => {
        if (el.tagName === "OPTION") {
          const key = el.getAttribute("data-i18n");
          if (key) el.textContent = t(key, el.textContent);
        }
      });
    });
  }

  function applyPlaceholders() {
    document.querySelectorAll("[data-i18n-placeholder]").forEach((el) => {
      el.placeholder = t(
        el.getAttribute("data-i18n-placeholder"),
        el.placeholder,
      );
    });
    document.querySelectorAll("[data-i18n-title]").forEach((el) => {
      el.title = t(el.getAttribute("data-i18n-title"), el.title);
    });
  }

  // ── Stats ────────────────────────────────────────────────────────────────
  async function loadStats() {
    try {
      const res = await fetch(`${API_CLUBS}?stats=1`);
      const data = await res.json();
      if (!data.success) return;
      animateCounter("stat-total", data.data.total || 0);
      animateCounter("stat-active", data.data.active || 0);
      animateCounter("stat-expired", data.data.expired || 0);
    } catch (_) {}
  }

  function animateCounter(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    const duration = 600;
    const start = performance.now();
    function step(now) {
      const progress = Math.min((now - start) / duration, 1);
      el.textContent = Math.floor(progress * target);
      if (progress < 1) requestAnimationFrame(step);
      else el.textContent = target;
    }
    requestAnimationFrame(step);
  }

  // ── Locations ────────────────────────────────────────────────────────────
  async function loadDistricts() {
    try {
      const res = await fetch(`${API_LOC}?type=district`);
      const data = await res.json();
      if (!data.success) return;
      data.data.forEach((d) => {
        const opt = document.createElement("option");
        opt.value = d.id;
        opt.textContent = d.name;
        districtSel.appendChild(opt);
      });
    } catch (_) {}
  }

  async function loadDivisions(districtId) {
    try {
      const res = await fetch(
        `${API_LOC}?type=division&parent_id=${encodeURIComponent(districtId)}`,
      );
      const data = await res.json();
      if (!data.success) return;
      data.data.forEach((d) => {
        const opt = document.createElement("option");
        opt.value = d.id;
        opt.textContent = d.name;
        divisionSel.appendChild(opt);
      });
    } catch (_) {}
  }

  // ── Fetch clubs ──────────────────────────────────────────────────────────
  async function fetchClubs() {
    setLoading(true);
    const search = (searchInput.value || "").trim();
    const district = districtSel.value;
    const division = divisionSel.value;
    const status = statusSel.value;
    const params = new URLSearchParams();
    if (search) params.append("search", search);
    if (district) params.append("district_id", district);
    if (division) params.append("division_id", division);
    if (status) params.append("reorg_status", status);
    params.append("page", String(currentPage));
    params.append("limit", "20");

    try {
      const res = await fetch(`${API_CLUBS}?${params.toString()}`);
      const data = await res.json();
      if (!data.success) {
        renderError(t("message.error", "An error occurred"));
        return;
      }
      const clubs = data.data || [];
      const pag = data.pagination || data.meta?.pagination || {};
      totalPages = pag.total_pages || 1;
      currentPage = pag.page || 1;
      renderResults(clubs, pag, search);
    } catch (_) {
      renderError(t("message.error", "An error occurred"));
    } finally {
      setLoading(false);
    }
  }

  // ── Skeleton loading ─────────────────────────────────────────────────────
  function setLoading(on) {
    if (!on) return;
    // Desktop skeleton rows
    const skeletonRow = () =>
      `<tr class="border-b border-gray-100 animate-pulse">
        <td class="px-4 py-3"><div class="h-3 bg-gray-200 rounded w-6"></div></td>
        <td class="px-4 py-3"><div class="h-3 bg-gray-200 rounded w-40"></div></td>
        <td class="px-4 py-3"><div class="h-3 bg-gray-200 rounded w-24"></div></td>
        <td class="px-4 py-3"><div class="h-3 bg-gray-200 rounded w-20"></div></td>
        <td class="px-4 py-3"><div class="h-3 bg-gray-200 rounded w-20"></div></td>
        <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded-full w-14"></div></td>
        <td class="px-4 py-3"><div class="h-7 bg-gray-200 rounded-lg w-20 mx-auto"></div></td>
      </tr>`;
    tbody.innerHTML = Array(6).fill(skeletonRow()).join("");

    // Mobile skeleton cards
    const skeletonCard = () =>
      `<div class="bg-white rounded-xl border border-gray-200 p-4 animate-pulse">
        <div class="flex justify-between mb-3">
          <div class="h-4 bg-gray-200 rounded w-40"></div>
          <div class="h-4 bg-gray-200 rounded w-16"></div>
        </div>
        <div class="grid grid-cols-2 gap-2 mb-3">
          <div class="h-3 bg-gray-200 rounded w-24"></div>
          <div class="h-3 bg-gray-200 rounded w-24"></div>
        </div>
        <div class="h-8 bg-gray-200 rounded-lg w-full"></div>
      </div>`;
    cardsDiv.innerHTML = Array(4).fill(skeletonCard()).join("");
  }

  // ── Render results (table + cards) ───────────────────────────────────────
  function renderResults(clubs, pag, searchTerm) {
    if (!clubs.length) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center py-12 text-gray-400">${t("public.no_clubs", "No clubs found")}</td></tr>`;
      cardsDiv.innerHTML = `<p class="text-center py-6 text-gray-400">${t("public.no_clubs", "No clubs found")}</p>`;
      resultInfo.textContent = "";
      pagination.style.display = "none";
      return;
    }

    const offset = ((pag.page || 1) - 1) * (pag.limit || 20);
    const totalLabel = t("report.total_records", "Total Records");
    resultInfo.textContent = `${totalLabel}: ${pag.total || clubs.length}`;
    const btnLabel = t("button.view_details", "View Details");

    // Desktop table rows
    tbody.innerHTML = clubs
      .map((club, i) => {
        const rowBg = i % 2 === 0 ? "" : "bg-gray-50/50";
        return `<tr class="border-b border-gray-100 hover:bg-blue-50/40 transition ${rowBg}">
        <td class="px-4 py-3 text-gray-500 text-sm">${offset + i + 1}</td>
        <td class="px-4 py-3 font-medium text-gray-800">${highlight(esc(club.name), searchTerm)}</td>
        <td class="px-4 py-3 font-mono text-xs text-gray-600">${highlight(esc(club.reg_number), searchTerm)}</td>
        <td class="px-4 py-3 text-gray-600 text-sm">${esc(club.district_name || "—")}</td>
        <td class="px-4 py-3 text-gray-600 text-sm">${esc(club.division_name || "—")}</td>
        <td class="px-4 py-3">${statusBadge(club.reorg_status)}</td>
        <td class="px-4 py-3 text-center">
          <button class="pub-view-btn inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition"
            data-club='${escapeAttr(JSON.stringify(club))}'>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            ${esc(btnLabel)}
          </button>
        </td>
      </tr>`;
      })
      .join("");

    // Mobile cards
    cardsDiv.innerHTML = clubs
      .map((club) => {
        return `<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <div class="flex items-start justify-between gap-2 mb-2">
          <p class="font-semibold text-gray-800 text-sm leading-snug">${highlight(esc(club.name), searchTerm)}</p>
          ${statusBadge(club.reorg_status)}
        </div>
        <p class="font-mono text-xs text-blue-700 mb-3">${highlight(esc(club.reg_number), searchTerm)}</p>
        <div class="grid grid-cols-2 gap-x-4 gap-y-1 mb-3 text-xs text-gray-500">
          <span>${esc(club.district_name || "—")}</span>
          <span>${esc(club.division_name || "—")}</span>
        </div>
        <button class="pub-view-btn w-full flex items-center justify-center gap-1.5 py-2 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition"
          data-club='${escapeAttr(JSON.stringify(club))}'>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          ${esc(btnLabel)}
        </button>
      </div>`;
      })
      .join("");

    // Wire view buttons on both table and cards
    document.querySelectorAll(".pub-view-btn").forEach((btn) => {
      btn.addEventListener("click", () =>
        openModal(JSON.parse(btn.getAttribute("data-club"))),
      );
    });

    // Pagination
    renderPagination(pag);
  }

  // ── Numbered pagination ──────────────────────────────────────────────────
  function renderPagination(pag) {
    totalPages = pag.total_pages || 1;
    currentPage = pag.page || 1;

    if (totalPages <= 1) {
      pagination.style.display = "none";
      return;
    }

    pagination.style.removeProperty("display");
    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= totalPages;

    // Build page number buttons with ellipsis
    const pages = paginationPages(currentPage, totalPages);
    pageNumbers.innerHTML = pages
      .map((p) => {
        if (p === "...") {
          return `<span class="px-1 text-gray-400 self-center">…</span>`;
        }
        const active = p === currentPage;
        return `<button data-page="${p}" class="w-8 h-8 rounded-lg text-sm font-medium transition
        ${active ? "bg-blue-600 text-white shadow-sm" : "border border-gray-300 bg-white text-gray-600 hover:bg-gray-50"}">${p}</button>`;
      })
      .join("");

    pageNumbers.querySelectorAll("button[data-page]").forEach((btn) => {
      btn.addEventListener("click", () => {
        currentPage = parseInt(btn.getAttribute("data-page"), 10);
        fetchClubs();
      });
    });
  }

  function paginationPages(cur, total) {
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = [];
    const addRange = (from, to) => {
      for (let i = from; i <= to; i++) pages.push(i);
    };
    pages.push(1);
    if (cur > 3) pages.push("...");
    addRange(Math.max(2, cur - 1), Math.min(total - 1, cur + 1));
    if (cur < total - 2) pages.push("...");
    pages.push(total);
    // Deduplicate
    return pages.filter((v, i, a) => a.indexOf(v) === i);
  }

  // ── Highlight search term ────────────────────────────────────────────────
  function highlight(html, term) {
    if (!term) return html;
    const safe = term.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    return html.replace(
      new RegExp(`(${safe})`, "gi"),
      `<mark class="bg-yellow-200 rounded px-0.5">$1</mark>`,
    );
  }

  // ── Status badge ─────────────────────────────────────────────────────────
  function statusBadge(status) {
    if (status === "active") {
      return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">${t("status.active", "Active")}</span>`;
    }
    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">${t("status.expired", "Expired")}</span>`;
  }

  // ── Modal ────────────────────────────────────────────────────────────────
  function openModal(club) {
    document.getElementById("md-name").textContent = club.name || "—";
    document.getElementById("md-reg").textContent = club.reg_number || "—";
    document.getElementById("md-date").textContent =
      club.registration_date || "—";
    document.getElementById("md-district").textContent =
      club.district_name || "—";
    document.getElementById("md-division").textContent =
      club.division_name || "—";
    document.getElementById("md-gs").textContent = club.gs_division_name || "—";
    document.getElementById("md-status").innerHTML = statusBadge(
      club.reorg_status,
    );
    // Reset copy button
    copyRegBtn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>`;
    modal.style.removeProperty("display");
    document.body.style.overflow = "hidden";
  }

  function closeModal() {
    modal.style.display = "none";
    document.body.style.overflow = "";
  }

  // ── Error state ──────────────────────────────────────────────────────────
  function renderError(msg) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-10 text-red-400">${esc(msg)}</td></tr>`;
    cardsDiv.innerHTML = `<p class="text-center py-6 text-red-400">${esc(msg)}</p>`;
    pagination.style.display = "none";
    resultInfo.textContent = "";
  }

  // ── Helpers ──────────────────────────────────────────────────────────────
  function esc(str) {
    if (str == null) return "";
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function escapeAttr(str) {
    return String(str).replace(/'/g, "&#39;").replace(/"/g, "&quot;");
  }
})();

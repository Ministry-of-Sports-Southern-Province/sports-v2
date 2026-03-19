/**
 * Edit Club JavaScript
 * Handles club editing form functionality
 */

// Store Tom Select instances
window.tomSelectInstances = {};

// Store club ID globally
let currentClubId = null;

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", async function () {
  // Get club ID from URL
  const urlParams = new URLSearchParams(window.location.search);
  currentClubId = urlParams.get("id");

  if (!currentClubId) {
    alert(
      window.i18n
        ? window.i18n.t("message.invalid_club_id_required")
        : "Club ID is required",
    );
    window.location.href = "dashboard.php";
    return;
  }

  // Initialize Tom Select dropdowns
  await initializeTomSelect();

  // Setup form submission
  setupFormSubmission();

  // Load and populate club data
  await loadClubData();
});

/**
 * Initialize Tom Select for all select elements
 */
async function initializeTomSelect() {
  // District select
  window.tomSelectInstances.district = new TomSelect("#district", {
    create: true,
    createOnBlur: true,
    maxOptions: 50,
    placeholder: window.i18n
      ? window.i18n.t("placeholder.type_to_search")
      : "Type to search...",
    load: function (query, callback) {
      if (query.length < 2) {
        callback();
        return;
      }
      fetch(
        `../api/locations.php?type=district&search=${encodeURIComponent(query)}`,
      )
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            callback(
              data.data.map((item) => ({ value: item.id, text: item.name })),
            );
          } else {
            callback();
          }
        })
        .catch(() => callback());
    },
    onChange: function (value) {
      handleDistrictChange(value);
    },
  });

  // Load all districts initially
  try {
    const response = await fetch("../api/locations.php?type=district&search=");
    const data = await response.json();
    if (data.success) {
      data.data.forEach((item) => {
        window.tomSelectInstances.district.addOption({
          value: item.id,
          text: item.name,
        });
      });
    }
  } catch (e) {
    console.error("Error loading districts:", e);
  }

  // Division select
  window.tomSelectInstances.division = new TomSelect("#division", {
    create: true,
    createOnBlur: true,
    maxOptions: 100,
    placeholder: window.i18n
      ? window.i18n.t("placeholder.select_district_first")
      : "Select district first",
    searchField: ["text"],
    sortField: { field: "text", direction: "asc" },
    load: function (query, callback) {
      const districtId = window.tomSelectInstances.district.getValue();
      if (!districtId) {
        callback();
        return;
      }
      fetch(
        `../api/locations.php?type=division&search=${encodeURIComponent(
          query,
        )}&parent_id=${districtId}`,
      )
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            callback(
              data.data.map((item) => ({ value: item.id, text: item.name })),
            );
          } else {
            callback();
          }
        })
        .catch(() => callback());
    },
    onChange: function (value) {
      handleDivisionChange(value);
    },
  });

  // GN Division select
  window.tomSelectInstances.gsDivision = new TomSelect("#gsDivision", {
    create: true,
    createOnBlur: true,
    maxOptions: 200,
    placeholder: window.i18n
      ? window.i18n.t("placeholder.select_division_first")
      : "Select division first",
    searchField: ["text"],
    sortField: { field: "text", direction: "asc" },
    load: function (query, callback) {
      const divisionId = window.tomSelectInstances.division.getValue();
      if (!divisionId) {
        callback();
        return;
      }
      fetch(
        `../api/locations.php?type=gs_division&search=${encodeURIComponent(
          query,
        )}&parent_id=${divisionId}`,
      )
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            callback(
              data.data.map((item) => ({ value: item.id, text: item.name })),
            );
          } else {
            callback();
          }
        })
        .catch(() => callback());
    },
  });

}

/**
 * Handle district change
 */
function handleDistrictChange(districtId) {
  // Clear division and GN division
  window.tomSelectInstances.division.clear();
  window.tomSelectInstances.division.clearOptions();
  window.tomSelectInstances.gsDivision.clear();
  window.tomSelectInstances.gsDivision.clearOptions();

  if (districtId) {
    // Load divisions for selected district
    fetch(`../api/locations.php?type=division&parent_id=${districtId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          data.data.forEach((item) => {
            window.tomSelectInstances.division.addOption({
              value: item.id,
              text: item.name,
            });
          });
        }
      });
  }
}

/**
 * Handle division change
 */
function handleDivisionChange(divisionId) {
  // Clear GN division
  window.tomSelectInstances.gsDivision.clear();
  window.tomSelectInstances.gsDivision.clearOptions();

  if (divisionId) {
    // Load GN divisions for selected division
    fetch(`../api/locations.php?type=gs_division&parent_id=${divisionId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          data.data.forEach((item) => {
            window.tomSelectInstances.gsDivision.addOption({
              value: item.id,
              text: item.name,
            });
          });
        }
      });
  }
}

/**
 * Load club data from API
 */
async function loadClubData() {
  try {
    const response = await fetch(`../api/clubs.php?id=${currentClubId}`);
    const data = await response.json();

    if (data.success) {
      await populateForm(data.data);
    } else {
      alert(
        window.i18n
          ? window.i18n.t("message.load_error")
          : "Failed to load club data",
      );
      window.location.href = "dashboard.php";
    }
  } catch (error) {
    console.error("Error loading club data:", error);
    alert(
      window.i18n
        ? window.i18n.t("message.load_error")
        : "Failed to load club data",
    );
    window.location.href = "dashboard.php";
  }
}

/**
 * Populate form with club data
 */
async function populateForm(club) {
  // Basic fields
  document.getElementById("regNumberFull").value = club.reg_number || "";
  document.getElementById("clubName").value = club.name || "";
  document.getElementById("registrationDate").value =
    club.registration_date || "";

  // Chairman info
  document.getElementById("chairmanName").value = club.chairman_name || "";
  document.getElementById("chairmanAddress").value =
    club.chairman_address || "";
  document.getElementById("chairmanPhone").value = club.chairman_phone || "";

  // Secretary info
  document.getElementById("secretaryName").value = club.secretary_name || "";
  document.getElementById("secretaryAddress").value =
    club.secretary_address || "";
  document.getElementById("secretaryPhone").value = club.secretary_phone || "";

  // Location fields - load options then set values
  if (club.district_id) {
    // Set district
    window.tomSelectInstances.district.setValue(club.district_id, true);

    // Load and set division
    if (club.division_id) {
      try {
        const divResponse = await fetch(
          `../api/locations.php?type=division&parent_id=${club.district_id}`,
        );
        const divData = await divResponse.json();
        if (divData.success) {
          divData.data.forEach((item) => {
            window.tomSelectInstances.division.addOption({
              value: item.id,
              text: item.name,
            });
          });
          window.tomSelectInstances.division.setValue(club.division_id, true);
        }
      } catch (e) {
        console.error("Error loading divisions:", e);
      }

      // Load and set GN division
      if (club.gs_division_id) {
        try {
          const gnResponse = await fetch(
            `../api/locations.php?type=gs_division&parent_id=${club.division_id}`,
          );
          const gnData = await gnResponse.json();
          if (gnData.success) {
            gnData.data.forEach((item) => {
              window.tomSelectInstances.gsDivision.addOption({
                value: item.id,
                text: item.name,
              });
            });
            window.tomSelectInstances.gsDivision.setValue(
              club.gs_division_id,
              true,
            );
          }
        } catch (e) {
          console.error("Error loading GN divisions:", e);
        }
      }
    }
  }

  // Reorganization dates
  if (club.reorganizations && club.reorganizations.length > 0) {
    club.reorganizations.forEach((reorg) => {
      addReorgDateField(reorg.reorg_date);
    });
  }
}

/**
 * Add reorganization date field
 */
function addReorgDateField(date = "") {
  const container = document.getElementById("reorgDatesContainer");
  const index = container.children.length;

  const div = document.createElement("div");
  div.className = "flex items-center gap-3";
  div.innerHTML = `
    <input type="date" 
      class="form-input flex-1 reorg-date-input" 
      value="${date}" 
      max="${new Date().toISOString().split("T")[0]}">
    <button type="button" 
      class="btn btn-outline text-red-600 hover:bg-red-50 remove-reorg-btn">
      <span data-i18n="button.remove">ඉවත් කරන්න</span>
    </button>
  `;

  // Add remove functionality
  div.querySelector(".remove-reorg-btn").addEventListener("click", function () {
    div.remove();
  });

  container.appendChild(div);
}

/**
 * Setup form submission
 */
function setupFormSubmission() {
  // Add reorganization date button handler
  document
    .getElementById("addReorgDateBtn")
    .addEventListener("click", function () {
      addReorgDateField();
    });
  document
    .getElementById("editClubForm")
    .addEventListener("submit", async function (e) {
      e.preventDefault();

      // Validate phones
      if (!validatePhone("chairman") || !validatePhone("secretary")) {
        return;
      }

      // Collect form data
      const formData = new URLSearchParams();

      formData.append("club_name", document.getElementById("clubName").value);
      formData.append(
        "reg_number",
        document.getElementById("regNumberFull").value,
      );
      formData.append(
        "registration_date",
        document.getElementById("registrationDate").value,
      );
      formData.append("date_entry_type", "manual");

      // Location
      formData.append(
        "district_id",
        window.tomSelectInstances.district.getValue(),
      );
      formData.append(
        "division_id",
        window.tomSelectInstances.division.getValue(),
      );
      formData.append(
        "gs_division_id",
        window.tomSelectInstances.gsDivision.getValue() || "",
      );

      // Chairman
      formData.append(
        "chairman_name",
        document.getElementById("chairmanName").value,
      );
      formData.append(
        "chairman_address",
        document.getElementById("chairmanAddress").value,
      );
      formData.append(
        "chairman_phone",
        document.getElementById("chairmanPhone").value,
      );

      // Secretary
      formData.append(
        "secretary_name",
        document.getElementById("secretaryName").value,
      );
      formData.append(
        "secretary_address",
        document.getElementById("secretaryAddress").value,
      );
      formData.append(
        "secretary_phone",
        document.getElementById("secretaryPhone").value,
      );

      // Reorganization dates
      const reorgDates = [];
      document.querySelectorAll(".reorg-date-input").forEach((input) => {
        if (input.value) {
          reorgDates.push({ date: input.value });
        }
      });
      formData.append("reorganizations", JSON.stringify(reorgDates));

      // Disable submit button
      const submitBtn = document.getElementById("submitBtn");
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = window.i18n
        ? window.i18n.t("message.loading")
        : "Loading...";

      try {
        const response = await fetch(`../api/clubs.php?id=${currentClubId}`, {
          method: "PUT",
          body: formData.toString(),
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
        });

        const data = await response.json();

        if (data.success) {
          alert(
            window.i18n
              ? window.i18n.t("message.update_success")
              : "Club updated successfully!",
          );
          window.location.href = "dashboard.php";
        } else {
          alert(
            data.message ||
              (window.i18n
                ? window.i18n.t("message.update_error")
                : "Failed to update club"),
          );
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
      } catch (error) {
        console.error("Error updating club:", error);
        alert(
          window.i18n
            ? window.i18n.t("message.update_error")
            : "Failed to update club",
        );
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    });
}

/**
 * Validate phone number
 */
function validatePhone(role) {
  const phoneInput = document.getElementById(`${role}Phone`);
  const errorSpan = document.getElementById(`${role}PhoneError`);
  const phone = phoneInput.value.trim();

  if (phone !== "" && !/^\d{10}$/.test(phone)) {
    if (errorSpan) {
      errorSpan.textContent = window.i18n
        ? window.i18n.t("validation.phone_10_digits")
        : "Phone must be exactly 10 digits";
      errorSpan.classList.remove("hidden");
    }
    phoneInput.focus();
    return false;
  }

  if (errorSpan) {
    errorSpan.classList.add("hidden");
  }
  return true;
}

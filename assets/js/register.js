/**
 * Registration Form JavaScript
 * Handles Tom Select initialization, validation, and form submission
 */

// Store Tom Select instances
window.tomSelectInstances = {};

// Debounce function for search and validation
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Initialize form when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  initializeTomSelect();
  setupFormValidation();
  setupDateToggle();
  setupFormSubmission();
  checkEditMode();
});

/**
 * Initialize Tom Select for all select elements
 */
function initializeTomSelect() {
  // District select
  window.tomSelectInstances.district = new TomSelect("#district", {
    create: false,
    maxOptions: 50,
    placeholder: window.i18n.t("placeholder.select"),
    load: function (query, callback) {
      if (query.length < 3) {
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
              data.data.map((item) => ({
                value: item.id,
                text: item.name,
                sinhala_letter: item.sinhala_letter,
              })),
            );
          } else {
            callback();
          }
        })
        .catch(() => callback());
    },
    onInitialize: function () {
      // Load initial districts
      fetch("../api/locations.php?type=district&search=")
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            data.data.forEach((item) => {
              this.addOption({
                value: item.id,
                text: item.name,
                sinhala_letter: item.sinhala_letter,
              });
            });
          }
        });
    },
    onChange: function (value) {
      handleDistrictChange(value, this);
    },
  });

  // Division select
  window.tomSelectInstances.division = new TomSelect("#division", {
    create: true,
    createOnBlur: true,
    maxOptions: 50,
    placeholder: window.i18n.t("placeholder.select_district_first"),
    load: function (query, callback) {
      const districtId = window.tomSelectInstances.district.getValue();
      if (!districtId) {
        callback();
        return;
      }
      if (query.length < 3) {
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
              data.data.map((item) => ({
                value: item.id,
                text: item.name,
              })),
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
    onCreate: function (input, callback) {
      const districtId = window.tomSelectInstances.district.getValue();
      createNewLocation("division", input, districtId, callback);
    },
  });

  // GN Division select
  window.tomSelectInstances.gnDivision = new TomSelect("#gnDivision", {
    create: true,
    createOnBlur: true,
    maxOptions: 50,
    placeholder: window.i18n.t("placeholder.select_division_first"),
    load: function (query, callback) {
      const divisionId = window.tomSelectInstances.division.getValue();
      if (!divisionId) {
        callback();
        return;
      }
      if (query.length < 3) {
        callback();
        return;
      }

      fetch(
        `../api/locations.php?type=gn_division&search=${encodeURIComponent(
          query,
        )}&parent_id=${divisionId}`,
      )
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            callback(
              data.data.map((item) => ({
                value: item.id,
                text: item.name,
              })),
            );
          } else {
            callback();
          }
        })
        .catch(() => callback());
    },
    onCreate: function (input, callback) {
      const divisionId = window.tomSelectInstances.division.getValue();
      createNewLocation("gn_division", input, divisionId, callback);
    },
  });

  // Equipment select - Load all equipment types (standard and non-standard)
  window.tomSelectInstances.equipmentSelect = new TomSelect(
    "#equipmentSelect",
    {
      create: true,
      createOnBlur: true,
      plugins: ["remove_button"],
      maxOptions: null,
      placeholder: window.i18n.t("placeholder.type_to_search"),
      valueField: "id",
      labelField: "name",
      searchField: "name",
      preload: "focus",
      load: function (query, callback) {
        // Load all equipment (no is_standard filter)
        const url =
          query.length > 0
            ? `../api/equipment-types.php?search=${encodeURIComponent(query)}`
            : `../api/equipment-types.php`;

        fetch(url)
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              callback(data.data);
            } else {
              callback();
            }
          })
          .catch(() => callback());
      },
      onCreate: function (input, callback) {
        createNewEquipmentType(input, callback);
      },
      onChange: function (values) {
        updateEquipmentList(values);
      },
    },
  );
}

/**
 * Handle district selection change
 */
function handleDistrictChange(districtId, selectInstance) {
  // Clear division and GN division
  window.tomSelectInstances.division.clear();
  window.tomSelectInstances.division.clearOptions();
  window.tomSelectInstances.gnDivision.clear();
  window.tomSelectInstances.gnDivision.clearOptions();

  const regNumberManual = document.getElementById("regNumberManual");
  const regNumberHelper = document.getElementById("regNumberHelper");
  const regNumberStatus = document.getElementById("regNumberStatus");
  const regNumberError = document.getElementById("regNumberError");

  if (districtId) {
    // Get district info and update reg number prefix
    const option = selectInstance.options[districtId];
    if (option && option.sinhala_letter) {
      const prefix = `දපස/ක්‍රිඩා/${option.sinhala_letter}/`;
      document.getElementById("regNumberPrefix").value = prefix;
      updateFullRegNumber();

      // Enable registration number field
      regNumberManual.disabled = false;
      if (regNumberHelper) regNumberHelper.classList.add("hidden");

      // Focus on the field
      setTimeout(() => regNumberManual.focus(), 100);
    }

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
  } else {
    document.getElementById("regNumberPrefix").value = "දපස/ක්‍රිඩා/";
    updateFullRegNumber();

    // Disable and clear registration number field
    regNumberManual.disabled = true;
    regNumberManual.value = "";
    if (regNumberHelper) regNumberHelper.classList.remove("hidden");

    // Clear validation messages
    regNumberStatus.innerHTML = "";
    if (regNumberError) {
      regNumberError.classList.add("hidden");
      regNumberError.textContent = "";
    }
  }
}

/**
 * Handle division selection change
 */
function handleDivisionChange(divisionId) {
  // Clear GN division
  window.tomSelectInstances.gnDivision.clear();
  window.tomSelectInstances.gnDivision.clearOptions();

  if (divisionId) {
    // Load GN divisions for selected division
    fetch(`../api/locations.php?type=gn_division&parent_id=${divisionId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          data.data.forEach((item) => {
            window.tomSelectInstances.gnDivision.addOption({
              value: item.id,
              text: item.name,
            });
          });
        }
      });
  }
}

/**
 * Create new location (district/division/gn_division)
 */
function createNewLocation(type, name, parentId, callback) {
  const formData = new FormData();
  formData.append("type", type);
  formData.append("name", name);
  if (parentId) {
    formData.append("parent_id", parentId);
  }

  fetch("../api/locations.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        callback({
          value: data.data.id,
          text: data.data.name,
          sinhala_letter: data.data.sinhala_letter || undefined,
        });

        // If district was created, update reg number prefix
        if (type === "district" && data.data.sinhala_letter) {
          const prefix = `දපස/ක්‍රිඩා/${data.data.sinhala_letter}/`;
          document.getElementById("regNumberPrefix").value = prefix;
          updateFullRegNumber();
        }
      } else {
        alert(data.message || window.i18n.t("validation.duplicate_entry"));
        callback(false);
      }
    })
    .catch((error) => {
      console.error("Error creating location:", error);
      alert(window.i18n.t("message.error"));
      callback(false);
    });
}

/**
 * Create new equipment type
 */
function createNewEquipmentType(name, callback) {
  const formData = new FormData();
  formData.append("name", name);

  fetch("../api/equipment-types.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const newEquipment = {
          id: data.data.id,
          name: data.data.name,
        };

        // Add to Tom Select options cache so it's available in future searches
        const tomSelect = window.tomSelectInstances.equipmentSelect;
        tomSelect.addOption(newEquipment);

        // Call the callback to select the item
        callback(newEquipment);
      } else {
        alert(data.message || window.i18n.t("validation.duplicate_entry"));
        callback(false);
      }
    })
    .catch((error) => {
      console.error("Error creating equipment type:", error);
      alert(window.i18n.t("message.error"));
      callback(false);
    });
}

/**
 * Add newly created equipment to standard equipment section
 */
function addToStandardEquipmentSection(equipment) {
  const container = document.getElementById("standardEquipment");

  const div = document.createElement("div");
  div.className = "flex items-center space-x-3";
  div.innerHTML = `
    <input type="checkbox" id="eq_${equipment.id}" name="equipment[]" value="${
      equipment.id
    }" class="equipment-checkbox">
    <label for="eq_${equipment.id}" class="flex-grow text-gray-700">${
      equipment.name
    }</label>
    <input type="number" id="eq_qty_${equipment.id}" name="equipment_qty[${
      equipment.id
    }]" min="1" disabled
      class="w-20 border border-gray-300 rounded px-2 py-1 text-sm equipment-quantity"
      placeholder="${window.i18n.t("form.quantity")}">
  `;
  container.appendChild(div);
}

/**
 * Update equipment list with quantity inputs (preserves existing quantities)
 */
function updateEquipmentList(selectedIds) {
  const container = document.getElementById("equipmentList");

  // Store existing quantities before updating
  const existingQuantities = {};
  container.querySelectorAll(".equipment-quantity-input").forEach((input) => {
    const eqId = input.dataset.equipmentId;
    existingQuantities[eqId] = input.value;
  });

  container.innerHTML = "";

  if (!selectedIds || selectedIds.length === 0) {
    return;
  }

  const tomSelect = window.tomSelectInstances.equipmentSelect;

  selectedIds.forEach((id) => {
    const option = tomSelect.options[id];
    if (!option) return;

    const div = document.createElement("div");
    div.className = "flex items-center space-x-3 p-2 bg-gray-50 rounded";
    div.innerHTML = `
      <label class="flex-grow text-gray-700">${option.name}</label>
      <input type="number" 
        id="eq_qty_${id}" 
        data-equipment-id="${id}"
        class="w-24 border border-gray-300 rounded px-2 py-1 text-sm equipment-quantity-input"
        placeholder="${window.i18n.t("form.quantity")}"
        min="1"
        value="${existingQuantities[id] || "1"}"
        required>
    `;
    container.appendChild(div);
  });
}

/**
 * Load standard equipment items
 */
function loadStandardEquipment() {
  fetch("../api/equipment-types.php?is_standard=1")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const container = document.getElementById("standardEquipment");
        data.data.forEach((equipment) => {
          const div = document.createElement("div");
          div.className = "flex items-center space-x-3";
          div.innerHTML = `
                        <input type="checkbox" id="eq_${
                          equipment.id
                        }" name="equipment[]" value="${
                          equipment.id
                        }" class="equipment-checkbox">
                        <label for="eq_${
                          equipment.id
                        }" class="flex-grow text-gray-700">${
                          equipment.name
                        }</label>
                        <input type="number" id="eq_qty_${
                          equipment.id
                        }" name="equipment_qty[${
                          equipment.id
                        }]" min="1" disabled
                            class="w-20 border border-gray-300 rounded px-2 py-1 text-sm equipment-quantity"
                            placeholder="${window.i18n.t("form.quantity")}">
                    `;
          container.appendChild(div);
        });
      }
    });
}

/**
 * Setup equipment checkbox and quantity validation
 */
function setupEquipmentValidation() {
  document.addEventListener("change", function (e) {
    if (e.target.classList.contains("equipment-checkbox")) {
      const equipmentId = e.target.value;
      const qtyInput = document.getElementById(`eq_qty_${equipmentId}`);
      if (qtyInput) {
        qtyInput.disabled = !e.target.checked;
        if (e.target.checked) {
          qtyInput.focus();
        } else {
          qtyInput.value = "";
        }
      }
    }
  });
}

/**
 * Update full registration number
 */
function updateFullRegNumber() {
  const prefix = document.getElementById("regNumberPrefix").value.trim();
  const manual = document.getElementById("regNumberManual").value.trim();
  document.getElementById("regNumberFull").value = prefix + manual;
}

/**
 * Setup form validation
 */
function setupFormValidation() {
  // Manual reg number validation
  const regNumberManual = document.getElementById("regNumberManual");
  regNumberManual.addEventListener(
    "input",
    debounce(function () {
      validateRegNumber();
    }, 500),
  );

  // Phone number validation
  document
    .getElementById("chairmanPhone")
    .addEventListener("input", function (e) {
      e.target.value = e.target.value.replace(/[^0-9]/g, "");
      validatePhone("chairman");
    });

  document
    .getElementById("secretaryPhone")
    .addEventListener("input", function (e) {
      e.target.value = e.target.value.replace(/[^0-9]/g, "");
      validatePhone("secretary");
    });

  // Registration date validation
  document
    .getElementById("registrationDate")
    .addEventListener("change", function () {
      validateRegistrationDate();
    });
}

/**
 * Validate registration number
 */
function validateRegNumber() {
  const manual = document.getElementById("regNumberManual").value;
  const errorSpan = document.getElementById("regNumberError");
  const statusDiv = document.getElementById("regNumberStatus");

  // Clear previous status
  statusDiv.innerHTML = "";
  window.i18n.hideError(errorSpan);

  if (!manual) {
    return false;
  }

  // Check if only digits
  if (!/^[0-9]+$/.test(manual)) {
    window.i18n.showError(errorSpan, "reg_number_digits");
    return false;
  }

  // Update full reg number
  updateFullRegNumber();
  const fullRegNumber = document.getElementById("regNumberFull").value;

  // Debug logging
  console.log("Validating registration number:", fullRegNumber);
  console.log("Length:", fullRegNumber.length);

  // Show checking status
  statusDiv.innerHTML =
    '<span class="text-blue-600 text-sm">' +
    window.i18n.t("validation.reg_number_checking") +
    "</span>";

  // Check uniqueness
  fetch(
    `../api/validate-reg-number.php?reg_number=${encodeURIComponent(
      fullRegNumber,
    )}`,
  )
    .then((response) => response.json())
    .then((data) => {
      console.log("Validation response:", data);
      if (data.success && data.data.available) {
        statusDiv.innerHTML =
          '<svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        return true;
      } else {
        window.i18n.showError(errorSpan, "reg_number_duplicate");
        statusDiv.innerHTML =
          '<svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
        return false;
      }
    })
    .catch((error) => {
      console.error("Error validating reg number:", error);
      statusDiv.innerHTML = "";
      return false;
    });
}

/**
 * Validate phone number
 */
function validatePhone(type) {
  const phoneInput = document.getElementById(`${type}Phone`);
  const errorSpan = document.getElementById(`${type}PhoneError`);
  const phone = phoneInput.value;

  window.i18n.hideError(errorSpan);

  if (phone && phone.length !== 10) {
    window.i18n.showError(errorSpan, "phone_10_digits");
    return false;
  }

  return true;
}

/**
 * Validate registration date
 */
function validateRegistrationDate() {
  const dateInput = document.getElementById("registrationDate");
  const errorSpan = document.getElementById("registrationDateError");
  const dateValue = dateInput.value;

  window.i18n.hideError(errorSpan);

  if (dateValue) {
    const selectedDate = new Date(dateValue);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (selectedDate > today) {
      window.i18n.showError(errorSpan, "date_future");
      return false;
    }
  }

  return true;
}

/**
 * Setup date type toggle
 */
function setupDateToggle() {
  const dateAutoRadio = document.getElementById("dateAuto");
  const dateManualRadio = document.getElementById("dateManual");
  const manualDateContainer = document.getElementById("manualDateContainer");
  const registrationDateInput = document.getElementById("registrationDate");

  dateAutoRadio.addEventListener("change", function () {
    if (this.checked) {
      manualDateContainer.classList.add("hidden");
      registrationDateInput.removeAttribute("required");
    }
  });

  dateManualRadio.addEventListener("change", function () {
    if (this.checked) {
      manualDateContainer.classList.remove("hidden");
      registrationDateInput.setAttribute("required", "required");
    }
  });
}

/**
 * Setup form submission
 */
function setupFormSubmission() {
  document
    .getElementById("registrationForm")
    .addEventListener("submit", function (e) {
      e.preventDefault();

      // Validate all fields
      if (!validateForm()) {
        return;
      }

      // Collect form data
      const formData = new FormData();

      // Basic club info
      formData.append(
        "reg_number",
        document.getElementById("regNumberFull").value,
      );
      formData.append("club_name", document.getElementById("clubName").value);

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
        "gn_division_id",
        window.tomSelectInstances.gnDivision.getValue() || "",
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

      // Date
      const dateType = document.querySelector(
        'input[name="dateType"]:checked',
      ).value;
      formData.append("date_entry_type", dateType);
      if (dateType === "manual") {
        formData.append(
          "registration_date",
          document.getElementById("registrationDate").value,
        );
      }

      // Equipment - Collect all selected equipment with quantities
      const selectedEquipment = [];
      const equipmentIds = window.tomSelectInstances.equipmentSelect.getValue();

      if (equipmentIds.length > 0) {
        equipmentIds.forEach((id) => {
          const qtyInput = document.getElementById(`eq_qty_${id}`);
          const quantity = qtyInput ? parseInt(qtyInput.value) : 1;
          if (quantity && quantity > 0) {
            selectedEquipment.push({
              equipment_type_id: id,
              quantity: quantity,
            });
          }
        });
      }

      formData.append("equipment", JSON.stringify(selectedEquipment));

      // Check if editing
      const clubId =
        document.getElementById("registrationForm")?.dataset.clubId ||
        document.querySelector('button[type="submit"]')?.dataset.clubId;

      // Disable submit button
      const submitBtn = document.getElementById("submitBtn");
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = window.i18n.t("message.loading");

      // Convert FormData to URLSearchParams for PUT request
      const urlParams = new URLSearchParams();
      for (const [key, value] of formData.entries()) {
        urlParams.append(key, value);
      }

      // Submit form
      const url = clubId ? `../api/clubs.php?id=${clubId}` : "../api/clubs.php";
      const method = clubId ? "PUT" : "POST";
      const body = clubId ? urlParams.toString() : formData;
      const headers = clubId
        ? { "Content-Type": "application/x-www-form-urlencoded" }
        : {};

      fetch(url, {
        method: method,
        body: body,
        headers: headers,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            const message = clubId
              ? window.i18n.t("message.update_success") ||
                "Club updated successfully!"
              : window.i18n.t("message.registration_success");
            alert(message);
            window.location.href = "dashboard.php";
          } else {
            alert(data.message || window.i18n.t("message.registration_error"));
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
          }
        })
        .catch((error) => {
          console.error("Error submitting form:", error);
          alert(window.i18n.t("message.registration_error"));
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        });
    });
}

/**
 * Validate entire form before submission
 */
function validateForm() {
  let isValid = true;

  // Validate reg number
  const regNumber = document.getElementById("regNumberFull").value;
  if (!regNumber || !document.getElementById("regNumberManual").value) {
    window.i18n.showError(
      document.getElementById("regNumberError"),
      "required",
    );
    isValid = false;
  }

  // Validate district
  if (!window.tomSelectInstances.district.getValue()) {
    window.i18n.showError(document.getElementById("districtError"), "required");
    isValid = false;
  }

  // Validate division
  if (!window.tomSelectInstances.division.getValue()) {
    window.i18n.showError(document.getElementById("divisionError"), "required");
    isValid = false;
  }

  // Validate phones
  if (!validatePhone("chairman")) isValid = false;
  if (!validatePhone("secretary")) isValid = false;

  // Validate date if manual
  if (document.getElementById("dateManual").checked) {
    if (!validateRegistrationDate()) isValid = false;
    if (!document.getElementById("registrationDate").value) {
      window.i18n.showError(
        document.getElementById("registrationDateError"),
        "required",
      );
      isValid = false;
    }
  }

  // Validate equipment quantities
  document.querySelectorAll(".equipment-quantity-input").forEach((input) => {
    const quantity = parseInt(input.value);
    if (!quantity || quantity < 1) {
      alert(window.i18n.t("validation.quantity_min"));
      isValid = false;
    }
  });

  return isValid;
}

/**
 * Check if we're in edit mode and load club data
 */
function checkEditMode() {
  const urlParams = new URLSearchParams(window.location.search);
  const clubId = urlParams.get("edit");

  if (!clubId) {
    return;
  }

  // Change page heading and button text
  document.querySelector("h1").textContent =
    window.i18n.t("heading.edit_club") || "Edit Club";
  const submitBtn = document.querySelector('button[type="submit"]');
  submitBtn.textContent = window.i18n.t("button.update_club") || "Update Club";
  submitBtn.dataset.clubId = clubId;

  // Load club data
  fetch(`/sports-v2/api/clubs.php?id=${clubId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        populateFormWithClubData(data.data);
      } else {
        alert(
          window.i18n.t("message.load_error") || "Failed to load club data",
        );
        window.location.href = "dashboard.php";
      }
    })
    .catch((error) => {
      console.error("Error loading club data:", error);
      alert(window.i18n.t("message.load_error") || "Failed to load club data");
      window.location.href = "dashboard.php";
    });
}

/**
 * Populate form fields with club data
 */
function populateFormWithClubData(club) {
  // Store club ID in form
  document.getElementById("registrationForm").dataset.clubId = club.id;

  // Basic fields - Registration number parts
  const regParts = club.reg_number ? club.reg_number.split("/") : [];
  if (regParts.length >= 4) {
    document.getElementById("regNumberManual").value = regParts[3] || "";
  }
  document.getElementById("clubName").value = club.name || "";

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

  // Registration date
  if (club.registration_date) {
    document.getElementById("registrationDate").value = club.registration_date;
    if (club.date_entry_type === "manual") {
      document.getElementById("dateManual").checked = true;
      document.getElementById("manualDateContainer").classList.remove("hidden");
    }
  }

  // Location fields - preload all options first, then set values
  setTimeout(() => {
    if (club.district_id && window.tomSelectInstances.district) {
      // Load divisions first if we have a division_id
      const loadDivisions = club.division_id
        ? fetch(
            `../api/locations.php?type=division&parent_id=${club.district_id}`,
          )
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
            })
        : Promise.resolve();

      // Load GN divisions if we have a gn_division_id
      const loadGNDivisions =
        club.gn_division_id && club.division_id
          ? fetch(
              `../api/locations.php?type=gn_division&parent_id=${club.division_id}`,
            )
              .then((response) => response.json())
              .then((data) => {
                if (data.success) {
                  data.data.forEach((item) => {
                    window.tomSelectInstances.gnDivision.addOption({
                      value: item.id,
                      text: item.name,
                    });
                  });
                }
              })
          : Promise.resolve();

      // Wait for all options to load, then set values
      Promise.all([loadDivisions, loadGNDivisions]).then(() => {
        // Set district value (this will trigger onChange and clear dependent fields)
        window.tomSelectInstances.district.setValue(club.district_id, true);

        // Re-add and set division options
        if (club.division_id) {
          fetch(
            `../api/locations.php?type=division&parent_id=${club.district_id}`,
          )
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                data.data.forEach((item) => {
                  window.tomSelectInstances.division.addOption({
                    value: item.id,
                    text: item.name,
                  });
                });
                window.tomSelectInstances.division.setValue(
                  club.division_id,
                  true,
                );

                // Re-add and set GN division options
                if (club.gn_division_id) {
                  fetch(
                    `../api/locations.php?type=gn_division&parent_id=${club.division_id}`,
                  )
                    .then((response) => response.json())
                    .then((data) => {
                      if (data.success) {
                        data.data.forEach((item) => {
                          window.tomSelectInstances.gnDivision.addOption({
                            value: item.id,
                            text: item.name,
                          });
                        });
                        window.tomSelectInstances.gnDivision.setValue(
                          club.gn_division_id,
                          true,
                        );
                      }
                    });
                }
              }
            });
        }
      });
    }
  }, 1000);

  // Equipment
  if (club.equipment && club.equipment.length > 0) {
    setTimeout(() => {
      // Set equipment IDs in the multi-select
      const equipmentIds = club.equipment.map((e) => e.id);
      if (window.tomSelectInstances.equipmentSelect) {
        window.tomSelectInstances.equipmentSelect.setValue(equipmentIds);

        // Set quantities
        club.equipment.forEach((equip) => {
          const qtyInput = document.getElementById(`eq_qty_${equip.id}`);
          if (qtyInput) {
            qtyInput.value = equip.quantity;
          }
        });
      }
    }, 1500);
  }
}

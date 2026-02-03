/**
 * Internationalization (i18n) Module
 * Handles language switching and translation loading
 */

const i18n = {
  currentLanguage: "si", // Default to Sinhala
  translations: {},

  /**
   * Initialize i18n system
   */
  init() {
    // Get saved language from localStorage or default to Sinhala
    this.currentLanguage = localStorage.getItem("language") || "si";
    this.loadTranslations(this.currentLanguage);
    this.setupLanguageSwitcher();
  },

  /**
   * Load translations for specified language
   * @param {string} lang - Language code (en, si, ta)
   */
  async loadTranslations(lang) {
    // Detect base path from this script's location
    // Script URL format: https://domain.com/SCMS-V2/assets/js/i18n.js
    // We need to extract: /SCMS-V2
    const basePathFromScript = this.getBasePath();

    // Build candidate URLs
    const candidates = [];

    // Primary: Use the detected script-based path (most reliable)
    // This works because we know exactly where the script is and can deduce where assets are
    candidates.push(`${basePathFromScript}/assets/lang/${lang}.json`);

    console.debug(
      "i18n: Loading",
      lang,
      "translations from base path:",
      basePathFromScript || "(root)",
      "URL candidates:",
      candidates,
    );

    let lastError = null;
    for (const url of candidates) {
      try {
        console.debug("i18n: Attempting fetch:", url);
        const response = await fetch(url);
        if (!response.ok) {
          lastError = new Error(
            `Failed to load ${url} - status ${response.status}`,
          );
          console.debug("i18n: Load failed, status:", response.status);
          continue;
        }
        console.debug("✓ i18n: Loaded translations from:", url);
        this.translations = await response.json();
        this.currentLanguage = lang;
        localStorage.setItem("language", lang);
        this.updatePageTranslations();
        this.updateLanguageSwitcher(lang);
        return;
      } catch (error) {
        console.debug("i18n: Fetch error:", error.message);
        lastError = error;
      }
    }

    console.error("i18n: Failed to load translations:", lastError);
    // Fallback to Sinhala if loading fails
    if (lang !== "si") {
      this.loadTranslations("si");
    }
  },

  /**
   * Calculate the base application path from this script's location
   * Example: If script is at https://example.com/SCMS-V2/assets/js/i18n.js
   * This returns: /SCMS-V2
   * @returns {string} Base application path (e.g., "" for root or "/SCMS-V2" for subdirectory)
   */
  getBasePath() {
    // Get this script's URL
    const scriptTag =
      document.currentScript ||
      document.querySelector('script[src*="i18n.js"]');

    if (!scriptTag || !scriptTag.src) {
      console.warn("i18n: Could not determine script URL, using root");
      return "";
    }

    const scriptUrl = scriptTag.src;
    console.debug("i18n: Script URL:", scriptUrl);

    // Parse the pathname from the full URL
    try {
      const urlObj = new URL(scriptUrl);
      let pathname = urlObj.pathname;

      // Remove trailing filename: /SCMS-V2/assets/js/i18n.js -> /SCMS-V2/assets/js
      pathname = pathname.substring(0, pathname.lastIndexOf("/"));

      // Remove /assets/js: /SCMS-V2/assets/js -> /SCMS-V2
      pathname = pathname.substring(0, pathname.lastIndexOf("/"));
      pathname = pathname.substring(0, pathname.lastIndexOf("/"));

      // Remove trailing slash if present
      if (pathname.endsWith("/")) {
        pathname = pathname.substring(0, pathname.length - 1);
      }

      console.debug("i18n: Calculated base path:", pathname || "(root)");
      return pathname;
    } catch (error) {
      console.error("i18n: Error calculating base path:", error);
      return "";
    }
  },

  /**
   * Get translation for a key
   * @param {string} key - Translation key
   * @returns {string} Translated text
   */
  t(key) {
    return this.translations[key] || key;
  },

  /**
   * Update all elements with data-i18n attribute
   */
  updatePageTranslations() {
    // Update elements with data-i18n attribute
    document.querySelectorAll("[data-i18n]").forEach((element) => {
      const key = element.getAttribute("data-i18n");
      const translation = this.t(key);

      // Check if it's an input/textarea with placeholder
      if (element.tagName === "INPUT" || element.tagName === "TEXTAREA") {
        if (element.hasAttribute("placeholder")) {
          element.setAttribute("placeholder", translation);
        }
      } else if (element.tagName === "SELECT") {
        // Handle select elements - update first option if it's a placeholder
        const firstOption = element.querySelector('option[value=""]');
        if (firstOption) {
          firstOption.textContent = translation;
        }
      } else {
        // Regular text content
        element.textContent = translation;
      }
    });

    // Update elements with data-i18n-placeholder attribute
    document.querySelectorAll("[data-i18n-placeholder]").forEach((element) => {
      const key = element.getAttribute("data-i18n-placeholder");
      element.setAttribute("placeholder", this.t(key));
    });

    // Update elements with data-i18n-title attribute
    document.querySelectorAll("[data-i18n-title]").forEach((element) => {
      const key = element.getAttribute("data-i18n-title");
      element.setAttribute("title", this.t(key));
    });

    // Update Tom Select placeholders if they exist
    this.updateTomSelectPlaceholders();
  },

  /**
   * Update Tom Select dropdowns with translated placeholders
   */
  updateTomSelectPlaceholders() {
    // This will be called after Tom Select instances are created
    if (window.tomSelectInstances) {
      Object.keys(window.tomSelectInstances).forEach((key) => {
        const instance = window.tomSelectInstances[key];
        const element = instance.input;
        const placeholderKey = element.getAttribute("data-i18n-placeholder");
        if (placeholderKey) {
          instance.settings.placeholder = this.t(placeholderKey);
          instance.control
            .querySelector("input")
            .setAttribute("placeholder", this.t(placeholderKey));
        }
      });
    }
  },

  /**
   * Setup language switcher event listeners
   */
  setupLanguageSwitcher() {
    const languageButtons = document.querySelectorAll("[data-language]");
    languageButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault();
        const lang = button.getAttribute("data-language");
        this.loadTranslations(lang);
      });
    });
  },

  /**
   * Update active state of language switcher
   * @param {string} lang - Current language code
   */
  updateLanguageSwitcher(lang) {
    document.querySelectorAll("[data-language]").forEach((button) => {
      if (button.getAttribute("data-language") === lang) {
        button.classList.add("bg-white", "text-gray-800", "font-semibold");
        button.classList.remove(
          "bg-white/10",
          "text-white",
          "hover:bg-white/20",
        );
      } else {
        button.classList.remove("bg-white", "text-gray-800", "font-semibold");
        button.classList.add("bg-white/10", "text-white", "hover:bg-white/20");
      }
    });
  },

  /**
   * Get translated validation message
   * @param {string} key - Validation key
   * @param {object} params - Parameters to replace in message
   * @returns {string} Translated validation message
   */
  getValidationMessage(key, params = {}) {
    let message = this.t(`validation.${key}`);

    // Replace parameters in message
    Object.keys(params).forEach((param) => {
      message = message.replace(`{${param}}`, params[param]);
    });

    return message;
  },

  /**
   * Show translated error message in element
   * @param {HTMLElement} element - Error message element
   * @param {string} key - Error message key
   */
  showError(element, key) {
    if (element) {
      element.textContent = this.getValidationMessage(key);
      element.classList.remove("hidden");
    }
  },

  /**
   * Hide error message
   * @param {HTMLElement} element - Error message element
   */
  hideError(element) {
    if (element) {
      element.classList.add("hidden");
      element.textContent = "";
    }
  },
};

// Initialize i18n when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => i18n.init());
} else {
  i18n.init();
}

// Export for use in other modules
window.i18n = i18n;

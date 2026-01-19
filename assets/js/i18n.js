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
    try {
      // Use absolute path from site root
      const response = await fetch(`/sports-v2/assets/lang/${lang}.json`);
      if (!response.ok) {
        throw new Error(`Failed to load translations for ${lang}`);
      }
      this.translations = await response.json();
      this.currentLanguage = lang;
      localStorage.setItem("language", lang);
      this.updatePageTranslations();
      this.updateLanguageSwitcher(lang);
    } catch (error) {
      console.error("Error loading translations:", error);
      // Fallback to Sinhala if loading fails
      if (lang !== "si") {
        this.loadTranslations("si");
      }
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
        button.classList.add("bg-blue-600", "text-white");
        button.classList.remove("bg-gray-200", "text-gray-700");
      } else {
        button.classList.remove("bg-blue-600", "text-white");
        button.classList.add("bg-gray-200", "text-gray-700");
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

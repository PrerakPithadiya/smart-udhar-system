/**
 * Reusable Search Suggestions Component
 * Provides real-time search suggestions for input fields
 */

class SearchSuggestions {
  constructor(inputSelector, options = {}) {
    this.input = document.querySelector(inputSelector);
    if (!this.input) {
      console.error(`Input element with selector "${inputSelector}" not found`);
      return;
    }

    // Default options
    this.options = {
      apiUrl: options.apiUrl || "api/search_customers.php",
      minChars: options.minChars || 1,
      delay: options.delay || 300,
      onSelect: options.onSelect || null,
      suggestionTemplate: (
        options.suggestionTemplate || this.defaultTemplate
      ).bind(this),
      maxSuggestions: options.maxSuggestions || 10,
      searchButton: options.searchButton || null, // New option for search button selector
    };

    this.suggestionsContainer = null;
    this.currentFocus = -1;
    this.timeout = null;
    this.searchButton = null;

    this.init();
  }

  init() {
    // Create suggestions container
    this.createSuggestionsContainer();

    // Add event listeners
    this.input.addEventListener("input", (e) => this.handleInput(e));
    this.input.addEventListener("keydown", (e) => this.handleKeydown(e));
    this.input.addEventListener("blur", (e) =>
      setTimeout(() => this.closeAllLists(), 200)
    );
    this.input.addEventListener("focus", (e) => this.handleFocus(e));

    // Handle search button if provided
    if (this.options.searchButton) {
      this.searchButton = document.querySelector(this.options.searchButton);
      if (this.searchButton) {
        this.searchButton.addEventListener("click", (e) =>
          this.handleSearchButton(e)
        );
      }
    }
  }

  createSuggestionsContainer() {
    this.suggestionsContainer = document.createElement("div");
    this.suggestionsContainer.setAttribute(
      "id",
      this.input.id + "-suggestions"
    );
    this.suggestionsContainer.classList.add("search-suggestions-container");
    this.suggestionsContainer.style.display = "none";
    this.input.parentNode.style.position = "relative";
    this.input.parentNode.appendChild(this.suggestionsContainer);
  }

  handleInput(e) {
    clearTimeout(this.timeout);

    const query = e.target.value;

    if (query.length < this.options.minChars) {
      this.closeAllLists();
      return;
    }

    // Add delay to avoid too many API calls
    this.timeout = setTimeout(() => {
      this.fetchSuggestions(query);
    }, this.options.delay);
  }

  handleFocus(e) {
    const query = e.target.value;
    if (query.length >= this.options.minChars) {
      this.fetchSuggestions(query);
    }
  }

  handleSearchButton(e) {
    e.preventDefault();
    const query = this.input.value.trim();
    if (query.length >= this.options.minChars) {
      this.fetchSuggestions(query);
    } else {
      // If empty or too short, hide suggestions
      this.closeAllLists();
    }
  }

  handleKeydown(e) {
    if (!this.suggestionsContainer) return;

    const items = this.suggestionsContainer.querySelectorAll(
      ".search-suggestion-item"
    );

    switch (e.key) {
      case "ArrowDown":
        e.preventDefault();
        this.currentFocus++;
        this.addActive(items);
        break;
      case "ArrowUp":
        e.preventDefault();
        this.currentFocus--;
        this.addActive(items);
        break;
      case "Enter":
        e.preventDefault();
        if (this.currentFocus > -1 && items[this.currentFocus]) {
          items[this.currentFocus].click();
        }
        break;
      case "Escape":
        this.closeAllLists();
        break;
    }
  }

  async fetchSuggestions(query) {
    try {
      const separator = this.options.apiUrl.includes("?") ? "&" : "?";
      const response = await fetch(
        `${this.options.apiUrl}${separator}q=${encodeURIComponent(query)}`
      );
      const data = await response.json();

      if (data.error) {
        console.error("Error fetching suggestions:", data.error);
        this.closeAllLists();
        return;
      }

      this.showSuggestions(data.suggestions);
    } catch (error) {
      console.error("Error fetching suggestions:", error);
      this.closeAllLists();
    }
  }

  showSuggestions(suggestions) {
    // Clear previous suggestions
    this.suggestionsContainer.innerHTML = "";
    this.currentFocus = -1;

    if (!suggestions || suggestions.length === 0) {
      this.closeAllLists();
      return;
    }

    // Limit the number of suggestions
    const limitedSuggestions = suggestions.slice(
      0,
      this.options.maxSuggestions
    );

    limitedSuggestions.forEach((suggestion, index) => {
      const item = document.createElement("div");
      item.classList.add("search-suggestion-item", "group");

      // Use custom template or default
      item.innerHTML = this.options.suggestionTemplate(suggestion);

      // Prevention of focus loss on mousedown
      item.addEventListener("mousedown", (e) => {
        e.preventDefault();
      });

      // Actual selection on click (works for mouse and Enter key)
      item.addEventListener("click", (e) => {
        e.stopPropagation();
        this.selectSuggestion(suggestion);
      });

      item.addEventListener("mouseover", () => {
        this.removeActive();
        this.currentFocus = index;
        this.addActive([item]);
      });

      this.suggestionsContainer.appendChild(item);
    });

    this.suggestionsContainer.style.display = "block";
  }

  defaultTemplate(suggestion) {
    let balanceText = "";
    if (suggestion.balance !== undefined) {
      const balance = parseFloat(suggestion.balance);
      let balanceClass = "text-slate-400";
      let balanceBg = "bg-slate-50";
      let statusIcon = "solar:check-circle-bold-duotone";

      if (balance > 0) {
        balanceClass = "text-rose-500";
        balanceBg = "bg-rose-50/80";
        statusIcon = "solar:danger-bold-duotone";
      } else if (balance < 0) {
        balanceClass = "text-emerald-500";
        balanceBg = "bg-emerald-50/80";
        statusIcon = "solar:verified-check-bold-duotone";
      }

      let formattedBalance = `₹${Math.abs(balance).toLocaleString("en-IN", {
        minimumFractionDigits: 2,
      })}`;
      let label =
        balance > 0
          ? "Debt Presence"
          : balance < 0
          ? "Credit Reserve"
          : "Balanced";

      balanceText = `<div class="flex flex-col items-end shrink-0">
                      <div class="flex items-center gap-1.5 ${balanceBg} ${balanceClass} px-3 py-1.5 rounded-xl border border-current/10 shadow-sm shadow-current/5">
                        <iconify-icon icon="${statusIcon}" class="text-lg"></iconify-icon>
                        <span class="text-[13px] font-black tracking-tighter">${formattedBalance}</span>
                      </div>
                      <span class="text-[8px] font-black uppercase tracking-[0.2em] text-slate-300 mt-1.5">${label}</span>
                    </div>`;
    }

    const initials = suggestion.name.substring(0, 1).toUpperCase();
    const colors = [
      "from-indigo-500 to-violet-600",
      "from-emerald-500 to-teal-600",
      "from-amber-500 to-orange-600",
      "from-rose-500 to-pink-600",
      "from-sky-500 to-blue-600",
    ];
    const gradient = colors[suggestion.id % colors.length];

    return `
            <div class="flex items-center gap-4 flex-grow overflow-hidden">
                <div class="relative shrink-0">
                  <div class="w-12 h-12 rounded-2xl bg-gradient-to-br ${gradient} flex items-center justify-center text-white font-black text-lg shadow-lg shadow-current/20 group-hover:scale-105 transition-transform duration-500">
                      ${initials}
                  </div>
                  <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-white rounded-full flex items-center justify-center shadow-sm">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-200"></div>
                  </div>
                </div>
                <div class="flex flex-col min-w-0">
                    <h4 class="text-[15px] font-black text-slate-800 tracking-tight leading-none mb-2 truncate group-hover:text-indigo-600 transition-colors">
                      ${this.highlightMatch(suggestion.name, this.input.value)}
                    </h4>
                    <div class="flex items-center gap-3">
                      ${
                        suggestion.mobile
                          ? `
                        <span class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400">
                          <iconify-icon icon="solar:phone-bold-duotone" class="text-indigo-400 text-sm"></iconify-icon>
                          ${suggestion.mobile}
                        </span>`
                          : ""
                      }
                      ${
                        suggestion.email
                          ? `
                        <span class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400">
                          <iconify-icon icon="solar:letter-bold-duotone" class="text-indigo-400 text-sm"></iconify-icon>
                          <span class="truncate max-w-[120px]">${suggestion.email}</span>
                        </span>`
                          : ""
                      }
                    </div>
                </div>
            </div>
            ${balanceText}
            <div class="absolute right-4 opacity-0 -translate-x-4 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300 pointer-events-none">
              <iconify-icon icon="solar:alt-arrow-right-bold-duotone" class="text-indigo-600 text-2xl"></iconify-icon>
            </div>
        `;
  }

  highlightMatch(text, query) {
    if (!query) return text;
    // Escaping query for regex
    const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    const regex = new RegExp(`(${escapedQuery})`, "gi");
    return text.replace(
      regex,
      `<span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600 bg-no-repeat bg-[length:100%_2px] bg-bottom font-black">$1</span>`
    );
  }

  selectSuggestion(suggestion) {
    this.input.value = suggestion.name;

    // Hide suggestions
    this.closeAllLists();

    // Trigger custom onSelect callback if provided
    // If onSelect handles navigation, we should avoid form submission
    if (this.options.onSelect) {
      this.options.onSelect(suggestion);
      return; // Exit early if we have a custom handler
    }

    // AUTO-SUBMIT FORM: Filter table to only this customer
    const form = this.input.closest("form");
    if (form) {
      form.submit();
    } else if (suggestion.id) {
      // Fallback for cases without a form
      const currentUrl = new URL(window.location.href);
      currentUrl.searchParams.set("search", suggestion.name);
      currentUrl.searchParams.set("action", "list");
      window.location.href = currentUrl.toString();
    }
  }

  addActive(items) {
    if (!items || items.length === 0) return false;

    this.removeActive();

    if (this.currentFocus >= items.length) {
      this.currentFocus = 0;
    }

    if (this.currentFocus < 0) {
      if (items.length > 0) {
        this.currentFocus = items.length - 1;
      }
    }

    if (items[this.currentFocus]) {
      items[this.currentFocus].classList.add("active");
    }
  }

  removeActive() {
    const items = this.suggestionsContainer.querySelectorAll(
      ".search-suggestion-item"
    );
    items.forEach((item) => {
      item.classList.remove("active");
    });
  }

  closeAllLists() {
    if (this.suggestionsContainer) {
      this.suggestionsContainer.style.display = "none";
    }
    this.currentFocus = -1;
  }
}

// Utility function to initialize search suggestions on multiple inputs
function initializeSearchSuggestions(selector, options = {}) {
  const elements = document.querySelectorAll(selector);
  const instances = [];

  elements.forEach((element, index) => {
    const elementOptions = {
      ...options,
      // Allow per-element options override
      apiUrl: element.getAttribute("data-api-url") || options.apiUrl,
      minChars: element.getAttribute("data-min-chars") || options.minChars,
      delay: element.getAttribute("data-delay") || options.delay,
    };

    const instance = new SearchSuggestions(`#${element.id}`, elementOptions);
    instances.push(instance);
  });

  return instances;
}

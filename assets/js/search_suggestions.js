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
      item.classList.add("search-suggestion-item");

      // Use custom template or default
      item.innerHTML = this.options.suggestionTemplate(suggestion);

      item.addEventListener("click", () => {
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

      if (balance > 0) {
        balanceClass = "text-rose-500";
        balanceBg = "bg-rose-50/50";
      } else if (balance < 0) {
        balanceClass = "text-emerald-500";
        balanceBg = "bg-emerald-50/50";
      }

      let formattedBalance = `₹${Math.abs(balance).toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
      let label = balance > 0 ? "Due" : (balance < 0 ? "Adv" : "Clear");

      balanceText = `<div class="flex flex-col items-end gap-1">
                      <span class="text-xs font-black ${balanceClass} ${balanceBg} px-2 py-1 rounded-lg border border-current/10">${formattedBalance}</span>
                      <span class="text-[8px] font-black uppercase tracking-widest text-slate-300">${label}</span>
                    </div>`;
    }

    const initials = suggestion.name.substring(0, 1).toUpperCase();

    return `
            <div class="flex items-center gap-4 flex-grow">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-black text-sm shadow-sm group-hover:scale-110 transition-transform">
                    ${initials}
                </div>
                <div class="flex flex-col">
                    <strong class="text-sm text-slate-700 tracking-tight">${this.highlightMatch(suggestion.name, this.input.value)}</strong>
                    ${suggestion.mobile ? `<span class="text-[10px] font-bold text-slate-400 flex items-center gap-1 mt-0.5"><iconify-icon icon="solar:phone-bold-duotone" class="text-xs"></iconify-icon> ${suggestion.mobile}</span>` : ""}
                </div>
            </div>
            ${balanceText}
        `;
  }

  highlightMatch(text, query) {
    if (!query) return text;
    const regex = new RegExp(`(${query})`, "gi");
    return text.replace(regex, `<span class="text-indigo-600 border-b-2 border-indigo-200">$1</span>`);
  }

  selectSuggestion(suggestion) {
    this.input.value = suggestion.name;

    // Hide suggestions
    this.closeAllLists();

    // Trigger custom onSelect callback if provided
    if (this.options.onSelect) {
      this.options.onSelect(suggestion);
    }

    // AUTO-SUBMIT FORM: Filter table to only this customer
    const form = this.input.closest('form');
    if (form) {
      form.submit();
    } else if (suggestion.id) {
      // Fallback for cases without a form
      const currentUrl = new URL(window.location.href);
      currentUrl.searchParams.set('search', suggestion.name);
      currentUrl.searchParams.set('action', 'list');
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
      items[this.currentFocus].style.backgroundColor = "#e9ecef";
    }
  }

  removeActive() {
    const items = this.suggestionsContainer.querySelectorAll(
      ".search-suggestion-item"
    );
    items.forEach((item) => {
      item.classList.remove("active");
      item.style.backgroundColor = "";
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

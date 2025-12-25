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
            apiUrl: options.apiUrl || '/api/search_customers.php',
            minChars: options.minChars || 1,
            delay: options.delay || 300,
            onSelect: options.onSelect || null,
            suggestionTemplate: options.suggestionTemplate || this.defaultTemplate,
            maxSuggestions: options.maxSuggestions || 10
        };

        this.suggestionsContainer = null;
        this.currentFocus = -1;
        this.timeout = null;

        this.init();
    }

    init() {
        // Create suggestions container
        this.createSuggestionsContainer();

        // Add event listeners
        this.input.addEventListener('input', (e) => this.handleInput(e));
        this.input.addEventListener('keydown', (e) => this.handleKeydown(e));
        this.input.addEventListener('blur', (e) => setTimeout(() => this.closeAllLists(), 200));
        this.input.addEventListener('focus', (e) => this.handleFocus(e));
    }

    createSuggestionsContainer() {
        this.suggestionsContainer = document.createElement('div');
        this.suggestionsContainer.setAttribute('id', this.input.id + '-suggestions');
        this.suggestionsContainer.classList.add('search-suggestions-container');
        this.suggestionsContainer.style.cssText = `
            position: absolute;
            border: 1px solid #ddd;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
        `;
        this.input.parentNode.style.position = 'relative';
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

    handleKeydown(e) {
        if (!this.suggestionsContainer) return;

        const items = this.suggestionsContainer.querySelectorAll('.search-suggestion-item');
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.currentFocus++;
                this.addActive(items);
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.currentFocus--;
                this.addActive(items);
                break;
            case 'Enter':
                e.preventDefault();
                if (this.currentFocus > -1 && items[this.currentFocus]) {
                    items[this.currentFocus].click();
                }
                break;
            case 'Escape':
                this.closeAllLists();
                break;
        }
    }

    async fetchSuggestions(query) {
        try {
            const response = await fetch(`${this.options.apiUrl}?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.error) {
                console.error('Error fetching suggestions:', data.error);
                this.closeAllLists();
                return;
            }

            this.showSuggestions(data.suggestions);
        } catch (error) {
            console.error('Error fetching suggestions:', error);
            this.closeAllLists();
        }
    }

    showSuggestions(suggestions) {
        // Clear previous suggestions
        this.suggestionsContainer.innerHTML = '';
        this.currentFocus = -1;

        if (!suggestions || suggestions.length === 0) {
            this.closeAllLists();
            return;
        }

        // Limit the number of suggestions
        const limitedSuggestions = suggestions.slice(0, this.options.maxSuggestions);

        limitedSuggestions.forEach((suggestion, index) => {
            const item = document.createElement('div');
            item.classList.add('search-suggestion-item');
            item.style.cssText = `
                padding: 10px;
                cursor: pointer;
                border-bottom: 1px solid #eee;
                display: flex;
                justify-content: space-between;
                align-items: center;
            `;
            
            // Use custom template or default
            item.innerHTML = this.options.suggestionTemplate(suggestion);
            
            item.addEventListener('click', () => {
                this.selectSuggestion(suggestion);
            });

            item.addEventListener('mouseover', () => {
                this.removeActive();
                this.currentFocus = index;
                this.addActive([item]);
            });

            this.suggestionsContainer.appendChild(item);
        });

        this.suggestionsContainer.style.display = 'block';
    }

    defaultTemplate(suggestion) {
        let balanceText = '';
        if (suggestion.balance !== undefined) {
            const balance = parseFloat(suggestion.balance);
            let balanceClass = 'balance-zero';
            if (balance > 0) {
                balanceClass = 'balance-negative'; // Due
            } else if (balance < 0) {
                balanceClass = 'balance-positive'; // Advance
            }
            
            let formattedBalance = `₹${Math.abs(balance).toFixed(2)}`;
            if (balance > 0) {
                formattedBalance += ' (Due)';
            } else if (balance < 0) {
                formattedBalance += ' (Advance)';
            }
            
            balanceText = `<span class="suggestion-balance ${balanceClass}">${formattedBalance}</span>`;
        }

        let contactInfo = '';
        if (suggestion.mobile) {
            contactInfo += `<small class="text-muted">📱 ${suggestion.mobile}</small>`;
        }
        if (suggestion.email) {
            if (contactInfo) contactInfo += '<br>';
            contactInfo += `<small class="text-muted">📧 ${suggestion.email}</small>`;
        }

        return `
            <div>
                <strong>${this.highlightMatch(suggestion.name, this.input.value)}</strong>
                ${contactInfo ? `<br>${contactInfo}` : ''}
            </div>
            ${balanceText}
        `;
    }

    highlightMatch(text, query) {
        if (!query) return text;
        
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    selectSuggestion(suggestion) {
        this.input.value = suggestion.name;
        
        // Hide suggestions
        this.closeAllLists();
        
        // Trigger custom onSelect callback if provided
        if (this.options.onSelect) {
            this.options.onSelect(suggestion);
        }
        
        // Optionally redirect to customer details page
        if (suggestion.id) {
            // You can customize this behavior based on your needs
            // window.location.href = `customers.php?action=view&id=${suggestion.id}`;
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
            items[this.currentFocus].classList.add('active');
            items[this.currentFocus].style.backgroundColor = '#e9ecef';
        }
    }

    removeActive() {
        const items = this.suggestionsContainer.querySelectorAll('.search-suggestion-item');
        items.forEach(item => {
            item.classList.remove('active');
            item.style.backgroundColor = '';
        });
    }

    closeAllLists() {
        if (this.suggestionsContainer) {
            this.suggestionsContainer.style.display = 'none';
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
            apiUrl: element.getAttribute('data-api-url') || options.apiUrl,
            minChars: element.getAttribute('data-min-chars') || options.minChars,
            delay: element.getAttribute('data-delay') || options.delay
        };
        
        const instance = new SearchSuggestions(`#${element.id}`, elementOptions);
        instances.push(instance);
    });
    
    return instances;
}
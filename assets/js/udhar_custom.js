// File: smart-udhar-system/assets/js/udhar_custom.js

// Global variables initialized from PHP data
let itemCounter = 0;
// Note: ITEMS_LIST and PRE_SELECTED_ITEM_ID should be defined in the main PHP file

// Initialize customer search suggestions
document.addEventListener("DOMContentLoaded", function () {
  // Support deep-link focus from dashboard shortcut (e.g., ?focus=customer_search)
  function safeFocus(input) {
    if (!input) return;
    try {
      input.focus({ preventScroll: true });
    } catch (e) {
      // Older browsers don't support focus options
      input.focus();
    }
    if (typeof input.select === "function") input.select();
  }

  function focusCustomerSearch(retriesLeft = 6) {
    const input = document.getElementById("customer_search");
    if (!input) {
      if (retriesLeft > 0)
        setTimeout(() => focusCustomerSearch(retriesLeft - 1), 150);
      return;
    }

    input.scrollIntoView({ behavior: "smooth", block: "center" });
    safeFocus(input);

    // Some scripts (bootstrap/search suggestions) may steal focus right after load.
    // Retry a few times to make sure the cursor ends up in the search box.
    if (retriesLeft > 0) {
      setTimeout(() => {
        safeFocus(input);
      }, 200);

      setTimeout(() => {
        safeFocus(input);
      }, 600);

      setTimeout(() => {
        safeFocus(input);
      }, 1100);
    }
  }

  try {
    const params = new URLSearchParams(window.location.search);
    const focusTarget = params.get("focus");
    if (focusTarget === "customer_search") {
      const LOCK_MS = 2000;
      const lockUntil = Date.now() + LOCK_MS;

      // Keep focus on customer search briefly (other scripts may steal focus right after load)
      const focusLockHandler = function (e) {
        const input = document.getElementById("customer_search");
        if (!input) return;

        if (Date.now() > lockUntil) {
          document.removeEventListener("focusin", focusLockHandler, true);
          return;
        }

        if (e && e.target === input) return;

        // Re-focus on next tick to avoid fighting the current focus event synchronously
        setTimeout(() => {
          if (Date.now() > lockUntil) return;
          safeFocus(input);
        }, 0);
      };

      document.addEventListener("focusin", focusLockHandler, true);

      focusCustomerSearch();
      window.addEventListener("load", () => focusCustomerSearch(2), {
        once: true,
      });

      // Ensure focus lock is removed even if focusin doesn't fire
      setTimeout(() => {
        document.removeEventListener("focusin", focusLockHandler, true);
      }, LOCK_MS + 50);
    }
  } catch (e) {
    // Ignore URL parsing errors
  }

  if (document.getElementById("customer_search")) {
    const customerSearch = new SearchSuggestions("#customer_search", {
      apiUrl: "api/search_customers.php",
      minChars: 2,
      delay: 300,
      maxSuggestions: 10,
      searchButton: "#customer_search_btn",
      onSelect: function (customer) {
        const infoDiv = document.getElementById("customer_info");
        document.getElementById("customer_id").value = customer.id;
        document.getElementById("selected_customer_name").textContent =
          customer.name;
        document.getElementById("selected_customer_mobile").textContent =
          customer.mobile || "";
        document.getElementById("selected_customer_address").textContent =
          customer.address || "Address not added";

        // Display balance
        const balance = parseFloat(customer.balance) || 0;
        const balanceBadge = document.getElementById(
          "selected_customer_balance",
        );
        balanceBadge.textContent = "₹" + Math.abs(balance).toFixed(2);

        if (balance > 0) {
          balanceBadge.className = "badge bg-danger";
          balanceBadge.textContent += " (To pay)";
        } else if (balance < 0) {
          balanceBadge.className = "badge bg-success";
          balanceBadge.textContent += " (Extra paid)";
        } else {
          balanceBadge.className = "badge bg-secondary";
          balanceBadge.textContent += " (No balance)";
        }

        infoDiv.style.display = "block";
      },
    });

    // Dynamically enable/disable search button
    const customerSearchInput = document.getElementById("customer_search");
    const customerSearchBtn = document.getElementById("customer_search_btn");

    customerSearchInput.addEventListener("input", function () {
      const query = this.value.trim();
      customerSearchBtn.disabled = query.length < 2;

      // If input is empty, clear selected customer and hide info
      if (query.length === 0) {
        const customerIdInput = document.getElementById("customer_id");
        const infoDiv = document.getElementById("customer_info");
        if (customerIdInput) customerIdInput.value = "";
        if (infoDiv) infoDiv.style.display = "none";
      }
    });

    // Optional: Add "Add New Customer" button functionality
    customerSearchInput.addEventListener("keydown", function (e) {
      if (
        e.key === "Enter" &&
        customerSearchInput.value.trim() !== "" &&
        !document.getElementById("customer_id").value
      ) {
        e.preventDefault();
        if (
          confirm(
            `Customer "${customerSearchInput.value}" not found. Do you want to add this customer?`,
          )
        ) {
          window.location.href = `customers.php?action=add&name=${encodeURIComponent(customerSearchInput.value)}`;
        }
      }
    });
  }

  // Udhar list page search suggestions (search by customer)
  if (document.getElementById("udhar-search")) {
    const udharSearch = new SearchSuggestions("#udhar-search", {
      apiUrl: "api/search_customers.php",
      minChars: 2,
      delay: 250,
      maxSuggestions: 10,
    });

    // Prevent overlap with the stats cards below by moving the suggestions container
    // out of the positioned search box and placing it after the search box.
    const searchInput = document.getElementById("udhar-search");
    const searchBox = searchInput?.closest(".udhar-search-box");
    const suggestionsEl = document.getElementById("udhar-search-suggestions");
    if (searchBox && suggestionsEl && searchBox.parentNode) {
      searchBox.parentNode.insertBefore(suggestionsEl, searchBox.nextSibling);
      suggestionsEl.style.position = "static";
      suggestionsEl.style.marginTop = "10px";
    }
  }

  // Enter/Escape navigation on Udhar entry form
  function getFocusableFields(formEl) {
    if (!formEl) return [];
    const selectors = [
      'input:not([type="hidden"])',
      "select",
      "textarea",
      "button",
      "[tabindex]",
    ].join(",");

    return Array.from(formEl.querySelectorAll(selectors)).filter((el) => {
      if (!el) return false;
      if (el.disabled) return false;
      if (el.getAttribute("aria-disabled") === "true") return false;
      if (el.getAttribute("tabindex") === "-1") return false;
      if (el.type && el.type.toLowerCase() === "hidden") return false;
      if (el.offsetParent === null && el.tagName.toLowerCase() !== "option")
        return false;
      return true;
    });
  }

  function suggestionsOpenForInput(inputEl) {
    if (!inputEl || !inputEl.id) return false;
    const container = document.getElementById(inputEl.id + "-suggestions");
    if (!container) return false;
    return (
      container.style.display !== "none" && container.innerHTML.trim() !== ""
    );
  }

  document.addEventListener(
    "keydown",
    function (e) {
      if (e.defaultPrevented) return;
      if (e.ctrlKey || e.altKey || e.metaKey) return;
      if (e.key !== "Enter" && e.key !== "Escape") return;

      const target = e.target;
      if (!target || !(target instanceof HTMLElement)) return;

      // Only apply inside Udhar add/edit forms
      const formEl = target.closest("form");
      if (!formEl) return;
      if (formEl.id !== "udharForm" && !formEl.closest(".bill-form-container"))
        return;

      const tag = target.tagName.toLowerCase();

      // Do not override textarea behavior
      if (tag === "textarea") return;

      // Don't interfere with suggestion dropdowns (Enter selects item, Escape closes list)
      if (tag === "input" && suggestionsOpenForInput(target)) return;

      // If a button is focused, keep normal behavior
      if (tag === "button") return;

      // Enter should behave like Tab, Escape like Shift+Tab
      e.preventDefault();

      const focusables = getFocusableFields(formEl);
      const idx = focusables.indexOf(target);
      if (idx === -1) return;

      const dir = e.key === "Enter" ? 1 : -1;
      let nextIdx = idx + dir;

      if (nextIdx < 0) nextIdx = 0;
      if (nextIdx >= focusables.length) nextIdx = focusables.length - 1;

      const nextEl = focusables[nextIdx];
      if (!nextEl || nextEl === target) return;

      try {
        nextEl.focus();
      } catch (err) {
        // ignore
      }

      if (nextEl.tagName.toLowerCase() === "input") {
        const t = nextEl.getAttribute("type");
        if (
          !t ||
          ["text", "search", "number", "tel", "email", "date"].includes(
            t.toLowerCase(),
          )
        ) {
          if (typeof nextEl.select === "function") nextEl.select();
        }
      }
    },
    true,
  );
});

// Add item row dynamically
function addItemRow(itemData = null) {
  const tbody = document.getElementById("itemsBody");
  const row = document.createElement("tr");
  const currIndex = itemCounter;
  row.id = "itemRow_" + currIndex;

  // Default values
  const defaultItem = itemData || {
    id: "",
    item_name: "",
    hsn_code: "",
    price: "0.00",
    unit: "PCS",
  };

  row.innerHTML = `
        <td>
            <div class="position-relative">
                <input type="text" class="form-control form-control-sm item-search-input" 
                       id="item_search_${itemCounter}" 
                       placeholder="Type item name..." 
                       value="${defaultItem.item_name}"
                       autocomplete="off"
                       required>
                <input type="hidden" name="items[${itemCounter}][item_id]" 
                       value="${defaultItem.id}" class="item-id-input">
                <input type="hidden" name="items[${itemCounter}][item_name]" 
                       value="${defaultItem.item_name}" class="item-name">
                <input type="hidden" name="items[${itemCounter}][cgst_rate]" value="0">
                <input type="hidden" name="items[${itemCounter}][sgst_rate]" value="0">
                <input type="hidden" name="items[${itemCounter}][igst_rate]" value="0">
            </div>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm item-hsn" 
                   name="items[${itemCounter}][hsn_code]" 
                   value="${defaultItem.hsn_code}" 
                   placeholder="HSN" readonly>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm quantity" 
                   name="items[${itemCounter}][quantity]" 
                   value="1" step="0.01" min="0.01" 
                   onchange="calculateItemTotal(${itemCounter})" required>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm item-unit text-center" 
                   name="items[${itemCounter}][unit]" 
                   value="${defaultItem.unit}" 
                   readonly style="background-color: #f8f9fa;">
        </td>
        <td>
            <div class="input-group input-group-sm">
                <span class="input-group-text">₹</span>
                <input type="number" class="form-control price" 
                       name="items[${itemCounter}][price]" 
                       value="${defaultItem.price}" step="0.01" min="0.01" 
                       onchange="calculateItemTotal(${itemCounter})" required>
            </div>
        </td>
        <td class="text-end fw-bold">
            ₹<span class="item-total">0.00</span>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" 
                    onclick="removeItemRow(${itemCounter})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
                        `;

  tbody.appendChild(row);
  itemCounter++;
  calculateItemTotal(currIndex);
  calculateTotals();

  // Initialize search suggestions for the new row
  new SearchSuggestions(`#item_search_${currIndex}`, {
    apiUrl: "api/search_items.php",
    minChars: 2,
    delay: 200,
    maxSuggestions: 8,
    suggestionTemplate: function (item) {
      return `
                        <div>
                            <strong>${this.highlightMatch(item.item_name, this.input.value)}</strong>
                            <br><small class="text-muted">Code: ${item.item_code || "N/A"} | HSN: ${item.hsn_code || "N/A"}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-light text-dark">₹${parseFloat(item.price).toFixed(2)}</span>
                            <br><small class="text-muted">${item.unit}</small>
                        </div>
                    `;
    },
    onSelect: function (item) {
      const row = document.getElementById("itemRow_" + currIndex);
      row.querySelector(".item-id-input").value = item.id;
      row.querySelector(".item-name").value = item.item_name;
      row.querySelector(".item-hsn").value = item.hsn_code;
      row.querySelector(".item-unit").value = item.unit;
      row.querySelector(".price").value = item.price;

      calculateItemTotal(currIndex);
    },
  });

  // Apply random color if feature is enabled
  if (document.getElementById("toggleRowColors").checked) {
    applyRandomColorToRow(row);
  }
}

// Random Color Generator for Rows
function getRandomPastelColor(avoidHue = -1) {
  let hue;
  let attempts = 0;
  // Ensure the new hue is at least 60 degrees away from the previous one for clear distinction
  // We use circular distance to handle the 0-360 wraparound
  do {
    hue = Math.floor(Math.random() * 360);

    let isDistinct = true;
    if (avoidHue !== -1) {
      let diff = Math.abs(hue - avoidHue);
      if (diff > 180) diff = 360 - diff;
      if (diff < 60) isDistinct = false;
    }

    if (isDistinct) break;
    attempts++;
  } while (attempts < 15);

  return hue;
}

function applyRandomColorToRow(row) {
  const previousRow = row.previousElementSibling;
  const avoidHue =
    previousRow && previousRow.dataset.hue
      ? parseInt(previousRow.dataset.hue)
      : -1;

  const hue = getRandomPastelColor(avoidHue);
  row.dataset.hue = hue;
  row.style.backgroundColor = `hsla(${hue}, 75%, 95%, 0.85)`;
  row.classList.add("colored-row");
}

// Calculate item total
function calculateItemTotal(rowIndex) {
  const row = document.getElementById("itemRow_" + rowIndex);
  const quantity = parseFloat(row.querySelector(".quantity").value) || 0;
  const price = parseFloat(row.querySelector(".price").value) || 0;

  const itemTotal = quantity * price;
  row.querySelector(".item-total").textContent = itemTotal.toFixed(2);

  calculateTotals();
}

// Remove item row
function removeItemRow(rowIndex) {
  const row = document.getElementById("itemRow_" + rowIndex);
  row.remove();
  calculateTotals();
}

// Calculate all totals
function calculateTotals() {
  let subTotal = 0;

  // Calculate from all item rows
  for (let i = 0; i < itemCounter; i++) {
    const row = document.getElementById("itemRow_" + i);
    if (row) {
      const quantity = parseFloat(row.querySelector(".quantity").value) || 0;
      const price = parseFloat(row.querySelector(".price").value) || 0;

      const itemTotal = quantity * price;
      subTotal += itemTotal;
    }
  }

  // Update display for fixed subtotal
  document.getElementById("subTotal").textContent = subTotal.toFixed(2);

  // Recalculate grand total
  calculateGrandTotal();
}

function calculateGrandTotal() {
  const subTotal =
    parseFloat(document.getElementById("subTotal").textContent) || 0;
  const discount = parseFloat(document.getElementById("discount").value) || 0;
  const transportation =
    parseFloat(document.getElementById("transportation_charge").value) || 0;
  const roundOff = parseFloat(document.getElementById("round_off").value) || 0;

  const grandTotal = subTotal - discount + transportation + roundOff;
  document.getElementById("grandTotal").textContent = grandTotal.toFixed(2);
}

// Show items modal for selection
function addItemFromList() {
  const modal = new bootstrap.Modal(document.getElementById("itemsModal"));
  modal.show();
}

function safeCloseItemsModal() {
  const modalEl = document.getElementById("itemsModal");
  if (!modalEl) return;

  try {
    const instance =
      bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    instance.hide();
  } catch (e) {
    // ignore
  }

  // Ensure backdrop is removed and page becomes interactive again
  document.body.classList.remove("modal-open");
  document.body.style.removeProperty("overflow");
  document.body.style.removeProperty("padding-right");
  document.querySelectorAll(".modal-backdrop").forEach((b) => b.remove());
}

// Add selected items from modal
function addSelectedItems() {
  try {
    const checkboxes = document.querySelectorAll(".item-checkbox:checked");

    // Build an ID -> item map once
    const itemsById = new Map();
    (Array.isArray(window.ITEMS_LIST) ? window.ITEMS_LIST : []).forEach(
      (it) => {
        if (it && typeof it.id !== "undefined") {
          itemsById.set(String(it.id), it);
        }
      },
    );

    checkboxes.forEach((checkbox) => {
      const itemId = String(checkbox.value || "").trim();
      const itemData = itemsById.get(itemId);
      if (itemData) {
        addItemRow(itemData);
      }
    });
  } finally {
    // Always cleanup so the screen never gets stuck
    safeCloseItemsModal();
    document
      .querySelectorAll(".item-checkbox")
      .forEach((cb) => (cb.checked = false));
  }
}

// Form validation
document.getElementById("udharForm")?.addEventListener("submit", function (e) {
  // Check if customer is selected
  const customerId = document.getElementById("customer_id").value;
  if (!customerId) {
    e.preventDefault();
    alert("Please choose a customer");
    document.getElementById("customer_search").focus();
    return false;
  }

  // Check if at least one item is added
  if (itemCounter === 0) {
    e.preventDefault();
    alert("Please add at least one item");
    return false;
  }

  // Check all items have valid data
  let hasErrors = false;
  for (let i = 0; i < itemCounter; i++) {
    const row = document.getElementById("itemRow_" + i);
    if (row) {
      const itemId = row.querySelector(".item-id-input").value;
      const quantity = row.querySelector(".quantity").value;
      const price = row.querySelector(".price").value;

      if (!itemId || parseFloat(quantity) <= 0 || parseFloat(price) <= 0) {
        hasErrors = true;
        break;
      }
    }
  }

  if (hasErrors) {
    e.preventDefault();
    alert("Please fill item details correctly");
    return false;
  }

  // Calculate totals one more time before submit
  calculateTotals();

  return true;
});

// Delete confirmation
function confirmDelete(id, billNo) {
  if (confirm('Delete bill "' + billNo + '"? This cannot be undone.')) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "";

    const input1 = document.createElement("input");
    input1.type = "hidden";
    input1.name = "udhar_id";
    input1.value = id;

    const input2 = document.createElement("input");
    input2.type = "hidden";
    input2.name = "delete_udhar";
    input2.value = "1";

    form.appendChild(input1);
    form.appendChild(input2);
    document.body.appendChild(form);
    form.submit();
  }
}

// Toggle colorful rows
document
  .getElementById("toggleRowColors")
  ?.addEventListener("change", function () {
    const rows = document.querySelectorAll("#itemsBody tr");
    if (this.checked) {
      rows.forEach((row) => {
        if (!row.classList.contains("colored-row")) {
          applyRandomColorToRow(row);
        }
      });
    } else {
      rows.forEach((row) => {
        row.style.backgroundColor = "";
        row.classList.remove("colored-row");
        delete row.dataset.hue;
      });
    }
  });

// Column Resizing Logic with Local Storage Persistence
document.addEventListener("DOMContentLoaded", function () {
  const tables = document.querySelectorAll(".resizable-table");

  function saveColumnWidths(table) {
    if (!table.id) return;
    const ths = Array.from(table.querySelectorAll("thead th"));
    const widths = ths.map((th) => th.getBoundingClientRect().width);
    localStorage.setItem(`colWidths_${table.id}`, JSON.stringify(widths));
  }

  function applyColumnWidths(table) {
    if (!table.id) return;
    let widths;
    try {
      widths = JSON.parse(localStorage.getItem(`colWidths_${table.id}`));
    } catch (e) {
      return;
    }

    if (Array.isArray(widths)) {
      const ths = table.querySelectorAll("thead th");
      widths.forEach((w, i) => {
        if (ths[i] && w > 30) {
          ths[i].style.width = w + "px";
        }
      });
    }
  }

  tables.forEach((table) => {
    applyColumnWidths(table);
    const cols = table.querySelectorAll("th");

    cols.forEach((col) => {
      const resizer = col.querySelector(".resizer");
      if (!resizer) return;

      let x = 0;
      let w = 0;

      const mouseDownHandler = function (e) {
        x = e.clientX;
        const styles = window.getComputedStyle(col);
        w = parseInt(styles.width, 10);

        document.addEventListener("mousemove", mouseMoveHandler);
        document.addEventListener("mouseup", mouseUpHandler);
        resizer.classList.add("resizing");
      };

      const mouseMoveHandler = function (e) {
        const dx = e.clientX - x;
        col.style.width = `${w + dx}px`;
      };

      const mouseUpHandler = function () {
        document.removeEventListener("mousemove", mouseMoveHandler);
        document.removeEventListener("mouseup", mouseUpHandler);
        resizer.classList.remove("resizing");
        saveColumnWidths(table);
      };

      resizer.addEventListener("mousedown", mouseDownHandler);
    });
  });
});

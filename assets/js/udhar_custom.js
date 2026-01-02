// File: smart-udhar-system/assets/js/udhar_custom.js

// Sidebar toggle for both mobile and desktop
// Sidebar toggle function
function toggleSidebar() {
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");

    sidebar.classList.toggle("closed");
    mainContent.classList.toggle("expanded");
}

// Sidebar toggle button inside sidebar
const sidebarToggleBtn = document.getElementById("sidebarToggle");
if (sidebarToggleBtn) {
    sidebarToggleBtn.addEventListener("click", toggleSidebar);
}

// Floating toggle button (visible when sidebar is closed)
const floatingToggleBtn = document.getElementById("floatingToggle");
if (floatingToggleBtn) {
    floatingToggleBtn.addEventListener("click", toggleSidebar);
}

// Auto-hide sidebar on mobile when clicking outside
document.addEventListener("click", function (event) {
    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.getElementById("sidebarToggle");
    const floatingBtn = document.getElementById("floatingToggle");
    const mainContent = document.querySelector(".main-content");

    if (
        window.innerWidth <= 768 &&
        !sidebar.contains(event.target) &&
        !toggleBtn.contains(event.target) &&
        !floatingBtn.contains(event.target) &&
        !sidebar.classList.contains("closed")
    ) {
        sidebar.classList.add("closed");
        mainContent.classList.add("expanded");
    }
});

// Global variables initialized from PHP data
let itemCounter = 0;
// Note: ITEMS_LIST and PRE_SELECTED_ITEM_ID should be defined in the main PHP file

// Initialize customer search suggestions
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('customer_search')) {
        const customerSearch = new SearchSuggestions('#customer_search', {
            apiUrl: 'api/search_customers.php',
            minChars: 2,
            delay: 300,
            maxSuggestions: 10,
            searchButton: '#customer_search_btn',
            onSelect: function (customer) {
                const infoDiv = document.getElementById('customer_info');
                document.getElementById('customer_id').value = customer.id;
                document.getElementById('selected_customer_name').textContent = customer.name;
                document.getElementById('selected_customer_mobile').textContent = customer.mobile || '';
                document.getElementById('selected_customer_address').textContent = customer.address || 'No address provided';

                // Display balance
                const balance = parseFloat(customer.balance) || 0;
                const balanceBadge = document.getElementById('selected_customer_balance');
                balanceBadge.textContent = '₹' + Math.abs(balance).toFixed(2);

                if (balance > 0) {
                    balanceBadge.className = 'badge bg-danger';
                    balanceBadge.textContent += ' (Due)';
                } else if (balance < 0) {
                    balanceBadge.className = 'badge bg-success';
                    balanceBadge.textContent += ' (Advance)';
                } else {
                    balanceBadge.className = 'badge bg-secondary';
                    balanceBadge.textContent += ' (Clear)';
                }

                infoDiv.style.display = 'block';
            }
        });

        // Dynamically enable/disable search button
        const customerSearchInput = document.getElementById('customer_search');
        const customerSearchBtn = document.getElementById('customer_search_btn');

        customerSearchInput.addEventListener('input', function () {
            const query = this.value.trim();
            customerSearchBtn.disabled = query.length < 2;

            // If input is empty, clear selected customer and hide info
            if (query.length === 0) {
                const customerIdInput = document.getElementById('customer_id');
                const infoDiv = document.getElementById('customer_info');
                if (customerIdInput) customerIdInput.value = '';
                if (infoDiv) infoDiv.style.display = 'none';
            }
        });

        // Optional: Add "Add New Customer" button functionality
        customerSearchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && customerSearchInput.value.trim() !== '' && !document.getElementById('customer_id').value) {
                e.preventDefault();
                if (confirm(`Customer "${customerSearchInput.value}" not found. Would you like to add as a new customer?`)) {
                    window.location.href = `customers.php?action=add&name=${encodeURIComponent(customerSearchInput.value)}`;
                }
            }
        });
    }
});

// Add item row dynamically
function addItemRow(itemData = null) {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    const currIndex = itemCounter;
    row.id = 'itemRow_' + currIndex;

    // Default values
    const defaultItem = itemData || {
        id: '',
        item_name: '',
        hsn_code: '',
        price: '0.00',
        cgst_rate: '2.5',
        sgst_rate: '2.5',
        igst_rate: '0.00',
        unit: 'PCS'
    };

    row.innerHTML = `
        <td>
            <div class="position-relative">
                <input type="text" class="form-control form-control-sm item-search-input" 
                       id="item_search_${itemCounter}" 
                       placeholder="Search Item..." 
                       value="${defaultItem.item_name}"
                       autocomplete="off"
                       required>
                <input type="hidden" name="items[${itemCounter}][item_id]" 
                       value="${defaultItem.id}" class="item-id-input">
                <input type="hidden" name="items[${itemCounter}][item_name]" 
                       value="${defaultItem.item_name}" class="item-name">
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
         <td>
            <div class="d-flex flex-column gap-1">
                <div class="d-flex gap-1">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text px-1" style="min-width: 20px; font-weight: bold; font-size: 0.7rem;">C</span>
                        <input type="number" class="form-control px-1 cgst-rate" 
                               name="items[${itemCounter}][cgst_rate]" 
                               value="${defaultItem.cgst_rate}" step="0.01" min="0" max="100"
                               onchange="calculateItemTotal(${itemCounter})">
                    </div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text px-1" style="min-width: 20px; font-weight: bold; font-size: 0.7rem;">S</span>
                        <input type="number" class="form-control px-1 sgst-rate" 
                               name="items[${itemCounter}][sgst_rate]" 
                               value="${defaultItem.sgst_rate}" step="0.01" min="0" max="100"
                               onchange="calculateItemTotal(${itemCounter})">
                    </div>
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text px-1" style="min-width: 20px; font-weight: bold; font-size: 0.7rem;">I</span>
                    <input type="number" class="form-control px-1 igst-rate" 
                           name="items[${itemCounter}][igst_rate]" 
                           value="${defaultItem.igst_rate}" step="0.01" min="0" max="100"
                           onchange="calculateItemTotal(${itemCounter})">
                </div>
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
        apiUrl: 'api/search_items.php',
        minChars: 2,
        delay: 200,
        maxSuggestions: 8,
        suggestionTemplate: function (item) {
            return `
                        <div>
                            <strong>${this.highlightMatch(item.item_name, this.input.value)}</strong>
                            <br><small class="text-muted">Code: ${item.item_code || 'N/A'} | HSN: ${item.hsn_code || 'N/A'}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-light text-dark">₹${parseFloat(item.price).toFixed(2)}</span>
                            <br><small class="text-muted">${item.unit}</small>
                        </div>
                    `;
        },
        onSelect: function (item) {
            const row = document.getElementById('itemRow_' + currIndex);
            row.querySelector('.item-id-input').value = item.id;
            row.querySelector('.item-name').value = item.item_name;
            row.querySelector('.item-hsn').value = item.hsn_code;
            row.querySelector('.item-unit').value = item.unit;
            row.querySelector('.price').value = item.price;
            row.querySelector('.cgst-rate').value = item.cgst_rate;
            row.querySelector('.sgst-rate').value = item.sgst_rate;
            row.querySelector('.igst-rate').value = item.igst_rate;

            calculateItemTotal(currIndex);
        }
    });

    // Apply random color if feature is enabled
    if (document.getElementById('toggleRowColors').checked) {
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
    const avoidHue = (previousRow && previousRow.dataset.hue) ? parseInt(previousRow.dataset.hue) : -1;

    const hue = getRandomPastelColor(avoidHue);
    row.dataset.hue = hue;
    row.style.backgroundColor = `hsla(${hue}, 75%, 95%, 0.85)`;
    row.classList.add('colored-row');
}


// Calculate item total
function calculateItemTotal(rowIndex) {
    const row = document.getElementById('itemRow_' + rowIndex);
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const price = parseFloat(row.querySelector('.price').value) || 0;
    const cgstRate = parseFloat(row.querySelector('.cgst-rate').value) || 0;
    const sgstRate = parseFloat(row.querySelector('.sgst-rate').value) || 0;
    const igstRate = parseFloat(row.querySelector('.igst-rate').value) || 0;

    const itemTotal = quantity * price;
    row.querySelector('.item-total').textContent = itemTotal.toFixed(2);

    calculateTotals();
}

// Remove item row
function removeItemRow(rowIndex) {
    const row = document.getElementById('itemRow_' + rowIndex);
    row.remove();
    calculateTotals();
}

// Calculate all totals
function calculateTotals() {
    let subTotal = 0;
    let cgstTotal = 0;
    let sgstTotal = 0;
    let igstTotal = 0;

    // Calculate from all item rows
    for (let i = 0; i < itemCounter; i++) {
        const row = document.getElementById('itemRow_' + i);
        if (row) {
            const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const cgstRate = parseFloat(row.querySelector('.cgst-rate').value) || 0;
            const sgstRate = parseFloat(row.querySelector('.sgst-rate').value) || 0;
            const igstRate = parseFloat(row.querySelector('.igst-rate').value) || 0;

            const itemTotal = quantity * price;
            subTotal += itemTotal;

            if (igstRate > 0) {
                igstTotal += (itemTotal * igstRate) / 100;
            } else {
                cgstTotal += (itemTotal * cgstRate) / 100;
                sgstTotal += (itemTotal * sgstRate) / 100;
            }
        }
    }

    // Update display for fixed subtotal
    document.getElementById('subTotal').textContent = subTotal.toFixed(2);

    // Auto-populate editable GST fields
    document.getElementById('cgst_amount').value = cgstTotal.toFixed(2);
    document.getElementById('sgst_amount').value = sgstTotal.toFixed(2);
    document.getElementById('igst_amount').value = igstTotal.toFixed(2);

    // Recalculate grand total
    calculateGrandTotal();
}

function calculateGrandTotal() {
    const subTotal = parseFloat(document.getElementById('subTotal').textContent) || 0;
    const cgst = parseFloat(document.getElementById('cgst_amount').value) || 0;
    const sgst = parseFloat(document.getElementById('sgst_amount').value) || 0;
    const igst = parseFloat(document.getElementById('igst_amount').value) || 0;
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const transportation = parseFloat(document.getElementById('transportation_charge').value) || 0;
    const roundOff = parseFloat(document.getElementById('round_off').value) || 0;

    const grandTotal = subTotal + cgst + sgst + igst - discount + transportation + roundOff;
    document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
}

// Show items modal for selection
function addItemFromList() {
    const modal = new bootstrap.Modal(document.getElementById('itemsModal'));
    modal.show();
}

// Add selected items from modal
function addSelectedItems() {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked');
    checkboxes.forEach(checkbox => {
        const itemData = JSON.parse(checkbox.value);
        addItemRow(itemData);
    });

    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('itemsModal')).hide();

    // Uncheck all checkboxes
    document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = false);
}

// Form validation
document.getElementById('udharForm')?.addEventListener('submit', function (e) {
    // Check if customer is selected
    const customerId = document.getElementById('customer_id').value;
    if (!customerId) {
        e.preventDefault();
        alert('Please select a customer');
        document.getElementById('customer_search').focus();
        return false;
    }

    // Check if at least one item is added
    if (itemCounter === 0) {
        e.preventDefault();
        alert('Please add at least one item to the bill');
        return false;
    }

    // Check all items have valid data
    let hasErrors = false;
    for (let i = 0; i < itemCounter; i++) {
        const row = document.getElementById('itemRow_' + i);
        if (row) {
            const itemId = row.querySelector('.item-id-input').value;
            const quantity = row.querySelector('.quantity').value;
            const price = row.querySelector('.price').value;

            if (!itemId || parseFloat(quantity) <= 0 || parseFloat(price) <= 0) {
                hasErrors = true;
                break;
            }
        }
    }

    if (hasErrors) {
        e.preventDefault();
        alert('Please fill all item details correctly');
        return false;
    }

    // Calculate totals one more time before submit
    calculateTotals();

    return true;
});

// Delete confirmation
function confirmDelete(id, billNo) {
    if (confirm('Are you sure you want to delete bill "' + billNo + '"? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';

        const input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'udhar_id';
        input1.value = id;

        const input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'delete_udhar';
        input2.value = '1';

        form.appendChild(input1);
        form.appendChild(input2);
        document.body.appendChild(form);
        form.submit();
    }
}


// Toggle colorful rows
document.getElementById('toggleRowColors')?.addEventListener('change', function () {
    const rows = document.querySelectorAll('#itemsBody tr');
    if (this.checked) {
        rows.forEach(row => {
            if (!row.classList.contains('colored-row')) {
                applyRandomColorToRow(row);
            }
        });
    } else {
        rows.forEach(row => {
            row.style.backgroundColor = '';
            row.classList.remove('colored-row');
            delete row.dataset.hue;
        });
    }
});

// Column Resizing Logic
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('billTable');
    const cols = table.querySelectorAll('th');

    cols.forEach(col => {
        const resizer = col.querySelector('.resizer');
        if (!resizer) return;

        let x = 0;
        let w = 0;

        const mouseDownHandler = function (e) {
            x = e.clientX;
            const styles = window.getComputedStyle(col);
            w = parseInt(styles.width, 10);

            document.addEventListener('mousemove', mouseMoveHandler);
            document.addEventListener('mouseup', mouseUpHandler);
            resizer.classList.add('resizing');
        };

        const mouseMoveHandler = function (e) {
            const dx = e.clientX - x;
            col.style.width = `${w + dx}px`;
        };

        const mouseUpHandler = function () {
            document.removeEventListener('mousemove', mouseMoveHandler);
            document.removeEventListener('mouseup', mouseUpHandler);
            resizer.classList.remove('resizing');
        };

        resizer.addEventListener('mousedown', mouseDownHandler);
    });
});
